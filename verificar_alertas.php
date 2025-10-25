<?php
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once "config.php"; // Inclua o arquivo de configuração do banco de dados

// Configurações do seu servidor SMTP (substitua pelos seus dados)
$mail = new PHPMailer(true);
$mail->isSMTP();
$mail->Host = 'seu_servidor_smtp.com'; // Ex: smtp.gmail.com
$mail->SMTPAuth = true;
$mail->Username = 'seu_email@exemplo.com';
$mail->Password = 'sua_senha';
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port = 587;

try {
    // Consulta para pegar o email do usuário e o limite
    $sql_usuarios = "SELECT id, email, limite_despesa FROM usuarios WHERE limite_despesa > 0";
    $result_usuarios = mysqli_query($link, $sql_usuarios);

    while ($usuario = mysqli_fetch_assoc($result_usuarios)) {
        $id_usuario = $usuario['id'];
        $email_usuario = $usuario['email'];
        $limite = $usuario['limite_despesa'];
        $mes_atual = date('Y-m');

        // Consulta para somar as despesas do mês do usuário
        $sql_despesas = "SELECT SUM(valor) as total_despesas FROM despesas WHERE usuario_id = ? AND DATE_FORMAT(data, '%Y-%m') = ?";
        if ($stmt = mysqli_prepare($link, $sql_despesas)) {
            mysqli_stmt_bind_param($stmt, "is", $id_usuario, $mes_atual);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $despesas = mysqli_fetch_assoc($result);
            $total_despesas = $despesas['total_despesas'] ?? 0;

            if ($total_despesas >= $limite) {
                // Envio do e-mail de alerta
                $mail->clearAddresses();
                $mail->setFrom('seu_email@exemplo.com', 'CCR-Finanças');
                $mail->addAddress($email_usuario);
                $mail->isHTML(true);
                $mail->Subject = 'Alerta de Despesa - CCR-Finanças';
                $mail->Body = "Olá,<br><br>Suas despesas totais neste mês já atingiram o seu limite de R$ " . number_format($limite, 2, ',', '.') . ".<br><br>Verifique seus registros para gerenciar suas finanças.<br><br>Atenciosamente,<br>Equipe CCR-Finanças";
                $mail->send();
                echo "Alerta enviado para " . $email_usuario . "<br>";
            }
        }
    }
} catch (Exception $e) {
    echo "Erro ao enviar e-mail: " . $mail->ErrorInfo;
}
mysqli_close($link);
?>