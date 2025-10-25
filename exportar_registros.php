<?php
require_once "seguranca.php";
require_once "config.php";

$id_usuario = $_SESSION["id"];

// Define o cabeçalho para download do arquivo XLS
header('Content-Type: application/vnd.ms-excel; charset=utf-8');
header('Content-Disposition: attachment; filename="registros_financeiros_CCR.xls"');

// Abre o arquivo de saída temporário
$output = fopen("php://output", "w");

// Define as colunas do cabeçalho
fputcsv($output, array('Data', 'Tipo', 'Descricao', 'Categoria_Origem', 'Valor'));

// Busca os dados de despesas
$query_despesas = "SELECT data, 'Despesa' as tipo, descricao, categoria, valor FROM despesas WHERE usuario_id = ? ORDER BY data ASC";
if ($stmt = mysqli_prepare($link, $query_despesas)) {
    mysqli_stmt_bind_param($stmt, "i", $id_usuario);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        // Substitui a vírgula por ponto no valor para o Excel reconhecer o número
        $row['valor'] = str_replace('.', ',', $row['valor']);
        fputcsv($output, $row);
    }
    mysqli_stmt_close($stmt);
}

// Busca os dados de ativos
$query_ativos = "SELECT data, 'Ativo' as tipo, descricao, origem, valor FROM ativos WHERE usuario_id = ? ORDER BY data ASC";
if ($stmt = mysqli_prepare($link, $query_ativos)) {
    mysqli_stmt_bind_param($stmt, "i", $id_usuario);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        // Substitui a vírgula por ponto no valor
        $row['valor'] = str_replace('.', ',', $row['valor']);
        fputcsv($output, $row);
    }
    mysqli_stmt_close($stmt);
}

// Fecha o arquivo
fclose($output);
exit;
?>