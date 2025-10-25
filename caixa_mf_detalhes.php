<?php
require_once "seguranca.php";
require_once "config.php";

$id_usuario = $_SESSION["id"];

// --- FUTURAMENTE: Buscar dados específicos aqui ---
$saldo_bci_ordem = 1234.56; // Valor de exemplo
$saldo_bci_credito = 789.01; // Valor de exemplo
$saldo_numerario = 550.75; // Valor de exemplo para numerário

// mysqli_close($link); // Comentado pois pode ser necessário depois
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Detalhes Caixa MF - CCR</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="ccr.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        .saldo-bci { font-size: 1.75rem; font-weight: 500; }
        .card-header { font-weight: bold; }
    </style>
</head>
<body>

    <div class="container mt-4">

        <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
            <a href="saldo_geral.php" class="btn btn-outline-secondary me-2"> <i class="fas fa-arrow-left me-1"></i> Voltar ao Saldo Geral</a>
            <img src="img/android-chrome-192x192.png" alt="Logo CCR" width="80">
            <div>
                 <a href="dashboard.php" class="btn btn-outline-primary me-2"><i class="fas fa-tachometer-alt me-1"></i> Painel Central</a>
                <a href="logout.php" class="btn btn-outline-danger"><i class="fas fa-sign-out-alt me-1"></i> Sair</a>
            </div>
        </div>

        <h1 class="mb-5 text-center display-5">Detalhes da Caixa MF</h1>

        <div class="row justify-content-center g-4">

            <div class="col-md-6 col-lg-4">
                <div class="card border-info shadow-sm h-100">
                    <div class="card-header bg-info text-white">
                        <i class="fas fa-university me-2"></i> BCI - Conta à Ordem
                    </div>
                    <div class="card-body text-center">
                        <h5 class="card-title text-muted mb-3">Saldo Disponível</h5>
                        <p class="saldo-bci text-info">R$ <?php echo number_format($saldo_bci_ordem, 2, ',', '.'); ?></p>
                        </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                 <div class="card border-primary shadow-sm h-100">
                     <div class="card-header bg-primary text-white">
                        <i class="fas fa-credit-card me-2"></i> BCI - Conta a Crédito
                    </div>
                    <div class="card-body text-center">
                        <h5 class="card-title text-muted mb-3">Saldo Utilizado</h5>
                        <p class="saldo-bci text-primary">R$ <?php echo number_format($saldo_bci_credito, 2, ',', '.'); ?></p>
                         </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                 <div class="card border-success shadow-sm h-100">
                     <div class="card-header bg-success text-white">
                        <i class="fas fa-money-bill-wave me-2"></i> Dinheiro em Numerário
                    </div>
                    <div class="card-body text-center">
                        <h5 class="card-title text-muted mb-3">Valor em Caixa</h5>
                        <p class="saldo-bci text-success">R$ <?php echo number_format($saldo_numerario, 2, ',', '.'); ?></p>
                         </div>
                </div>
            </div>

        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>