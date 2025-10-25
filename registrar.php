<?php
// Inclui o arquivo de configuração
require_once "config.php";

$nome_usuario = $senha = $confirma_senha = "";
$nome_usuario_err = $senha_err = $confirma_senha_err = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){

    // Valida o nome de usuário
    if(empty(trim($_POST["nome_usuario"]))){
        $nome_usuario_err = "Por favor, insira o nome de usuário.";
    } else{
        // Prepara a consulta para checar se o usuário já existe
        $sql = "SELECT id FROM usuarios WHERE nome_usuario = ?";

        if($stmt = mysqli_prepare($link, $sql)){
            mysqli_stmt_bind_param($stmt, "s", $param_nome_usuario);
            $param_nome_usuario = trim($_POST["nome_usuario"]);

            if(mysqli_stmt_execute($stmt)){
                mysqli_stmt_store_result($stmt);
                if(mysqli_stmt_num_rows($stmt) == 1){
                    $nome_usuario_err = "Este nome de usuário já está em uso.";
                } else{
                    $nome_usuario = trim($_POST["nome_usuario"]);
                }
            } else{
                echo "Ops! Algo deu errado. Por favor, tente novamente mais tarde.";
            }
            mysqli_stmt_close($stmt);
        }
    }

    // Valida a senha
    if(empty(trim($_POST["senha"]))){
        $senha_err = "Por favor, insira uma senha.";
    } elseif(strlen(trim($_POST["senha"])) < 6){
        $senha_err = "A senha deve ter pelo menos 6 caracteres.";
    } else{
        $senha = trim($_POST["senha"]);
    }

    // Valida a confirmação da senha
    if(empty(trim($_POST["confirma_senha"]))){
        $confirma_senha_err = "Por favor, confirme a senha.";
    } else{
        $confirma_senha = trim($_POST["confirma_senha"]);
        if(empty($senha_err) && ($senha != $confirma_senha)){
            $confirma_senha_err = "As senhas não coincidem.";
        }
    }

    // Se os inputs estiverem corretos, insere o usuário no banco
    if(empty($nome_usuario_err) && empty($senha_err) && empty($confirma_senha_err)){
        $sql = "INSERT INTO usuarios (nome_usuario, senha_hash) VALUES (?, ?)";

        if($stmt = mysqli_prepare($link, $sql)){
            mysqli_stmt_bind_param($stmt, "ss", $param_nome_usuario, $param_senha_hash);

            // Cria um hash de senha
            $param_nome_usuario = $nome_usuario;
            $param_senha_hash = password_hash($senha, PASSWORD_DEFAULT); // Usa o algoritmo de hash padrão

            if(mysqli_stmt_execute($stmt)){
                header("location: login.php");
            } else{
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
    <title>Registrar</title>
     <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="ccr.css">
    <style>
        body{ font: 14px sans-serif; }
        .wrapper{ max-width: 420px; padding: 20px; margin: auto; }
    </style>
</head>
<body>
    <div class="wrapper">
        <h2>Registrar</h2>
        <p>Por favor, preencha este formulário para criar uma conta.</p>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label>Nome de Usuário</label>
                <input type="text" name="nome_usuario" class="form-control <?php echo (!empty($nome_usuario_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $nome_usuario; ?>">
                <span class="invalid-feedback"><?php echo $nome_usuario_err; ?></span>
            </div>
            <div class="form-group">
                <label>Senha</label>
                <input type="password" name="senha" class="form-control <?php echo (!empty($senha_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $senha; ?>">
                <span class="invalid-feedback"><?php echo $senha_err; ?></span>
            </div>
            <div class="form-group">
                <label>Confirmar Senha</label>
                <input type="password" name="confirma_senha" class="form-control <?php echo (!empty($confirma_senha_err)) ? 'is-invalid' : ''; ?>">
                <span class="invalid-feedback"><?php echo $confirma_senha_err; ?></span>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Registrar">
                <input type="reset" class="btn btn-secondary ml-2" value="Limpar">
            </div>
            <p>Já tem uma conta? <a href="login.php">Faça login aqui</a>.</p>
        </form>
    </div>
</body>
</html>