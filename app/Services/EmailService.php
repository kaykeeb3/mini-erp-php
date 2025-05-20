<?php
namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dotenv\Dotenv;

class EmailService
{
    private static $envLoaded = false;

    private static function loadEnv()
    {
        if (!self::$envLoaded) {
            $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
            $dotenv->load();
            self::$envLoaded = true;
        }
    }

    public static function send($to, $subject, $body)
    {
        self::loadEnv();

        $mail = new PHPMailer(true);

        try {
            // Configurações SMTP lendo do .env
            $mail->isSMTP();
            $mail->Host = $_ENV['MAIL_HOST'] ?? 'sandbox.smtp.mailtrap.io';
            $mail->SMTPAuth = true;
            $mail->Username = $_ENV['MAIL_USERNAME'] ?? '';
            $mail->Password = $_ENV['MAIL_PASSWORD'] ?? '';
            $mail->Port = intval($_ENV['MAIL_PORT'] ?? 2525);

            $encryption = strtolower($_ENV['MAIL_ENCRYPTION'] ?? 'tls');
            if ($encryption === 'ssl') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } elseif ($encryption === 'tls') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            } else {
                $mail->SMTPSecure = false; // sem criptografia
            }

            // Remetente e destinatário
            $mail->setFrom(
                $_ENV['MAIL_FROM_ADDRESS'] ?? 'noreply@mini-erp.test',
                $_ENV['MAIL_FROM_NAME'] ?? 'Mini ERP'
            );
            $mail->addAddress($to);

            // Conteúdo do e-mail
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = nl2br($body);

            $mail->send();

            return true;
        } catch (Exception $e) {
            error_log("Mailer Error: " . $mail->ErrorInfo);
            return false;
        }
    }
}
