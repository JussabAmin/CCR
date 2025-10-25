<?php
require_once "seguranca.php";
require_once "config.php";
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Importar Despesas - CCR</title>
    
    <link rel="apple-touch-icon" sizes="180x180" href="img/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="img/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="img/favicon-16x16.png">
    <link rel="manifest" href="img/site.webmanifest">
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="ccr.css">
</head>
<body>
    <div class="content-container p-4">
        <h1 class="my-5 text-center">Importar Despesas do Excel</h1>
        
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card p-4">
                    <p class="text-center">Por favor, prepare seu arquivo Excel com as colunas na seguinte ordem:</p>
                    <ul class="list-unstyled text-center fw-bold">
                        <li>Coluna A: Valor</li>
                        <li>Coluna B: Data (formato YYYY-MM-DD)</li>
                        <li>Coluna C: Categoria</li>
                        <li>Coluna D: Descrição</li>
                    </ul>
                    <hr>
                    
                    <?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
                        <div class="alert alert-success">Importação concluída com sucesso!</div>
                    <?php elseif (isset($_GET['status']) && $_GET['status'] == 'error'): ?>
                        <div class="alert alert-danger">Erro na importação. Verifique o formato do arquivo ou tente novamente.</div>
                    <?php endif; ?>

                    <form action="processar_importacao.php" method="post" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="excel_file" class="form-label">Selecione o arquivo Excel (.xlsx)</label>
                            <input type="file" class="form-control" id="excel_file" name="excel_file" accept=".xlsx" required>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Importar Despesas pelo Excel</button>
                            <a href="dashboard.php" class="btn btn-secondary">Voltar a Central de Controlo</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>