<?php
require_once "seguranca.php";
require_once "config.php";

$descricao = $valor = $categoria_origem = $data = $anexo_existente = "";
$descricao_err = $valor_err = $data_err = "";

$id = $tipo = "";

if (isset($_GET["id"]) && !empty(trim($_GET["id"])) && isset($_GET["tipo"]) && !empty(trim($_GET["tipo"]))) {
    $id = trim($_GET["id"]);
    $tipo = trim($_GET["tipo"]);

    // Seleciona a tabela correta e a coluna da categoria/origem
    $tabela = ($tipo == "despesa") ? "despesas" : "ativos";
    $campo_cat_origem = ($tipo == "despesa") ? "categoria" : "origem";

    // Busca os dados do registro
    $sql_fetch = "SELECT descricao, valor, " . $campo_cat_origem . ", data, anexo FROM " . $tabela . " WHERE id = ? AND usuario_id = ?";
    if ($stmt = mysqli_prepare($link, $sql_fetch)) {
        mysqli_stmt_bind_param($stmt, "ii", $param_id, $param_usuario_id);
        $param_id = $id;
        $param_usuario_id = $_SESSION["id"];

        if (mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            if (mysqli_num_rows($result) == 1) {
                $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
                $descricao = $row["descricao"];
                $valor = $row["valor"];
                $data = $row["data"];
                $anexo_existente = $row["anexo"];
                $categoria_origem = $row[$campo_cat_origem];
            } else {
                echo "Registro não encontrado.";
                exit;
            }
        } else {
            echo "Ops! Algo deu errado. Tente novamente.";
            exit;
        }
        mysqli_stmt_close($stmt);
    }
} elseif ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Processa a atualização
    $id = trim($_POST["id"]);
    $tipo = trim($_POST["tipo"]);
    $anexo_existente = trim($_POST["anexo_existente"]);

    if (empty(trim($_POST["descricao"]))) {
        $descricao_err = "Insira uma descrição.";
    } else {
        $descricao = trim($_POST["descricao"]);
    }

    if (empty(trim($_POST["valor"]))) {
        $valor_err = "Insira um valor.";
    } elseif (!is_numeric(str_replace(',', '.', trim($_POST["valor"])))) {
        $valor_err = "Insira um valor numérico.";
    } else {
        $valor = floatval(str_replace(',', '.', trim($_POST["valor"])));
    }

    $categoria_origem = trim($_POST["categoria_origem"]);
    if (empty(trim($_POST["data"]))) {
        $data_err = "Selecione uma data.";
    } else {
        $data = trim($_POST["data"]);
    }

    $anexo = $anexo_existente;
    if (isset($_FILES["anexo"]) && $_FILES["anexo"]["error"] == 0) {
        $diretorio_anexos = "anexos/";
        $nome_arquivo = basename($_FILES["anexo"]["name"]);
        $caminho_arquivo = $diretorio_anexos . uniqid() . "-" . $nome_arquivo;
        $tipo_arquivo = strtolower(pathinfo($caminho_arquivo, PATHINFO_EXTENSION));

        $tipos_permitidos = array("jpg", "png", "pdf", "docx");
        if (in_array($tipo_arquivo, $tipos_permitidos)) {
            if (move_uploaded_file($_FILES["anexo"]["tmp_name"], $caminho_arquivo)) {
                $anexo = $caminho_arquivo;
                // Opcional: remover o anexo antigo se for substituído
                // if (!empty($anexo_existente) && file_exists($anexo_existente)) {
                //     unlink($anexo_existente);
                // }
            } else {
                echo "Erro ao enviar arquivo.";
            }
        } else {
            echo "Apenas JPG, PNG, PDF e DOCX são permitidos.";
        }
    }

    if (empty($descricao_err) && empty($valor_err) && empty($data_err)) {
        $tabela = ($tipo == "despesa") ? "despesas" : "ativos";
        $campo_cat_origem = ($tipo == "despesa") ? "categoria" : "origem";
        
        $sql_update = "UPDATE " . $tabela . " SET descricao = ?, valor = ?, " . $campo_cat_origem . " = ?, data = ?, anexo = ? WHERE id = ? AND usuario_id = ?";
        
        if ($stmt = mysqli_prepare($link, $sql_update)) {
            mysqli_stmt_bind_param($stmt, "sdsssii", $param_descricao, $param_valor, $param_categoria_origem, $param_data, $param_anexo, $param_id, $param_usuario_id);
            
            $param_descricao = $descricao;
            $param_valor = $valor;
            $param_categoria_origem = $categoria_origem;
            $param_data = $data;
            $param_anexo = $anexo;
            $param_id = $id;
            $param_usuario_id = $_SESSION["id"]; // Parâmetro para a condição WHERE

            if (mysqli_stmt_execute($stmt)) {
                header("location: visualizar_registros.php");
                exit;
            } else {
                echo "Ops! Algo deu errado. Tente novamente.";
            }
            mysqli_stmt_close($stmt);
        }
    }
} else {
    header("location: visualizar_registros.php");
    exit;
}

mysqli_close($link);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Editar Registro</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="wrapper">
        <h2>Editar <?php echo ucfirst($tipo); ?></h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo $id; ?>">
            <input type="hidden" name="tipo" value="<?php echo $tipo; ?>">
            <input type="hidden" name="anexo_existente" value="<?php echo htmlspecialchars($anexo_existente); ?>">

            <div class="form-group">
                <label>Descrição</label>
                <input type="text" name="descricao" class="form-control <?php echo (!empty($descricao_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($descricao); ?>">
                <span class="invalid-feedback"><?php echo $descricao_err; ?></span>
            </div>
            <div class="form-group">
                <label>Valor</label>
                <input type="text" name="valor" class="form-control <?php echo (!empty($valor_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($valor); ?>">
                <span class="invalid-feedback"><?php echo $valor_err; ?></span>
            </div>
            <div class="form-group">
                <label>Categoria/Origem</label>
                <input type="text" name="categoria_origem" class="form-control" value="<?php echo htmlspecialchars($categoria_origem); ?>">
            </div>
            <div class="form-group">
                <label>Data</label>
                <input type="date" name="data" class="form-control <?php echo (!empty($data_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($data); ?>">
                <span class="invalid-feedback"><?php echo $data_err; ?></span>
            </div>
            <div class="form-group">
                <label>Anexar Novo Arquivo (opcional)</label>
                <?php if ($anexo_existente): ?>
                    <p>Anexo atual: <a href="<?php echo htmlspecialchars($anexo_existente); ?>" target="_blank">Ver Anexo</a></p>
                <?php endif; ?>
                <input type="file" name="anexo" class="form-control">
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Salvar Alterações">
                <a href="visualizar_registros.php" class="btn btn-secondary ml-2">Cancelar</a>
            </div>
        </form>
    </div>
</body>
</html>