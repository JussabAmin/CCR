<?php
require_once "seguranca.php";
require_once "config.php";

$id_usuario = $_SESSION["id"];

// Arrays para armazenar os dados
$trabalhadores = [];
$salarios_por_trabalhador = [];
$emprestimos_por_trabalhador = [];

// Busca a lista de trabalhadores
$query_trabalhadores = "SELECT id, nome FROM trabalhadores WHERE usuario_id = ? ORDER BY nome ASC";
if ($stmt = mysqli_prepare($link, $query_trabalhadores)) {
    mysqli_stmt_bind_param($stmt, "i", $id_usuario);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $trabalhadores[] = $row;
    }
    mysqli_stmt_close($stmt);
}

// Para cada trabalhador, busca o total de salários e empréstimos
foreach ($trabalhadores as $trabalhador) {
    $trabalhador_id = $trabalhador['id'];

    // Busca o total de salários pagos a este trabalhador
    $query_salarios = "SELECT SUM(valor) as total FROM salarios WHERE trabalhador_id = ? AND usuario_id = ?";
    if ($stmt = mysqli_prepare($link, $query_salarios)) {
        mysqli_stmt_bind_param($stmt, "ii", $trabalhador_id, $id_usuario);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        $salarios_por_trabalhador[$trabalhador_id] = $row['total'] ?? 0;
        mysqli_stmt_close($stmt);
    }

    // Busca o total de empréstimos feitos por este trabalhador
    $query_emprestimos = "SELECT SUM(valor) as total FROM emprestimos WHERE trabalhador_id = ? AND usuario_id = ?";
    if ($stmt = mysqli_prepare($link, $query_emprestimos)) {
        mysqli_stmt_bind_param($stmt, "ii", $trabalhador_id, $id_usuario);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        $emprestimos_por_trabalhador[$trabalhador_id] = $row['total'] ?? 0;
        mysqli_stmt_close($stmt);
    }
}

mysqli_close($link);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Controle de Trabalhadores</title>
    
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
                    <li><a href="visualizar_registros.php">Ver Registos</a></li>
                    <li><a href="gerenciar_registro.php">Gerenciar Registos</a></li>
                    <li><a href="adicionar_despesa.php">Adicionar Despesa</a></li>
                    <li><a href="adicionar_ativo.php">Adicionar Entrada</a></li>
                    <li><a href="logout.php">Sair da Conta</a></li>
                </ul>
            </nav>
        </div>

        <h1 class="my-5 text-center">Controle de Trabalhadores</h1>
        
        <div class="row mb-4">
            <div class="col-md-12 text-end">
                <a href="adicionar_trabalhador.php" class="btn btn-primary">Adicionar Novo Trabalhador</a>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <h3>Lista de Trabalhadores</h3>
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Salário Total Pago</th>
                            <th>Empréstimo Total</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($trabalhadores) > 0): ?>
                            <?php foreach ($trabalhadores as $trabalhador):
                                $trabalhador_id = $trabalhador['id'];
                                $salario_total = $salarios_por_trabalhador[$trabalhador_id] ?? 0;
                                $emprestimo_total = $emprestimos_por_trabalhador[$trabalhador_id] ?? 0;
                            ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($trabalhador['nome']); ?></td>
                                    <td>R$ <?php echo number_format($salario_total, 2, ',', '.'); ?></td>
                                    <td>R$ <?php echo number_format($emprestimo_total, 2, ',', '.'); ?></td>
                                    <td>
                                        <a href="detalhes_trabalhador.php?id=<?php echo $trabalhador_id; ?>" class="btn btn-sm btn-info">Ver Detalhes</a>
                                        <a href="remover_trabalhador.php?id=<?php echo $trabalhador_id; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza que deseja remover este trabalhador?');">Remover</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center">Nenhum trabalhador cadastrado.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>