<?php
require_once "seguranca.php";
require_once "config.php";

$trabalhador = null;
$id_usuario = $_SESSION["id"];
$trabalhador_id = null;

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
    $valor_salario = trim($_POST["valor_salario"]);
    $data_salario = trim($_POST["data_salario"]);

    // Validação básica
    if (empty($valor_salario) || empty($data_salario)) {
        $erro = "Por favor, preencha todos os campos.";
    } else {
        $sql = "INSERT INTO salarios (valor, data, trabalhador_id, usuario_id) VALUES (?, ?, ?, ?)";
        
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "dsii", $valor_salario, $data_salario, $trabalhador_id, $id_usuario);
            
            if (mysqli_stmt_execute($stmt)) {
                header("location: detalhes_trabalhador.php?id=" . $trabalhador_id);
                exit;
            } else {
                $erro = "Algo deu errado. Tente novamente mais tarde.";
            }
            mysqli_stmt_close($stmt);
        }
    }
}

mysqli_close($link);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Adicionar Salário</title>
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="ccr.css">
</head>
<body>
    <div class="content-container p-4">
        <h1 class="my-5 text-center">Adicionar Salário para <?php echo htmlspecialchars($trabalhador['nome']); ?></h1>

        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card p-4">
                    <?php if (isset($erro)): ?>
                        <div class="alert alert-danger"><?php echo $erro; ?></div>
                    <?php endif; ?>
                    <form action="adicionar_salario.php?trabalhador_id=<?php echo $trabalhador_id; ?>" method="post">
                        <div class="mb-3">
                            <label for="valor_salario" class="form-label">Valor do Salário (R$)</label>
                            <input type="number" step="0.01" name="valor_salario" id="valor_salario" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="data_salario" class="form-label">Data do Pagamento</label>
                            <input type="date" name="data_salario" id="data_salario" class="form-control" required>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Adicionar Salário</button>
                            <a href="detalhes_trabalhador.php?id=<?php echo $trabalhador_id; ?>" class="btn btn-secondary">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>