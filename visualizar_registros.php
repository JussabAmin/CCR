<?php
require_once "seguranca.php";
require_once "config.php";

$id_usuario = $_SESSION["id"];
$registros = [];

// Adicione as novas variáveis de filtro
$data_inicial = $_GET['data_inicial'] ?? null;
$data_final = $_GET['data_final'] ?? null;
$descricao_filtro = $_GET['descricao'] ?? null;
$categoria_filtro = $_GET['categoria'] ?? null;

// Constrói a query SQL
$query_despesas = "SELECT id, 'Despesa' as tipo, descricao, valor, categoria AS origem, data, anexo FROM despesas WHERE usuario_id = ?";
$query_ativos = "SELECT id, 'Ativo' as tipo, descricao, valor, origem, data, anexo FROM ativos WHERE usuario_id = ?";

// Adiciona as condições de filtro
$condicoes = [];
$parametros = ['i', $id_usuario]; // O 'i' é para o ID do usuário

if ($data_inicial && $data_final) {
    $condicoes[] = "data BETWEEN ? AND ?";
    $parametros[0] .= 'ss'; // Adiciona 's' para as duas datas
    $parametros[] = $data_inicial;
    $parametros[] = $data_final;
}

if ($descricao_filtro) {
    $condicoes[] = "descricao LIKE ?";
    $parametros[0] .= 's';
    $parametros[] = "%" . $descricao_filtro . "%";
}

if ($categoria_filtro) {
    // Adiciona a condição de categoria/origem.
    $condicoes[] = "categoria LIKE ?";
    $parametros[0] .= 's';
    $parametros[] = "%" . $categoria_filtro . "%";
}

// Junta todas as condições à query
if (!empty($condicoes)) {
    $condicoes_str = implode(' AND ', $condicoes);
    $query_despesas .= " AND " . $condicoes_str;
    
    // A query de ativos precisa ser tratada separadamente para a categoria/origem
    $condicoes_str_ativos = str_replace("categoria", "origem", $condicoes_str);
    $query_ativos .= " AND " . $condicoes_str_ativos;
}

$query_despesas .= " ORDER BY data DESC";
$query_ativos .= " ORDER BY data DESC";

// Executa a query para despesas
if ($stmt = mysqli_prepare($link, $query_despesas)) {
    mysqli_stmt_bind_param($stmt, ...$parametros);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $registros[] = $row;
    }
    mysqli_stmt_close($stmt);
}

// Executa a query para ativos
if ($stmt = mysqli_prepare($link, $query_ativos)) {
    mysqli_stmt_bind_param($stmt, ...$parametros);
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
    <title>Meus Registos</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="ccr.css">
</head>
<body>
    <div class="content-container p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <a href="dashboard.php" class="btn btn-primary me-2">Painel Central</a>
            <img src="img/android-chrome-192x192.png" alt="Logo CCR" width="100">
            <div>
                <a href="adicionar_despesa.php" class="btn btn-secondary me-2">Adicionar Despesa</a>
                <a href="adicionar_ativo.php" class="btn btn-success me-2">Adicionar Entrada</a>
                <a href="saldo_geral.php" class="btn btn-info me-2">Saldo Geral</a>
                <a href="logout.php" class="btn btn-danger">Sair da Conta</a>
            </div>
        </div>

        <div class="wrapper" style="max-width: 1000px; margin: 0 auto;">
            <h1 class="my-5 text-center">Registos Financeiros</h1>
            <p class="text-center">Visualize e filtre suas despesas e ativos.</p>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="get" class="mb-4">
               <div class="row g-3">
                   <div class="col-md-3">
                       <label for="descricao" class="form-label">Descrição</label>
                       <input type="text" name="descricao" id="descricao" class="form-control" value="<?php echo htmlspecialchars($descricao_filtro ?? ''); ?>">
                   </div>
                   <div class="col-md-3">
                       <label for="categoria" class="form-label">Categoria/Origem</label>
                       <input type="text" name="categoria" id="categoria" class="form-control" value="<?php echo htmlspecialchars($categoria_filtro ?? ''); ?>">
                   </div>
                   <div class="col-md-3">
                       <label for="data_inicial" class="form-label">Data Inicial</label>
                       <input type="date" name="data_inicial" id="data_inicial" class="form-control" value="<?php echo htmlspecialchars($data_inicial ?? ''); ?>">
                   </div>
                   <div class="col-md-3">
                       <label for="data_final" class="form-label">Data Final</label>
                       <input type="date" name="data_final" id="data_final" class="form-control" value="<?php echo htmlspecialchars($data_final ?? ''); ?>">
                   </div>
               </div>
               <div class="row g-3 mt-2">
                    <div class="col-md-12 d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary me-2">Aplicar Filtros</button>
                        <a href="visualizar_registros.php" class="btn btn-secondary">Limpar Filtros</a>
                    </div>
               </div>
           </form>

            <div class="table-responsive">
                <table class="table table-striped table-hover table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>Tipo</th>
                            <th>Descrição</th>
                            <th>Valor</th>
                            <th>Categoria/Origem</th>
                            <th>Data</th>
                            <th>Anexo</th>
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
                                    <?php if ($registro['anexo']): ?>
                                        <a href="<?php echo htmlspecialchars($registro['anexo']); ?>" target="_blank" class="btn btn-sm btn-info text-white">Ver Anexo</a>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <!--<a href="editar_registro.php?id=<?php //echo $registro['id']; ?>&tipo=<?php //echo strtolower($registro['tipo']); ?>" class="btn btn-sm btn-warning">Editar</a>-->
                                   <!-- <a href="excluir_registro.php?id=<?php //echo $registro['id']; ?>&tipo=<?php //echo strtolower($registro['tipo']); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza que deseja excluir este registro?');">Excluir</a>-->
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center">Nenhum registro encontrado.</td>
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