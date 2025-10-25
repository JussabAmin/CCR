<?php
require_once "seguranca.php";
require_once "config.php";

$id_usuario = $_SESSION["id"];

// Garante que um ID de trabalhador foi passado e que é um POST (boa prática)
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["id"]) && !empty(trim($_GET["id"]))) {
    $trabalhador_id = trim($_GET["id"]);

    // Query para deletar o trabalhador e seus registros
    // ON DELETE CASCADE nas chaves estrangeiras garante que salários e empréstimos também serão deletados
    $sql = "DELETE FROM trabalhadores WHERE id = ? AND usuario_id = ?";

    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "ii", $trabalhador_id, $id_usuario);
        
        if (mysqli_stmt_execute($stmt)) {
            // Sucesso, redireciona de volta
            header("location: trabalhadores.php");
            exit;
        } else {
            echo "Erro ao tentar remover o trabalhador.";
        }
        mysqli_stmt_close($stmt);
    }
} else {
    // Redireciona se o acesso for inválido
    header("location: trabalhadores.php");
    exit;
}

mysqli_close($link);
?>