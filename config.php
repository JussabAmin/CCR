<?php
// Credenciais do banco de dados
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');   // Nome de usuário padrão do XAMPP
define('DB_PASSWORD', '');       // Senha padrão do XAMPP (vazia)
define('DB_NAME', 'financeiro'); // Mude para o nome do seu banco de dados (ex: 'financeiro')

// Tenta se conectar ao banco de dados MySQL
$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Checa a conexão
if($link === false){
    die("ERRO: Não foi possível conectar. " . mysqli_connect_error());
}
?>