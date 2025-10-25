<?php
require_once "seguranca.php";
require_once "config.php";

// Verifica se os parâmetros id e tipo foram passados
if (isset($_GET["id"]) && !empty(trim($_GET["id"])) && isset($_GET["tipo"]) && !empty(trim($_GET["tipo"]))) {
    $id = trim($_GET["id"]);
    $tipo = trim($_GET["tipo"]);
    
    // Prepara a query SQL com base no tipo
    if ($tipo == "despesa") {
        $sql = "DELETE FROM despesas WHERE id = ? AND usuario_id = ?";
    } elseif ($tipo == "ativo") {
        $sql = "DELETE FROM ativos WHERE id = ? AND usuario_id = ?";
    } else {
        header("location: visualizar_registros.php");
        exit;
    }
    
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "ii", $param_id, $param_usuario_id);
        
        $param_id = $id;
        $param_usuario_id = $_SESSION["id"];
        
        if (mysqli_stmt_execute($stmt)) {
            // Sucesso: redireciona de volta para a lista
            header("location: visualizar_registros.php");
            exit;
        } else {
            echo "Ops! Algo deu errado. Por favor, tente novamente mais tarde.";
        }
        mysqli_stmt_close($stmt);
    }
    mysqli_close($link);
} else {
    // Se o ID ou o tipo não forem válidos, redireciona
    header("location: visualizar_registros.php");
    exit;
}
?>