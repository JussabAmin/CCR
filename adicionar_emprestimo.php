<?php
require_once "seguranca.php";
require_once "config.php";

$trabalhador = null;
$id_usuario = $_SESSION["id"];
$trabalhador_id = null;
$erro = null;

// Verifica se um ID de trabalhador foi passado na URL
if (isset($_GET["trabalhador_id"]) && !empty(trim($_GET["trabalhador_id"]))) {
    $trabalhador_id = trim($_GET["trabalhador_id"]);
    
    // Busca o nome do trabalhador para exibir no título
    $query_trabalhador = "SELECT nome FROM trabalhadores WHERE id = ? AND usuario_id = ?";
    if ($stmt = mysqli_prepare($link, $query_trabalhador)) {
        mysqli_stmt_bind_param($stmt, "ii", $trabalhador_id, $id_usuario);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if (mysqli_num_rows($result) == 1) {
            $trabalhador = mysqli_fetch_assoc($result);
        } else {
            // Redireciona se o trabalhador não for encontrado
            header("location: trabalhadores.php");
            exit;
        }
        mysqli_stmt_close($stmt);
    }
} else {
    // Redireciona se nenhum ID for fornecido
    header("location: trabalhadores.php");
    exit;
}

// Lógica de processamento do formulário
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $valor_emprestimo = trim($_POST["valor_emprestimo"]);
    $data_emprestimo = trim($_POST["data_emprestimo"]);
    $descricao_emprestimo = trim($_POST["descricao_emprestimo"]);

    // Validação básica
    if (empty($valor_emprestimo) || empty($data_emprestimo)) {
        $erro = "Por favor, preencha o valor e a data.";
    } else {
        // Inicia a transação
        mysqli_begin_transaction($link);
        
        $sucesso = false;

        try {
            // Query 1: Insere o registro na tabela de emprestimos
            $sql_emprestimos = "INSERT INTO emprestimos (valor, data, descricao, trabalhador_id, usuario_id) VALUES (?, ?, ?, ?, ?)";
            if ($stmt_emprestimo = mysqli_prepare($link, $sql_emprestimos)) {
                mysqli_stmt_bind_param($stmt_emprestimo, "dssii", $valor_emprestimo, $data_emprestimo, $descricao_emprestimo, $trabalhador_id, $id_usuario);
                mysqli_stmt_execute($stmt_emprestimo);
                mysqli_stmt_close($stmt_emprestimo);
            } else {
                throw new Exception("Erro ao preparar a query de empréstimos.");
            }

            // Query 2: Insere o registro na tabela de despesas para refletir no dashboard
            $categoria_despesa = "Empréstimos";
            $descricao_despesa = "Empréstimo para " . htmlspecialchars($trabalhador['nome']);
            if (!empty($descricao_emprestimo)) {
                 $descricao_despesa .= " - " . htmlspecialchars($descricao_emprestimo);
            }
            $sql_despesas = "INSERT INTO despesas (valor, data, categoria, descricao, usuario_id) VALUES (?, ?, ?, ?, ?)";
            if ($stmt_despesa = mysqli_prepare($link, $sql_despesas)) {
                mysqli_stmt_bind_param($stmt_despesa, "dsssi", $valor_emprestimo, $data_emprestimo, $categoria_despesa, $descricao_despesa, $id_usuario);
                mysqli_stmt_execute($stmt_despesa);
                mysqli_stmt_close($stmt_despesa);
            } else {
                throw new Exception("Erro ao preparar a query de despesas.");
            }

            // Se ambas as queries deram certo, confirma a transação
            mysqli_commit($link);
            $sucesso = true;

        } catch (Exception $e) {
            // Em caso de qualquer erro, desfaz todas as alterações
            mysqli_rollback($link);
            $erro = "Algo deu errado: " . $e->getMessage();
            $sucesso = false;
        }

        if ($sucesso) {
            header("location: detalhes_trabalhador.php?id=" . $trabalhador_id);
            exit;
        }
    }
}

mysqli_close($link);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Adicionar Empréstimo</title>
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="ccr.css">
</head>
<body>
    <div class="content-container p-4">
        <h1 class="my-5 text-center">Adicionar Empréstimo para <?php echo htmlspecialchars($trabalhador['nome']); ?></h1>

        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card p-4">
                    <?php if (isset($erro)): ?>
                        <div class="alert alert-danger"><?php echo $erro; ?></div>
                    <?php endif; ?>
                    <form action="adicionar_emprestimo.php?trabalhador_id=<?php echo $trabalhador_id; ?>" method="post">
                        <div class="mb-3">
                            <label for="valor_emprestimo" class="form-label">Valor do Empréstimo (R$)</label>
                            <input type="number" step="0.01" name="valor_emprestimo" id="valor_emprestimo" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="data_emprestimo" class="form-label">Data do Empréstimo</label>
                            <input type="date" name="data_emprestimo" id="data_emprestimo" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="descricao_emprestimo" class="form-label">Descrição (Opcional)</label>
                            <textarea name="descricao_emprestimo" id="descricao_emprestimo" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Adicionar Empréstimo</button>
                            <a href="detalhes_trabalhador.php?id=<?php echo $trabalhador_id; ?>" class="btn btn-secondary">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>