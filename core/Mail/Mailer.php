<?php
declare(strict_types=1);

namespace Core\Mail;

use PHPMailer\PHPMailer\Exception as PHPMailerException;
use PHPMailer\PHPMailer\PHPMailer;
use RuntimeException;

/**
 * წერილის გაგზავნა SMTP-ით (PHPMailer). ლოკალურად — Mailpit (127.0.0.1:1025), წერილები http://127.0.0.1:8025-ზე ჩანს.
 *
 * @phpstan-type MailConfig array{host: string, port: int, encryption: string, username: string, password: string, from_email: string, from_name: string}
 */
final class Mailer
{
    private ?PHPMailer $smtp = null;

    /** @param MailConfig $config encryption: '' | 'tls' | 'ssl' */
    public function __construct(private readonly array $config)
    {
    }

    /**
     * @param array<string, string> $headers დამატებითი header-ები (მაგ. List-Unsubscribe)
     * @throws RuntimeException გაგზავნა ვერ მოხერხდა
     */
    public function send(string $to, string $subject, string $html, string $text = '', array $headers = []): void
    {
        $mail = $this->mailer();
        try {
            $mail->clearAllRecipients();
            $mail->clearCustomHeaders();
            $mail->addAddress($to);
            $mail->Subject = $subject;
            $mail->Body = $html;
            $mail->AltBody = $text !== '' ? $text : trim(html_entity_decode(strip_tags((string)preg_replace('/<br\s*\/?>|<\/p>/i', "\n", $html)), ENT_QUOTES, 'UTF-8'));
            foreach ($headers as $name => $value) {
                $mail->addCustomHeader($name, $value);
            }
            $mail->send();
        } catch (PHPMailerException $e) {
            throw new RuntimeException('Mail error: ' . $mail->ErrorInfo, 0, $e);
        }
    }

    /** SMTP კავშირი ერთხელ იხსნება და პარტიის ყველა წერილს ემსახურება (newsletter) */
    private function mailer(): PHPMailer
    {
        if ($this->smtp !== null) {
            return $this->smtp;
        }
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = $this->config['host'];
        $mail->Port = (int)$this->config['port'];
        $mail->SMTPKeepAlive = true;
        $mail->Timeout = 15;
        $mail->CharSet = PHPMailer::CHARSET_UTF8;
        $mail->Encoding = PHPMailer::ENCODING_BASE64;
        $mail->isHTML(true);

        $mail->SMTPAuth = $this->config['username'] !== '';
        $mail->Username = $this->config['username'];
        $mail->Password = $this->config['password'];
        $mail->SMTPSecure = match ($this->config['encryption']) {
            'ssl'   => PHPMailer::ENCRYPTION_SMTPS,
            'tls'   => PHPMailer::ENCRYPTION_STARTTLS,
            default => '',
        };
        // დაშიფვრა არ არის მითითებული (Mailpit) → STARTTLS-ის ავტომატური ცდაც გამორთულია
        $mail->SMTPAutoTLS = $this->config['encryption'] !== '';
        $mail->setFrom($this->config['from_email'], $this->config['from_name']);

        return $this->smtp = $mail;
    }

    public function __destruct()
    {
        $this->smtp?->smtpClose();
    }
}
