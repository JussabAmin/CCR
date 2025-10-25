<?php
require_once "seguranca.php";
require_once "config.php";

$id_usuario = $_SESSION["id"];

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["id"]) && isset($_GET["trabalhador_id"])) {
    $emprestimo_id = trim($_GET["id"]);
    $trabalhador_id = trim($_GET["trabalhador_id"]);

    // Inicia a transação para garantir a integridade dos dados
    mysqli_begin_transaction($link);
    $sucesso = false;

    try {
        // Passo 1: Busca o valor e a data do empréstimo
        $sql_busca = "SELECT valor, data FROM emprestimos WHERE id = ? AND usuario_id = ?";
        if ($stmt_busca = mysqli_prepare($link, $sql_busca)) {
            mysqli_stmt_bind_param($stmt_busca, "ii", $emprestimo_id, $id_usuario);
            mysqli_stmt_execute($stmt_busca);
            $result = mysqli_stmt_get_result($stmt_busca);
            $emprestimo = mysqli_fetch_assoc($result);
            mysqli_stmt_close($stmt_busca);

            if (!$emprestimo) {
                throw new Exception("Empréstimo não encontrado ou não pertence a este usuário.");
            }
        } else {
            throw new Exception("Erro ao buscar dados do empréstimo: " . mysqli_error($link));
        }

        // Passo 2: Atualiza o status do empréstimo para 'pago' (1)
        $sql_update = "UPDATE emprestimos SET pago = 1 WHERE id = ? AND usuario_id = ?";
        if ($stmt_update = mysqli_prepare($link, $sql_update)) {
            mysqli_stmt_bind_param($stmt_update, "ii", $emprestimo_id, $id_usuario);
            mysqli_stmt_execute($stmt_update);
            mysqli_stmt_close($stmt_update);
        } else {
            throw new Exception("Erro ao atualizar o empréstimo: " . mysqli_error($link));
        }

        // Passo 3: Adiciona o valor do empréstimo como uma entrada (ativo)
        $categoria_ativo = "Reembolso de Empréstimo";
        $descricao_ativo = "Pagamento de empréstimo do trabalhador com ID: " . $trabalhador_id;
        $sql_ativo = "INSERT INTO ativos (valor, data, categoria, descricao, usuario_id) VALUES (?, ?, ?, ?, ?)";
        if ($stmt_ativo = mysqli_prepare($link, $sql_ativo)) {
            mysqli_stmt_bind_param($stmt_ativo, "dsssi", $emprestimo['valor'], $emprestimo['data'], $categoria_ativo, $descricao_ativo, $id_usuario);
            mysqli_stmt_execute($stmt_ativo);
            mysqli_stmt_close($stmt_ativo);
        } else {
            throw new Exception("Erro ao registrar a entrada: " . mysqli_error($link));
        }

        mysqli_commit($link);
        $sucesso = true;
    } catch (Exception $e) {
        mysqli_rollback($link);
        echo "Erro Fatal: " . $e->getMessage();
        // A linha abaixo interrompe a execução para que você possa ver o erro
        exit;
    }

    mysqli_close($link);
    
    // Redireciona de volta após a conclusão (este trecho só roda se não houver erro)
    if ($sucesso) {
        header("location: detalhes_trabalhador.php?id=" . $trabalhador_id . "&status=success");
    } else {
        header("location: detalhes_trabalhador.php?id=" . $trabalhador_id . "&status=error");
    }
    exit;

} else {
    // Redireciona se a página for acessada sem os parâmetros corretos
    header("location: trabalhadores.php");
    exit;
}
?>