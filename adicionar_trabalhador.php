<?php
require_once "seguranca.php";
require_once "config.php";

// Verifica se o formulário foi submetido (via método POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome_trabalhador = trim($_POST["nome_trabalhador"]);
    $id_usuario = $_SESSION["id"];

    // Prepara a query de inserção
    $sql = "INSERT INTO trabalhadores (nome, usuario_id) VALUES (?, ?)";

    if ($stmt = mysqli_prepare($link, $sql)) {
        // Vincula as variáveis à query preparada
        mysqli_stmt_bind_param($stmt, "si", $nome_trabalhador, $id_usuario);
        
        // Tenta executar a query
        if (mysqli_stmt_execute($stmt)) {
            // Redireciona para a página de trabalhadores após o sucesso
            header("location: trabalhadores.php");
            exit;
        } else {
            echo "Algo deu errado. Por favor, tente novamente mais tarde.";
        }
        mysqli_stmt_close($stmt);
    }
}

// O código HTML do formulário começa aqui
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Adicionar Trabalhador</title>
    
    <link rel="apple-touch-icon" sizes="180x180" href="img/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="img/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="img/favicon-16x16.png">
    <link rel="manifest" href="img/site.webmanifest">
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="ccr.css">
</head>
<body>
    <div class="content-container p-4">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
            <img src="img/android-chrome-192x192.png" alt="Logo CCR" width="100" class="mb-3 mb-md-0">
            <nav class="sliced-menu">
                <ul>
                    <li><a href="dashboard.php">Painel Central</a></li>
                    <li><a href="trabalhadores.php">Voltar para Trabalhadores</a></li>
                    <li><a href="logout.php">Sair da Conta</a></li>
                </ul>
            </nav>
        </div>

        <h1 class="my-5 text-center">Adicionar Novo Trabalhador</h1>
        
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card p-4">
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                        <div class="mb-3">
                            <label for="nome_trabalhador" class="form-label">Nome do Trabalhador</label>
                            <input type="text" name="nome_trabalhador" id="nome_trabalhador" class="form-control" required>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Adicionar</button>
                            <a href="trabalhadores.php" class="btn btn-secondary">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>