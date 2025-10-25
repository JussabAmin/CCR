<?php
require_once "seguranca.php";
require_once "config.php";

$id_usuario = $_SESSION["id"];

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Inicia a transação para garantir a integridade dos dados
    mysqli_begin_transaction($link);
    
    $sucesso = false;
    $erro_mensagem = "Ocorreu um erro ao processar o pagamento.";

    try {
        // Query para somar apenas os salários com status 'pendente'
        $sql_soma = "SELECT SUM(valor) AS total_salarios FROM salarios WHERE usuario_id = ? AND status = 'pendente'";
        if ($stmt_soma = mysqli_prepare($link, $sql_soma)) {
            mysqli_stmt_bind_param($stmt_soma, "i", $id_usuario);
            mysqli_stmt_execute($stmt_soma);
            $result_soma = mysqli_stmt_get_result($stmt_soma);
            $row_soma = mysqli_fetch_assoc($result_soma);
            $total_salarios = $row_soma['total_salarios'] ?? 0;
            mysqli_stmt_close($stmt_soma);
        } else {
            throw new Exception("Erro ao somar os salários.");
        }

        // Se houver salários para pagar, registra a despesa e atualiza o status
        if ($total_salarios > 0) {
            $data_hoje = date("Y-m-d");
            $categoria_despesa = "Salários";
            $descricao_despesa = "Pagamento total de salários pendentes";

            // 1. Insere o registro na tabela de despesas
            $sql_despesa = "INSERT INTO despesas (valor, data, categoria, descricao, usuario_id) VALUES (?, ?, ?, ?, ?)";
            if ($stmt_despesa = mysqli_prepare($link, $sql_despesa)) {
                mysqli_stmt_bind_param($stmt_despesa, "dsssi", $total_salarios, $data_hoje, $categoria_despesa, $descricao_despesa, $id_usuario);
                mysqli_stmt_execute($stmt_despesa);
                mysqli_stmt_close($stmt_despesa);
            } else {
                throw new Exception("Erro ao inserir a despesa.");
            }

            // 2. Atualiza o status dos salários de 'pendente' para 'pago'
            $sql_atualizar_status = "UPDATE salarios SET status = 'pago' WHERE usuario_id = ? AND status = 'pendente'";
            if ($stmt_atualizar = mysqli_prepare($link, $sql_atualizar_status)) {
                mysqli_stmt_bind_param($stmt_atualizar, "i", $id_usuario);
                mysqli_stmt_execute($stmt_atualizar);
                mysqli_stmt_close($stmt_atualizar);
            } else {
                throw new Exception("Erro ao atualizar o status dos salários.");
            }
        }
        
        // Se tudo ocorreu bem, confirma as operações
        mysqli_commit($link);
        $sucesso = true;

    } catch (Exception $e) {
        // Em caso de erro, desfaz tudo
        mysqli_rollback($link);
        $erro_mensagem = "Erro: " . $e->getMessage();
        error_log($erro_mensagem);
    }
    
    mysqli_close($link);
    
    // Redireciona de volta para a página de trabalhadores
    header("location: trabalhadores.php");
    exit;

} else {
    // Redireciona se a página for acessada sem o método POST
    header("location: trabalhadores.php");
    exit;
}
?>