<?php
// A linha mais importante: garante que apenas usuários logados acessem esta página.

require_once "config.php";

// Pega o nome do usuário da sessão para uma mensagem de boas-vindas.
// Se você guardar o nome em outra variável de sessão (ex: 'nome'), troque 'username' abaixo.

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Portal de Seleção - CCR Central</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="ccr.css">
    <style>
        /* Estilo para garantir que o conteúdo fique centralizado na tela */
        body, html {
            height: 100%;
        }
        .container-portal {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100%;
        }
        .card {
            transition: transform 0.2s;
        }
        .card:hover {
            transform: scale(1.05);
        }
    </style>
</head>
<body>

    <div class="container-portal text-center">
        <img src="img/android-chrome-192x192.png" alt="Logo CCR" width="100" class="mb-4">
        
        <h1 class="display-5">Bem-vindo</h1>
        <p class="lead mb-5">Escolha uma área do sistema para acessar.</p>

        <div class="row justify-content-center w-100">
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">Painel Financeiro</h5>
                        <p class="card-text">Acesse o dashboard com resumos de ativos, despesas, saldos e análises financeiras.</p>
                        <a href="dashboard.php" class="btn btn-primary mt-auto">Acessar Finanças</a>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">Agenda Empresarial</h5>
                        <p class="card-text">Gerencie suas tarefas, reuniões, pagamentos recorrentes e veja o histórico de atividades.</p>
                        <a href="Agenda/agenda.php" class="btn btn-success mt-auto">Acessar Agenda</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-5">
            <a href="logout.php" class="btn btn-outline-danger">Sair do Sistema</a>
        </div>
    </div>

</body>
</html>