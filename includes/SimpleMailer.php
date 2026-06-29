<?php
/**
 * SimpleMailer - kirim email via Gmail SMTP (App Password) tanpa library eksternal.
 */
class SimpleMailer
{
    private $host = 'smtp.gmail.com';
    private $port = 587;
    private $username;
    private $password;
    private $fromName;
    private $socket;
    public $lastError = '';

    public function __construct($username, $password, $fromName = '')
    {
        $this->username = $username;
        $this->password = $password;
        $this->fromName = $fromName ?: SITE_NAME;
    }

    private function cmd($command)
    {
        if ($command !== null) {
            fwrite($this->socket, $command . "\r\n");
        }
        $response = '';
        while ($line = fgets($this->socket, 515)) {
            $response .= $line;
            if (isset($line[3]) && $line[3] === ' ') break;
        }
        return $response;
    }

    public function send($to, $subject, $bodyHtml)
    {
        $this->socket = @fsockopen($this->host, $this->port, $errno, $errstr, 15);
        if (!$this->socket) {
            $this->lastError = "Gagal konek SMTP: $errstr";
            return false;
        }

        $this->cmd(null);
        $this->cmd("EHLO " . $this->host);
        $this->cmd("STARTTLS");

        if (!stream_socket_enable_crypto($this->socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            $this->lastError = "Gagal mengaktifkan TLS";
            return false;
        }

        $this->cmd("EHLO " . $this->host);
        $this->cmd("AUTH LOGIN");
        $this->cmd(base64_encode($this->username));
        $authResp = $this->cmd(base64_encode($this->password));

        if (substr($authResp, 0, 3) !== '235') {
            $this->lastError = "Autentikasi gagal. Pastikan App Password benar. Respon: $authResp";
            fclose($this->socket);
            return false;
        }

        $this->cmd("MAIL FROM: <{$this->username}>");
        $this->cmd("RCPT TO: <{$to}>");
        $this->cmd("DATA");

        $headers  = "From: {$this->fromName} <{$this->username}>\r\n";
        $headers .= "To: <{$to}>\r\n";
        $headers .= "Subject: {$subject}\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

        $message = $headers . "\r\n" . $bodyHtml . "\r\n.";
        $this->cmd($message);
        $this->cmd("QUIT");
        fclose($this->socket);

        return true;
    }
}
