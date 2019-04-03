<?php

// Configuração do e-mail
$remetente = "no-reply@netshow.me";
$destinatario = "teste@teste.com";
$assuntoEmail = 'CONTATO PELO SITE';

// Configuração do Banco de Dados
$username = 'root'; // Usuário do Banco
$pass = ''; // Senha do usuário do Banco
$dbname = 'netshowme-bd1'; // Nome do banco de dados
$host = 'localhost'; // Local onde o banco está hospedado.


/*
-------------------------------------------
Não alterar nada depois daqui!
Inicialização de configuração do banco de dados.
-------------------------------------------
*/

// Conexão via PDO
$pdo = new PDO ( "mysql:host=".$host.";dbname=".$dbname, $username, $pass );
if (! $pdo) {
	die ( 'Erro ao criar a conexão' );
}

$pdo -> setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

// Configura timezone.
date_default_timezone_set("America/Bahia");