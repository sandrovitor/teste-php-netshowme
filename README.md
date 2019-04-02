# Teste PHP - NetShowMe
Resposta ao teste da NetShowMe

### Banco de dados:
netshowme-bd1 (Collation: utf8-general_ci)

### Criação da tabela:
```
CREATE TABLE `netshowme-bd1`.`contato` ( `ID` INT(3) NOT NULL AUTO_INCREMENT , `nome` VARCHAR(50) NOT NULL , `email` VARCHAR(50) NOT NULL , `telefone` VARCHAR(16) NOT NULL , `mensagem` VARCHAR(450) NOT NULL , `anexo` VARCHAR(25) NOT NULL , `IP` VARCHAR(16) NOT NULL , PRIMARY KEY (`ID`)) ENGINE = InnoDB;
```
