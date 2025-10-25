<?php
require_once "seguranca.php";
require_once "config.php";

$id_usuario = $_SESSION["id"];
$limite = $_POST["limite"];

$sql = "UPDATE usuarios SET limite_despesa = ? WHERE id = ?";
if ($stmt = mysqli_prepare($link, $sql)) {
    mysqli_stmt_bind_param($stmt, "di", $limite, $id_usuario);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    header("location: dashboard.php");
} else {
    echo "Erro ao atualizar o limite.";
}
mysqli_close($link);
?>