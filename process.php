<?php


if(!isset($_POST['nome']) || !isset($_POST['telefone']) || !isset($_POST['email']) || !isset($_POST['mensagem']) || !isset($_FILES['arquivo']) || $_FILES['arquivo']['error'] != 0)  {
    echo 'Todos os campos são obrigatórios.';
    exit();
} else {


    $nome = filter_var($_POST['nome'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $telefone = str_replace(array(' ', '-'), '', $_POST['telefone']);
    $mensagem = filter_var(strip_tags($_POST['mensagem']), FILTER_SANITIZE_STRING);

    // Tamanho das strings
    if(strlen($nome) > 40) {
        echo 'O nome excedeu o limite de 40 caracteres.';
        exit();
    }

    if(strlen($email) > 40) {
        echo 'O e-mail excedeu o limite de 40 caracteres.';
        exit();
    }

    if(strlen($mensagem) > 400) {
        echo 'A mensagem excedeu o limite de 400 caracteres.';
        exit();
    }

    // Valida nome, email e telefone.
    if(preg_match('~^[a-zA-Z -çÇ]+$~u', $nome) !== 1) { // Valida $nome
        echo '- Nome não passou na validação!<br>';
        var_dump($nome);
        exit();
    }

    if(preg_match('~^[a-z].[a-z0-9-._]+\@.[a-z0-9.-]+.[a-z]$~', $email) !== 1) { // Valida email
        echo '- E-mail não passou na validação!<br>';
        var_dump($email);
        exit();
    } 

    if(preg_match('~^\(?\d{2}\)?\d{8,}$~', $telefone) !== 1) { // Valida telefone
        echo '- Telefone não passou na validação!<br>';
        var_dump($telefone);
        exit();
    } 

    // Valida arquivo através do mime-type.
    
    if($_FILES['arquivo']['type'] == 'application/pdf') { //PDF
        $arqValido = true;
        $ext = '.pdf';
    }
    if($_FILES['arquivo']['type'] == 'application/msword') { //DOC
        $arqValido = true;
        $ext = '.doc';
    }
    if($_FILES['arquivo']['type'] == '	application/vnd.openxmlformats-officedocument.wordprocessingml.document') { //DOCX
        $arqValido = true;
        $ext = '.docx';
    }
    if($_FILES['arquivo']['type'] == 'application/vnd.oasis.opendocument.text') { //ODT
        $arqValido = true;
        $ext = '.odt';
    }
    if($_FILES['arquivo']['type'] == 'text/plain') { //TXT
        $arqValido = true;
        $ext = '.txt';
    }

    if(!isset($arqValido) || $arqValido !== true) {
        echo '- Arquivo anexo é inválido!<br>';
        exit();
    }

    // Verifica se o tamanho do arquivo possui 500KB ou menos.
    // 500KB = 512.000 bytes
    if(filesize($_FILES['arquivo']['tmp_name']) > 512000) {
        echo '- Arquivo excedeu o limite de 500KB.';
        exit();
    }
    
    //IP do visitante
    $ipVisitante = $_SERVER['REMOTE_ADDR'];

    
    // Todos os campos são válidos!
    // Salva arquivos no Banco de Dados e registro na tabela.

    // Carrega arquivo de configuração.
    include_once('config.php');

    $caracteres = 'abcdefghijlkmnopqrstuvxyzwABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
    $nomeArq = '';
    for($i=0; $i < 10; $i++) {
        $nomeArq .= $caracteres[mt_rand(0,61)];
    }

    // Move o arquivo para a pasta upload
    $moveu = move_uploaded_file($_FILES['arquivo']['tmp_name'], 'upload/'.$nomeArq.$ext);
    if($moveu == false) {
        echo 'Ocorreu um erro ao mover o arquivo enviado';
        exit();
    }

    // Insere informações no BD
    $abc = $pdo->prepare('INSERT INTO `contato` (`ID`, `nome`, `email`, `telefone`, `mensagem`, `anexo`, `IP`, `data_envio`) VALUES (null, :nome, :email, :telefone, :mensagem, :anexo, :ip, :envio)');
    $abc->bindValue(':nome', $nome, PDO::PARAM_STR);
    $abc->bindValue(':email', addslashes($email), PDO::PARAM_STR);
    $abc->bindValue(':telefone', $telefone, PDO::PARAM_STR);
    $abc->bindValue(':mensagem', addslashes($mensagem), PDO::PARAM_STR);
    $abc->bindValue(':anexo', 'upload/'.$nomeArq.$ext, PDO::PARAM_STR);
    $abc->bindValue(':ip', $ipVisitante, PDO::PARAM_STR);
    $abc->bindValue(':envio', date('Y-m-d H:i:s'), PDO::PARAM_STR);

    try {
        //$abc->execute();
    } catch(PDOException $e) {
        echo 'Ocorreu um erro ao escrever no Banco de Dados: '.$e->getMessage();
        exit();
    }

    
    
    // Endereço do site
    $host = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    $host = substr($host, 0, strrpos($host, '/process.php')); // Remove o nome do script atual da URL
    
    
    

    $html =<<<DADOS
<html>
<head>
<title>Contato pelo site</title>
</head>

<body>
<h4> Contato pelo site</h4>
<strong>Nome: </strong>$nome<br>
<strong>Endereço de e-mail: </strong>$email<br>
<strong>Telefone: </strong>$telefone<br>
<strong>Endereço de IP: </strong>$ipVisitante<br>
<strong>Arquivo anexo: </strong> <a href="$host/upload/$nomeArq$ext" target="_blank">$nomeArq$ext</a>
<br>--<br>
<strong>Mensagem: </strong><br>
$mensagem

<br><br><br>
<small>[Mensagem gerada automaticamente.]</small>
</body>
</html>
DADOS;


    // Verifica se as variáveis estão devidamente configuradas, antes de enviar e-mail.
    if(isset($remetente) && $remetente != '' && isset($destinatario) && $destinatario != '') {
        $messageHTML = $html;
        unset($html);
    
        // Para enviar email HTML é necessário setar no cabeçalho o Content-type
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-type: text/html; charset=utf-8';
    
        // Additional headers
        $headers[] = 'To: '.$destinatario;
        $headers[] = 'From: '.$remetente;
        $headers[] = 'Reply-To: '.$email;
    
        $send = mail($destinatario, $assuntoEmail, $messageHTML, implode("\r\n", $headers));

        // Mostra o HTML na página para visualizar como ficou o e-mail.
        echo $messageHTML;
    
    } else {
        echo '<br> E-mail não enviado, pois não foi informado Destinatário e/ou Remetente<br>';
    }

    
}