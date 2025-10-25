<?php
require_once "seguranca.php";
require_once "config.php";

// Verifica se o usuário está logado


$descricao = $valor = $categoria = $data = $anexo = "";
$descricao_err = $valor_err = $categoria_err = $data_err = "";

// Processa o formulário quando ele é enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Valida a descrição
    if (empty(trim($_POST["descricao"]))) {
        $descricao_err = "Por favor, insira uma descrição.";
    } else {
        $descricao = trim($_POST["descricao"]);
    }

    // Valida o valor
    if (empty(trim($_POST["valor"]))) {
        $valor_err = "Por favor, insira um valor.";
    } elseif (!is_numeric(str_replace(',', '.', trim($_POST["valor"])))) {
        $valor_err = "Por favor, insira um valor numérico.";
    } else {
        $valor = floatval(str_replace(',', '.', trim($_POST["valor"])));
    }

    // Valida a categoria (se necessário)
    $categoria = trim($_POST["categoria"]);
    if (empty($categoria)) {
        $categoria = "Outros"; // Valor padrão
    }

    // Valida a data
    if (empty(trim($_POST["data"]))) {
        $data_err = "Por favor, selecione uma data.";
    } else {
        $data = trim($_POST["data"]);
    }

    // Processa o upload do anexo
    if (isset($_FILES["anexo"]) && $_FILES["anexo"]["error"] == 0) {
        $diretorio_anexos = "anexos/";
        $nome_arquivo = basename($_FILES["anexo"]["name"]);
        $caminho_arquivo = $diretorio_anexos . uniqid() . "-" . $nome_arquivo;
        $tipo_arquivo = strtolower(pathinfo($caminho_arquivo, PATHINFO_EXTENSION));

        // Permite certos tipos de arquivos
        $tipos_permitidos = array("jpg", "png", "pdf", "docx");
        if (in_array($tipo_arquivo, $tipos_permitidos)) {
            if (move_uploaded_file($_FILES["anexo"]["tmp_name"], $caminho_arquivo)) {
                $anexo = $caminho_arquivo;
            } else {
                echo "Desculpe, houve um erro ao enviar seu arquivo.";
            }
        } else {
            echo "Desculpe, apenas arquivos JPG, PNG, PDF e DOCX são permitidos.";
        }
    }

    // Insere no banco de dados se não houver erros
    if (empty($descricao_err) && empty($valor_err) && empty($data_err)) {
        $sql = "INSERT INTO despesas (usuario_id, descricao, valor, categoria, data, anexo) VALUES (?, ?, ?, ?, ?, ?)";

        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "isdsss", $param_usuario_id, $param_descricao, $param_valor, $param_categoria, $param_data, $param_anexo);

            $param_usuario_id = $_SESSION["id"];
            $param_descricao = $descricao;
            $param_valor = $valor;
            $param_categoria = $categoria;
            $param_data = $data;
            $param_anexo = $anexo;

            if (mysqli_stmt_execute($stmt)) {
                header("location: dashboard.php");
            } else {
                echo "Ops! Algo deu errado. Por favor, tente novamente mais tarde.";
            }
            mysqli_stmt_close($stmt);
        }
    }
    mysqli_close($link);
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Adicionar Despesa</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="ccr.css">
</head>
<body>
    <div class="wrapper">
        <h2>Adicionar Nova Despesa</h2>
        <p>Preencha os detalhes da sua despesa.</p>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label>Descrição</label>
                <input type="text" name="descricao" class="form-control <?php echo (!empty($descricao_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $descricao; ?>">
                <span class="invalid-feedback"><?php echo $descricao_err; ?></span>
            </div>
            <div class="form-group">
                <label>Valor</label>
                <input type="text" name="valor" class="form-control <?php echo (!empty($valor_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $valor; ?>">
                <span class="invalid-feedback"><?php echo $valor_err; ?></span>
            </div>
            <div class="form-group">
                <label>Categoria</label>
                <input type="text" name="categoria" class="form-control" value="<?php echo $categoria; ?>">
            </div>
            <div class="form-group">
                <label>Data</label>
                <input type="date" name="data" class="form-control <?php echo (!empty($data_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $data; ?>">
                <span class="invalid-feedback"><?php echo $data_err; ?></span>
            </div>
            <div class="form-group">
                <label>Anexar Arquivo (opcional)</label>
                <input type="file" name="anexo" class="form-control">
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Adicionar Despesa">
            </div>
            <p><a href="dashboard.php">Voltar para o Painel</a>.</p>
        </form>
    </div>
</body>
</html>