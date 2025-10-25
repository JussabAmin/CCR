<?php
require_once "seguranca.php";
require_once "config.php";

$id_usuario = $_SESSION["id"];

// Arrays para armazenar os saldos
$saldos = [
    'bancos' => 0,
    'caixa_geral' => 0, // Saldo base da caixa geral (pode ter transações diretas)
    'caixa_mf' => 0,
    'caixa_luis' => 0,
    'caixa_abdul' => 0,
    'caixa_tesouraria' => 0,
    // Chaves para os detalhes da Caixa MF
    'caixa_mf_bci_ordem' => 0,
    'caixa_mf_bci_credito' => 0,
    'caixa_mf_numerario' => 0,
];

// --- Lógica de Cálculo ---
$query_ativos = "SELECT origem, SUM(valor) as total FROM ativos WHERE usuario_id = ? GROUP BY origem";
if ($stmt = mysqli_prepare($link, $query_ativos)) {
    mysqli_stmt_bind_param($stmt, "i", $id_usuario);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $origem_lower = strtolower($row['origem']);
        if ($origem_lower == 'bci conta ordem') { // Ajuste o nome se necessário
            $saldos['caixa_mf_bci_ordem'] += $row['total'];
        } elseif ($origem_lower == 'bci conta credito') { // Ajuste o nome se necessário
             $saldos['caixa_mf_bci_credito'] += $row['total']; // Ou outra lógica se for dívida
        } elseif ($origem_lower == 'numerario') { // Ajuste o nome se necessário
             $saldos['caixa_mf_numerario'] += $row['total'];
        }
        elseif (array_key_exists($origem_lower, $saldos)) {
            $saldos[$origem_lower] += $row['total'];
        }
    }
    mysqli_stmt_close($stmt);
}

$categorias_relevantes_str = "'bancos','caixa_geral','caixa_mf','caixa_luis','caixa_abdul','caixa_tesouraria','bci conta ordem','bci conta credito','numerario'"; // Adicionados detalhes
$query_despesas = "SELECT categoria, SUM(valor) as total FROM despesas WHERE usuario_id = ? AND LOWER(categoria) IN ($categorias_relevantes_str) GROUP BY categoria";
if ($stmt = mysqli_prepare($link, $query_despesas)) {
    mysqli_stmt_bind_param($stmt, "i", $id_usuario);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $categoria_lower = strtolower($row['categoria']);
        if ($categoria_lower == 'bci conta ordem') {
            $saldos['caixa_mf_bci_ordem'] -= $row['total'];
        } elseif ($categoria_lower == 'bci conta credito') {
             $saldos['caixa_mf_bci_credito'] -= $row['total']; // Ou outra lógica
        } elseif ($categoria_lower == 'numerario') {
             $saldos['caixa_mf_numerario'] -= $row['total'];
        }
        elseif (array_key_exists($categoria_lower, $saldos)) {
             $saldos[$categoria_lower] -= $row['total'];
         }
    }
    mysqli_stmt_close($stmt);
}

// Saldo da Caixa MF para exibição
$saldo_caixa_mf_display = $saldos['caixa_mf_bci_ordem'] + $saldos['caixa_mf_bci_credito'] + $saldos['caixa_mf_numerario'];

// Saldo da Caixa Geral (Soma APENAS de Luís, Abdul, Tesouraria)
$saldo_caixa_geral_calculado = $saldos['caixa_luis'] + $saldos['caixa_abdul'] + $saldos['caixa_tesouraria'];

// Saldo Total Geral (Soma Bancos + Caixa Luís + Caixa Abdul + Caixa Tesouraria)
$saldo_total_geral = $saldos['bancos'] + $saldo_caixa_geral_calculado;


mysqli_close($link);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Saldo Geral - CCR</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="ccr.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        .card-saldo .card-title { font-size: 1rem; margin-bottom: 0.5rem; }
        .card-saldo .saldo-valor { font-size: 2rem; font-weight: 500; }
        .card-detalhe .saldo-valor { font-size: 1.5rem; }
        .moza-logo { max-width: 100px; height: auto; margin-bottom: 1rem; }
        a.card-link { text-decoration: none; color: inherit; }
        a.card-link:hover .card { transform: translateY(-3px); box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        .caixa-detalhes-group { border: 1px solid #dee2e6; border-radius: 0.5rem; padding: 1.5rem 1rem 0.5rem 1rem; background-color: #f8f9fa; margin-top: -1rem; padding-top: 2.5rem; position: relative; z-index: 1; }
        .card-caixa-geral { z-index: 2; position: relative; }
        .card-caixa-mf { border-left: 5px solid #6c757d; }
    </style>
</head>
<body>

    <div class="container mt-4">

        <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
            <a href="dashboard.php" class="btn btn-outline-primary me-2"> <i class="fas fa-tachometer-alt me-1"></i> Painel Central</a>
            <img src="img/android-chrome-192x192.png" alt="Logo CCR" width="80">
            <div>
                <a href="visualizar_registros.php" class="btn btn-outline-secondary me-2"><i class="fas fa-list-alt me-1"></i> Meus Registos</a>
                <a href="logout.php" class="btn btn-outline-danger"><i class="fas fa-sign-out-alt me-1"></i> Sair</a>
            </div>
        </div>

        <h1 class="mb-4 text-center display-5">Saldo Geral</h1>

        <div class="row justify-content-center mb-5">
            <div class="col-lg-8 col-md-10">
                <div class="card text-white bg-primary shadow-sm card-saldo">
                    <div class="card-body text-center">
                        <h5 class="card-title">Saldo Total Geral</h5>
                        <p class="saldo-valor display-4">MT <?php echo number_format($saldo_total_geral, 2, ',', '.'); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4 justify-content-center g-4">

            <div class="col-lg-6 mb-4">
                 <h2 class="h5 mb-3 text-center text-muted">Saldo Bancário</h2>
                 <div class="card bg-success-subtle border-success shadow-sm card-saldo h-100">
                    <div class="card-body text-center d-flex flex-column justify-content-center align-items-center">
                        <img src="img/moza_banco_logo.png" alt="Moza Banco Logo" class="moza-logo rounded-3">
                        <h5 class="card-title text-success-emphasis mt-2">Moza Banco</h5>
                        <p class="saldo-valor text-success-emphasis" id="saldo-bancos">MT <?php echo number_format($saldos['bancos'], 2, ',', '.'); ?></p>
                        <button class="btn btn-sm btn-outline-success mt-3 mx-auto" style="max-width: 100px;" data-bs-toggle="modal" data-bs-target="#editModal" data-tipo="bancos" data-saldo="<?php echo $saldos['bancos']; ?>">
                           <i class="fas fa-edit me-1"></i> Editar
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 mb-4">
                <h2 class="h5 mb-3 text-center text-muted">Saldo em Caixa</h2>

                <div class="card bg-warning-subtle border-warning shadow-sm card-saldo card-caixa-geral mb-0">
                    <div class="card-body text-center d-flex flex-column">
                        <h5 class="card-title text-warning-emphasis">Saldo Total em Caixa </h5>
                        <p class="saldo-valor text-warning-emphasis mt-auto" id="saldo-caixa-geral">MT <?php echo number_format($saldo_caixa_geral_calculado, 2, ',', '.'); ?></p>
                         <button class="btn btn-sm btn-outline-warning mt-3 mx-auto" style="max-width: 100px;" data-bs-toggle="modal" data-bs-target="#editModal" data-tipo="caixa_geral" data-saldo="<?php echo $saldos['caixa_geral']; ?>">
                            <i class="fas fa-edit me-1"></i> Editar Saldo Base Caixa
                        </button>
                    </div>
                </div>

                <div class="caixa-detalhes-group shadow-sm">
                    <h6 class="text-center text-muted mb-3 small">Detalhes da Caixa</h6>
                    <div class="row justify-content-center g-3">

                        <div class="col-6 col-md-6 col-lg-6">
                            <a href="caixa_mf_detalhes.php" class="card-link">
                                <div class="card bg-light border card-saldo card-detalhe h-100 card-caixa-mf">
                                    <div class="card-body text-center p-2">
                                        <h6 class="card-title small">Caixa MF </h6>
                                        <p class="saldo-valor">MT <?php echo number_format($saldo_caixa_mf_display, 2, ',', '.'); ?></p>
                                    </div>
                                </div>
                             </a>
                        </div>

                        <div class="col-6 col-md-6 col-lg-6">
                            <div class="card bg-light border card-saldo card-detalhe h-100">
                                <div class="card-body text-center p-2">
                                    <h6 class="card-title small">Caixa Luís</h6>
                                    <p class="saldo-valor">MT <?php echo number_format($saldos['caixa_luis'], 2, ',', '.'); ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="col-6 col-md-6 col-lg-6 mt-3">
                            <div class="card bg-light border card-saldo card-detalhe h-100">
                                <div class="card-body text-center p-2">
                                    <h6 class="card-title small">Caixa Abdul</h6>
                                    <p class="saldo-valor">MT <?php echo number_format($saldos['caixa_abdul'], 2, ',', '.'); ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="col-6 col-md-6 col-lg-6 mt-3">
                            <div class="card bg-light border card-saldo card-detalhe h-100">
                                <div class="card-body text-center p-2">
                                    <h6 class="card-title small">Caixa Tesouraria</h6>
                                    <p class="saldo-valor">MT <?php echo number_format($saldos['caixa_tesouraria'], 2, ',', '.'); ?></p>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>

    </div>

    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
         <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Editar Saldo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editForm">
                        <input type="hidden" id="editTipo">
                        <div class="mb-3">
                            <label for="inputSaldo" class="form-label">Novo Saldo</label>
                            <input type="number" class="form-control" id="inputSaldo" step="0.01">
                        </div>
                        <div class="mb-3">
                            <label for="inputSenha" class="form-label">Senha Especial</label>
                            <input type="password" class="form-control" id="inputSenha">
                        </div>
                        <div id="alertMessage" class="alert d-none mt-3" role="alert"></div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnSalvar">Salvar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        $(document).ready(function() {
            // Código jQuery para o modal
            $('#editModal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget); var tipo = button.data('tipo'); var saldo = button.data('saldo');
                var modal = $(this); modal.find('#editModalLabel').text('Editar Saldo - ' + (tipo === 'bancos' ? 'Moza Banco' : 'Caixa Geral (Base)')); modal.find('#editTipo').val(tipo); modal.find('#inputSaldo').val(saldo); modal.find('#inputSenha').val(''); modal.find('#alertMessage').addClass('d-none');
            });
            $('#btnSalvar').click(function() {
                var tipo = $('#editTipo').val(); var novoSaldo = $('#inputSaldo').val(); var senha = $('#inputSenha').val(); var alert = $('#alertMessage');
                $.ajax({ url: 'atualizar_saldo.php', type: 'POST', data: { tipo: tipo, novo_saldo: novoSaldo, senha_especial: senha }, dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            alert.removeClass('alert-danger d-none').addClass('alert-success').text(response.message);
                            if (tipo === 'bancos' || tipo === 'caixa_geral') {
                                $('#saldo-' + tipo.replace('_', '-')).text(parseFloat(novoSaldo).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' MT');
                            }
                            setTimeout(function() { $('#editModal').modal('hide'); location.reload(); }, 1500);
                        } else {
                            alert.removeClass('alert-success d-none').addClass('alert-danger').text(response.message || 'Ocorreu um erro.');
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                         console.error("Erro AJAX:", textStatus, errorThrown);
                         alert.removeClass('alert-success d-none').addClass('alert-danger').text('Erro de comunicação com o servidor.');
                    }
                });
            });
            // Correção Acessibilidade Modal
            $('#editModal').on('hidden.bs.modal', function (e) { document.body.focus(); });
        });
    </script>
</body>
</html>