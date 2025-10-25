<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Inicia a sessão
session_start();

// Verifica se o usuário não está logado
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    // Redireciona para a página de login
    header("location: login.php");
    exit;
}
?>