<?php
require_once "seguranca.php";
require_once "config.php";

$id_usuario = $_SESSION["id"];
$registros = [];

// Busca todos os registros do usuário
$query_despesas = "SELECT id, 'Despesa' as tipo, descricao, valor, categoria AS origem, data, anexo FROM despesas WHERE usuario_id = ? ORDER BY data DESC";
if ($stmt = mysqli_prepare($link, $query_despesas)) {
    mysqli_stmt_bind_param($stmt, "i", $id_usuario);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $registros[] = $row;
    }
    mysqli_stmt_close($stmt);
}

$query_ativos = "SELECT id, 'Ativo' as tipo, descricao, valor, origem, data, anexo FROM ativos WHERE usuario_id = ? ORDER BY data DESC";
if ($stmt = mysqli_prepare($link, $query_ativos)) {
    mysqli_stmt_bind_param($stmt, "i", $id_usuario);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $registros[] = $row;
    }
    mysqli_stmt_close($stmt);
}

// Ordena todos os registros por data
usort($registros, function($a, $b) {
    return strtotime($b['data']) - strtotime($a['data']);
});

mysqli_close($link);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Gerenciar Registos</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="ccr.css">
</head>
<body>
    <div class="content-container p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <a href="dashboard.php" class="btn btn-primary me-2">Painel Central</a>
            <img src="img/android-chrome-192x192.png" alt="Logo CCR" width="100">
            <div>
                <a href="visualizar_registros.php" class="btn btn-secondary me-2">Ver Registos</a>
                <a href="logout.php" class="btn btn-danger">Sair da Conta</a>
            </div>
        </div>

        <div class="wrapper" style="max-width: 1000px; margin: 0 auto;">
            <h1 class="my-5 text-center">Gerenciar Registos</h1>
            <p class="text-center">Selecione um registro para editar ou excluir.</p>

            <div class="table-responsive">
                <table class="table table-striped table-hover table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>Tipo</th>
                            <th>Descrição</th>
                            <th>Valor</th>
                            <th>Categoria/Origem</th>
                            <th>Data</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($registros) > 0): ?>
                        <?php foreach ($registros as $registro): ?>
                            <tr>
                                <td><span class="badge <?php echo ($registro['tipo'] == 'Despesa') ? 'bg-danger' : 'bg-success'; ?>"><?php echo $registro['tipo']; ?></span></td>
                                <td><?php echo htmlspecialchars($registro['descricao']); ?></td>
                                <td>R$ <?php echo number_format($registro['valor'], 2, ',', '.'); ?></td>
                                <td><?php echo htmlspecialchars($registro['origem']); ?></td>
                                <td><?php echo htmlspecialchars($registro['data']); ?></td>
                                <td>
                                    <a href="editar_registro.php?id=<?php echo $registro['id']; ?>&tipo=<?php echo strtolower($registro['tipo']); ?>" class="btn btn-sm btn-warning">Editar</a>
                                    <a href="excluir_registro.php?id=<?php echo $registro['id']; ?>&tipo=<?php echo strtolower($registro['tipo']); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza que deseja excluir este registro?');">Excluir</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center">Nenhum registro encontrado.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-4">
                <a href="exportar_registros.php" class="btn btn-success">Exportar para Excel</a>
                <a href="dashboard.php" class="btn btn-secondary">Voltar para o Painel</a>
            </div>
        </div>
    </div>
</body>
</html>