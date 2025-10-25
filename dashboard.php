<?php
require_once "seguranca.php";
require_once "config.php";

$id_usuario = $_SESSION["id"];

// Arrays para armazenar os dados por mês
$despesas_por_mes = [];
$ativos_por_mes = [];

// Busca o total de despesas por mês
$query_despesas_mes = "SELECT DATE_FORMAT(data, '%Y-%m') as mes, SUM(valor) as total FROM despesas WHERE usuario_id = ? GROUP BY mes ORDER BY mes";
if ($stmt = mysqli_prepare($link, $query_despesas_mes)) {
    mysqli_stmt_bind_param($stmt, "i", $id_usuario);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $despesas_por_mes[$row['mes']] = $row['total'];
    }
    mysqli_stmt_close($stmt);
}

// Busca o total de ativos por mês
$query_ativos_mes = "SELECT DATE_FORMAT(data, '%Y-%m') as mes, SUM(valor) as total FROM ativos WHERE usuario_id = ? GROUP BY mes ORDER BY mes";
if ($stmt = mysqli_prepare($link, $query_ativos_mes)) {
    mysqli_stmt_bind_param($stmt, "i", $id_usuario);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $ativos_por_mes[$row['mes']] = $row['total'];
    }
    mysqli_stmt_close($stmt);
}

// Busca o total de despesas por categoria para o gráfico de pizza
$query_despesas_categoria = "SELECT categoria, SUM(valor) as total FROM despesas WHERE usuario_id = ? GROUP BY categoria ORDER BY total DESC";
$dados_despesas_categoria = [];
if ($stmt = mysqli_prepare($link, $query_despesas_categoria)) {
    mysqli_stmt_bind_param($stmt, "i", $id_usuario);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $dados_despesas_categoria[] = ['categoria' => $row['categoria'], 'total' => $row['total']];
    }
    mysqli_stmt_close($stmt);
}

// Combina os dados e calcula o saldo mensal
$meses_disponiveis = array_unique(array_merge(array_keys($despesas_por_mes), array_keys($ativos_por_mes)));
sort($meses_disponiveis);

$saldos_mensais = [];
$dados_graficos_despesas = [];
$dados_graficos_ativos = [];

foreach ($meses_disponiveis as $mes) {
    $total_despesas = $despesas_por_mes[$mes] ?? 0;
    $total_ativos = $ativos_por_mes[$mes] ?? 0;
    $saldo = $total_ativos - $total_despesas;

    $saldos_mensais[] = [
        'mes' => $mes,
        'saldo' => $saldo
    ];
    $dados_graficos_despesas[] = ['mes' => $mes, 'total' => $total_despesas];
    $dados_graficos_ativos[] = ['mes' => $mes, 'total' => $total_ativos];
}

// Saldo Líquido Total
$total_ativos_geral = array_sum($ativos_por_mes);
$total_despesas_geral = array_sum($despesas_por_mes);
$saldo_liquido = $total_ativos_geral - $total_despesas_geral;

// Dados do Mês Atual
$mes_atual = date('Y-m');
$entradas_mes_atual = $ativos_por_mes[$mes_atual] ?? 0;
$despesas_mes_atual = $despesas_por_mes[$mes_atual] ?? 0;
$saldo_mes_atual = $entradas_mes_atual - $despesas_mes_atual;

// Cálculo das Médias
$num_meses_ativos = count($ativos_por_mes);
$num_meses_despesas = count($despesas_por_mes);

$media_entradas = ($num_meses_ativos > 0) ? $total_ativos_geral / $num_meses_ativos : 0;
$media_despesas = ($num_meses_despesas > 0) ? $total_despesas_geral / $num_meses_despesas : 0;

// Converte os dados para JSON para o JavaScript
$json_despesas = json_encode($dados_graficos_despesas);
$json_ativos = json_encode($dados_graficos_ativos);
$json_saldos = json_encode($saldos_mensais);
$json_despesas_categoria = json_encode($dados_despesas_categoria);

mysqli_close($link);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>CCR-Finanças</title>
    
    <link rel="apple-touch-icon" sizes="180x180" href="img/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="img/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="img/favicon-16x16.png">
    <link rel="manifest" href="img/site.webmanifest">
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="ccr.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <style>
        #limiteForm {
            display: none;
        }
    </style>
    
</head>
<body>
    
    <div class="content-container p-4">
        
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
            <img src="img/android-chrome-192x192.png" alt="Logo CCR" width="100" class="mb-3 mb-md-0">
            <nav class="sliced-menu">
                <ul>
                    <li><a href="visualizar_registros.php">Ver Registos</a></li>
                    <li><a href="gerenciar_registro.php">Gerenciar Registos</a></li>
                    <li><a href="adicionar_despesa.php">Adicionar Despesa</a></li>
                    <li><a href="adicionar_ativo.php">Adicionar Entrada</a></li>
                    <li><a href="trabalhadores.php">Funcionários</a></li>
                    <li><a href="importar_excel.php">Importar Excel</a></li>
                    <li><a href="logout.php">Sair da Conta</a></li>
                </ul>
            </nav>
        </div>

        <div class="row mt-4 mb-5 text-center">
            <div class="col-md-4">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h5 class="card-title">Ativos Totais</h5>
                        <p class="card-text fs-2">R$ <?php echo number_format($total_ativos_geral, 2, ',', '.'); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-danger text-white">
                    <div class="card-body">
                        <h5 class="card-title">Despesas Totais</h5>
                        <p class="card-text fs-2">R$ <?php echo number_format($total_despesas_geral, 2, ',', '.'); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card <?php echo ($saldo_liquido >= 0) ? 'bg-primary' : 'bg-warning text-dark'; ?>">
                    <div class="card-body">
                        <h5 class="card-title">Saldo Líquido</h5>
                        <p class="card-text fs-2">R$ <?php echo number_format($saldo_liquido, 2, ',', '.'); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4 mb-5 text-center">
            <div class="col-md-4">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h5 class="card-title">Entradas (Este Mês)</h5>
                        <p class="card-text fs-2">R$ <?php echo number_format($entradas_mes_atual, 2, ',', '.'); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-danger text-white">
                    <div class="card-body">
                        <h5 class="card-title">Despesas (Este Mês)</h5>
                        <p class="card-text fs-2">R$ <?php echo number_format($despesas_mes_atual, 2, ',', '.'); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card <?php echo ($saldo_mes_atual >= 0) ? 'bg-primary' : 'bg-warning text-dark'; ?>">
                    <div class="card-body">
                        <h5 class="card-title">Saldo (Este Mês)</h5>
                        <p class="card-text fs-2">R$ <?php echo number_format($saldo_mes_atual, 2, ',', '.'); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4 mb-5 text-center">
            <div class="col-md-6">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h5 class="card-title">Média de Entradas</h5>
                        <p class="card-text fs-2">R$ <?php echo number_format($media_entradas, 2, ',', '.'); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card bg-danger text-white">
                    <div class="card-body">
                        <h5 class="card-title">Média de Despesas</h5>
                        <p class="card-text fs-2">R$ <?php echo number_format($media_despesas, 2, ',', '.'); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <hr class="my-5">

        <div class="row mt-4 mb-5 text-center">
            <div class="col-md-6 offset-md-3">
                <button class="btn btn-info w-100" onclick="toggleLimiteForm()">Definir Limite de Despesa</button>
            </div>
        </div>

        <div class="row mt-4 mb-5 text-center" id="limiteForm">
            <div class="col-md-6 offset-md-3">
                <div class="card p-4">
                    <h5 class="card-title text-center">Definir Limite de Despesa Mensal</h5>
                    <form action="definir_limite.php" method="post">
                        <div class="form-group mb-3">
                            <label for="limite" class="form-label">Limite Mensal (R$)</label>
                            <input type="number" step="0.01" class="form-control" id="limite" name="limite" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Salvar Limite</button>
                    </form>
                </div>
            </div>
        </div>

        <hr class="my-5">

        <div class="row mt-5">
            <div class="col-md-12 text-center mb-4">
                <h2>Análises Financeiras</h2>
                <p>Veja seus dados de forma visual para entender suas finanças.</p>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-md-6 mb-4">
                <div class="card p-3">
                    <h5 class="card-title text-center">Evolução Mensal (Entradas vs. Saídas)</h5>
                    <canvas id="balanceChart"></canvas>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="card p-3">
                    <h5 class="card-title text-center">Distribuição de Despesas por Categoria</h5>
                    <canvas id="expensesChart"></canvas>
                </div>
            </div>
        </div>
        
    </div> <script>
        // Função para mostrar/esconder o formulário
        function toggleLimiteForm() {
            const form = document.getElementById('limiteForm');
            if (form.style.display === 'none' || form.style.display === '') {
                form.style.display = 'flex';
            } else {
                form.style.display = 'none';
            }
        }

        // Dados para o Gráfico de Evolução Mensal (Entradas vs. Saídas)
        const dadosAtivos = <?php echo $json_ativos; ?>;
        const dadosDespesas = <?php echo $json_despesas; ?>;
        const labels = [...new Set([...dadosAtivos.map(d => d.mes), ...dadosDespesas.map(d => d.mes)])].sort();

        const totaisAtivos = labels.map(mes => dadosAtivos.find(d => d.mes === mes)?.total || 0);
        const totaisDespesas = labels.map(mes => dadosDespesas.find(d => d.mes === mes)?.total || 0);

        const dataBalance = {
            labels: labels,
            datasets: [{
                label: 'Entradas',
                backgroundColor: 'rgba(40, 167, 69, 0.5)',
                borderColor: 'rgba(40, 167, 69, 1)',
                data: totaisAtivos,
            }, {
                label: 'Despesas',
                backgroundColor: 'rgba(220, 53, 69, 0.5)',
                borderColor: 'rgba(220, 53, 69, 1)',
                data: totaisDespesas,
            }]
        };

        const configBalance = {
            type: 'bar',
            data: dataBalance,
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        };

        const balanceChart = new Chart(
            document.getElementById('balanceChart'),
            configBalance
        );

        // Dados para o Gráfico de Pizza (Despesas por Categoria)
        const dadosDespesasCategoria = <?php echo $json_despesas_categoria; ?>;
        const categorias = dadosDespesasCategoria.map(d => d.categoria);
        const totaisCategorias = dadosDespesasCategoria.map(d => d.total);

        const dataExpenses = {
            labels: categorias,
            datasets: [{
                label: 'Total de Despesas',
                data: totaisCategorias,
                backgroundColor: [
                    '#dc3545', '#ffc107', '#28a745', '#17a2b8', '#6c757d', '#fd7e14', '#e83e8c', '#6f42c1', '#20c997', '#007bff'
                ],
                hoverOffset: 4
            }]
        };

        const configExpenses = {
            type: 'pie',
            data: dataExpenses,
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Distribuição de Despesas por Categoria'
                    }
                }
            },
        };

        const expensesChart = new Chart(
            document.getElementById('expensesChart'),
            configExpenses
        );
    </script>
</body>
</html>