<?php
require_once "seguranca.php";
require_once "config.php";

$id_usuario = $_SESSION["id"];
$trabalhador = null;
$salarios = [];
$emprestimos = [];
$saldo_mensal = [];

// Garante que um ID de trabalhador foi passado na URL
if (isset($_GET["id"]) && !empty(trim($_GET["id"]))) {
    $trabalhador_id = trim($_GET["id"]);

    // Busca os dados do trabalhador
    $query_trabalhador = "SELECT * FROM trabalhadores WHERE id = ? AND usuario_id = ?";
    if ($stmt = mysqli_prepare($link, $query_trabalhador)) {
        mysqli_stmt_bind_param($stmt, "ii", $trabalhador_id, $id_usuario);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if (mysqli_num_rows($result) == 1) {
            $trabalhador = mysqli_fetch_assoc($result);
        }
        mysqli_stmt_close($stmt);
    }

    // Se o trabalhador for encontrado, busca os salários e empréstimos
    if ($trabalhador) {
        $query_salarios = "SELECT * FROM salarios WHERE trabalhador_id = ? AND usuario_id = ? ORDER BY data DESC";
        if ($stmt = mysqli_prepare($link, $query_salarios)) {
            mysqli_stmt_bind_param($stmt, "ii", $trabalhador_id, $id_usuario);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            while ($row = mysqli_fetch_assoc($result)) {
                $salarios[] = $row;
            }
            mysqli_stmt_close($stmt);
        }

        $query_emprestimos = "SELECT * FROM emprestimos WHERE trabalhador_id = ? AND usuario_id = ? ORDER BY data DESC";
        if ($stmt = mysqli_prepare($link, $query_emprestimos)) {
            mysqli_stmt_bind_param($stmt, "ii", $trabalhador_id, $id_usuario);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            while ($row = mysqli_fetch_assoc($result)) {
                $emprestimos[] = $row;
            }
            mysqli_stmt_close($stmt);
        }
    } else {
        // Redireciona se o trabalhador não for encontrado
        header("location: trabalhadores.php");
        exit;
    }
} else {
    // Redireciona se nenhum ID for fornecido
    header("location: trabalhadores.php");
    exit;
}

// --- Lógica para Calcular o Saldo Mensal ---
foreach ($salarios as $salario) {
    $mes = date('Y-m', strtotime($salario['data']));
    if (!isset($saldo_mensal[$mes])) {
        $saldo_mensal[$mes] = ['salario' => 0, 'emprestimo' => 0];
    }
    // Apenas salários pendentes são somados para o resumo mensal
    if ($salario['status'] == 'pendente') {
        $saldo_mensal[$mes]['salario'] += $salario['valor'];
    }
}

foreach ($emprestimos as $emprestimo) {
    // Apenas empréstimos pendentes (pago = 0) são considerados para o desconto
    if ($emprestimo['pago'] == 0) {
        $mes = date('Y-m', strtotime($emprestimo['data']));
        if (!isset($saldo_mensal[$mes])) {
            $saldo_mensal[$mes] = ['salario' => 0, 'emprestimo' => 0];
        }
        $saldo_mensal[$mes]['emprestimo'] += $emprestimo['valor'];
    }
}

// Ordena os meses cronologicamente
ksort($saldo_mensal);

mysqli_close($link);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Detalhes de <?php echo htmlspecialchars($trabalhador['nome']); ?></title>
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="ccr.css">
</head>
<body>
    <div class="content-container p-4">
        <h1 class="my-5 text-center">Detalhes de <?php echo htmlspecialchars($trabalhador['nome']); ?></h1>

        <?php 
            if (isset($_GET['status']) && $_GET['status'] == 'success') {
                echo '<div class="alert alert-success text-center">Ação realizada com sucesso!</div>';
            }
            if (isset($_GET['status']) && $_GET['status'] == 'error') {
                echo '<div class="alert alert-danger text-center">Erro ao realizar a ação. Tente novamente.</div>';
            }
        ?>
        
        <div class="d-flex justify-content-between mb-4">
            <a href="trabalhadores.php" class="btn btn-secondary">Voltar para Trabalhadores</a>
            <a href="adicionar_salario.php?trabalhador_id=<?php echo $trabalhador_id; ?>" class="btn btn-success">Adicionar Salário</a>
            <a href="adicionar_emprestimo.php?trabalhador_id=<?php echo $trabalhador_id; ?>" class="btn btn-warning">Adicionar Empréstimo</a>
            <form action="pagar_salarios_total.php" method="post" style="display:inline;">
                <input type="hidden" name="trabalhador_id" value="<?php echo $trabalhador_id; ?>">
                <button type="submit" class="btn btn-info">Pagar Todos os Salários</button>
            </form>
        </div>
        
        <hr class="my-4">

        <div class="row mb-5">
            <div class="col-md-12">
                <div class="card p-3">
                    <h4 class="card-title text-center">Resumo Mensal</h4>
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Mês</th>
                                <th>Salário Bruto</th>
                                <th>Empréstimo (a descontar)</th>
                                <th>Total a Pagar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($saldo_mensal) > 0): ?>
                                <?php foreach ($saldo_mensal as $mes => $valores): ?>
                                    <tr>
                                        <td><?php echo date('m/Y', strtotime($mes)); ?></td>
                                        <td>R$ <?php echo number_format($valores['salario'], 2, ',', '.'); ?></td>
                                        <td>R$ <?php echo number_format($valores['emprestimo'], 2, ',', '.'); ?></td>
                                        <?php
                                            $total_pagar = $valores['salario'] - $valores['emprestimo'];
                                            $cor_total = ($total_pagar >= 0) ? 'text-success' : 'text-danger';
                                        ?>
                                        <td class="<?php echo $cor_total; ?>">
                                            R$ <?php echo number_format($total_pagar, 2, ',', '.'); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center">Nenhum registro encontrado para este trabalhador.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card p-3">
                    <h4 class="card-title">Histórico de Salários</h4>
                    <ul class="list-group list-group-flush">
                        <?php if (count($salarios) > 0): ?>
                            <?php foreach ($salarios as $salario): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><?php echo date('d/m/Y', strtotime($salario['data'])); ?></span>
                                    <span>R$ <?php echo number_format($salario['valor'], 2, ',', '.'); ?></span>
                                    <span class="badge bg-<?php echo ($salario['status'] == 'pago') ? 'success' : 'warning text-dark'; ?>">
                                        <?php echo ($salario['status'] == 'pago') ? 'Pago' : 'Pendente'; ?>
                                    </span>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li class="list-group-item">Nenhum salário registrado.</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
            
            <div class="col-md-6 mb-4">
                <div class="card p-3">
                    <h4 class="card-title">Histórico de Empréstimos</h4>
                    <ul class="list-group list-group-flush">
                        <?php if (count($emprestimos) > 0): ?>
                            <?php foreach ($emprestimos as $emprestimo): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <?php if ($emprestimo['pago'] == 1): ?>
                                            <del>
                                        <?php endif; ?>
                                        
                                        <span>R$ <?php echo number_format($emprestimo['valor'], 2, ',', '.'); ?></span>
                                        <small class="text-muted">(<?php echo date('d/m/Y', strtotime($emprestimo['data'])); ?>)</small>
                                        <small class="text-muted"><?php echo htmlspecialchars($emprestimo['descricao']); ?></small>
                                        
                                        <?php if ($emprestimo['pago'] == 1): ?>
                                            </del>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <?php if ($emprestimo['pago'] == 1): ?>
                                            <span class="badge bg-success">Pago</span>
                                        <?php else: ?>
                                            <a href="marcar_emprestimo_pago.php?id=<?php echo $emprestimo['id']; ?>&trabalhador_id=<?php echo $trabalhador_id; ?>" class="btn btn-sm btn-info" onclick="return confirm('Confirmar pagamento deste empréstimo?');">
                                                Marcar como Pago
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li class="list-group-item">Nenhum empréstimo registrado.</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>