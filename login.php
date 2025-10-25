<?php
// Inicia a sessão
//session_start();

// Verifica se o usuário já está logado
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    header("location: escolha.php");
    exit;
}

// Inclui o arquivo de configuração
require_once "config.php";

$nome_usuario = $senha = "";
$nome_usuario_err = $senha_err = $login_err = "";

// Processa o formulário quando ele é enviado
if($_SERVER["REQUEST_METHOD"] == "POST"){

    // Valida o nome de usuário
    if(empty(trim($_POST["nome_usuario"]))){
        $nome_usuario_err = "Por favor, insira o nome de usuário.";
    } else{
        $nome_usuario = trim($_POST["nome_usuario"]);
    }

    // Valida a senha
    if(empty(trim($_POST["senha"]))){
        $senha_err = "Por favor, insira a sua senha.";
    } else{
        $senha = trim($_POST["senha"]);
    }

    // Se não houver erros nos inputs
    if(empty($nome_usuario_err) && empty($senha_err)){
        $sql = "SELECT id, nome_usuario, senha_hash FROM usuarios WHERE nome_usuario = ?";

        if($stmt = mysqli_prepare($link, $sql)){
            mysqli_stmt_bind_param($stmt, "s", $param_nome_usuario);
            $param_nome_usuario = $nome_usuario;

            if(mysqli_stmt_execute($stmt)){
                mysqli_stmt_store_result($stmt);

                // Checa se o nome de usuário existe
                if(mysqli_stmt_num_rows($stmt) == 1){
                    mysqli_stmt_bind_result($stmt, $id, $nome_usuario, $senha_hash);
                    if(mysqli_stmt_fetch($stmt)){
                        // Verifica a senha
                        if(password_verify($senha, $senha_hash)){
                            // Senha correta, inicia uma nova sessão
                            session_start();

                            // Armazena dados da sessão
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["nome_usuario"] = $nome_usuario;

                            // Redireciona para a página de escolha <-- LINHA CORRIGIDA
                            header("location: escolha.php");
                        } else{
                            // Senha incorreta
                            $login_err = "Nome de usuário ou senha inválidos.";
                        }
                    }
                } else{
                    // Nome de usuário não existe
                    $login_err = "Nome de usuário ou senha inválidos.";
                }
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
    <title>Login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="ccr.css">
</head>
<body>
    <div class="wrapper">
        <h2>Login</h2>
        <p>Por favor, preencha suas credenciais para entrar.</p>

        <?php
        if(!empty($login_err)){
            echo '<div class="alert alert-danger">' . $login_err . '</div>';
        }
        ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label>Nome de Usuário</label>
                <input type="text" name="nome_usuario" class="form-control <?php echo (!empty($nome_usuario_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $nome_usuario; ?>">
                <span class="invalid-feedback"><?php echo $nome_usuario_err; ?></span>
            </div>
            <div class="form-group">
                <label>Senha</label>
                <input type="password" name="senha" class="form-control <?php echo (!empty($senha_err)) ? 'is-invalid' : ''; ?>">
                <span class="invalid-feedback"><?php echo $senha_err; ?></span>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Entrar">
            </div>
            <!-- <p>Não tem uma conta? <a href="registrar.php">Cadastre-se agora</a>.</p> -->
        </form>
    </div> 
</body>
</html>