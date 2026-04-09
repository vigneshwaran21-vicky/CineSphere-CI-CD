<?php
class SmtpMailer {
    private $host;
    private $port;
    private $username;
    private $password;
    public $error = '';

    public function __construct($host = 'smtp.gmail.com', $port = 587, $username = '', $password = '') {
        $this->host = $host;
        $this->port = $port;
        $this->username = $username;
        $this->password = $password;
    }

    public function send($to, $subject, $message) {
        $crlf = "\r\n";
        
        $socket = fsockopen($this->host, $this->port, $errno, $errstr, 5);
        if (!$socket) { 
            $this->error = "Could not connect to SMTP host: $errstr ($errno)";
            return false; 
        }
        stream_set_timeout($socket, 5); // 5 seconds stream timeout

        if (!$this->server_parse($socket, "220")) return false;
        fputs($socket, "EHLO " . $this->host . $crlf);
        if (!$this->server_parse($socket, "250")) return false;

        fputs($socket, "STARTTLS" . $crlf);
        if (!$this->server_parse($socket, "220")) return false;

        stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
        fputs($socket, "EHLO " . $this->host . $crlf);
        if (!$this->server_parse($socket, "250")) return false;

        fputs($socket, "AUTH LOGIN" . $crlf);
        if (!$this->server_parse($socket, "334")) return false;
        
        fputs($socket, base64_encode($this->username) . $crlf);
        if (!$this->server_parse($socket, "334")) return false;
        
        fputs($socket, base64_encode($this->password) . $crlf);
        if (!$this->server_parse($socket, "235")) return false;

        fputs($socket, "MAIL FROM: <" . $this->username . ">" . $crlf);
        if (!$this->server_parse($socket, "250")) return false;
        
        fputs($socket, "RCPT TO: <" . $to . ">" . $crlf);
        if (!$this->server_parse($socket, "250")) return false;

        fputs($socket, "DATA" . $crlf);
        if (!$this->server_parse($socket, "354")) return false;

        $headers = "From: CineSphere <" . $this->username . ">" . $crlf;
        $headers .= "To: " . $to . $crlf;
        $headers .= "Subject: " . $subject . $crlf;
        $headers .= "Content-Type: text/html; charset=UTF-8" . $crlf;
        
        fputs($socket, $headers . $crlf . $message . $crlf . "." . $crlf);
        if (!$this->server_parse($socket, "250")) return false;

        fputs($socket, "QUIT" . $crlf);
        fclose($socket);

        return true;
    }

    private function server_parse($socket, $response) {
        $server_response = "";
        while (substr($server_response, 3, 1) != ' ') {
            if (!($server_response = fgets($socket, 256))) {
                return false;
            }
        }
        if (!(substr($server_response, 0, 3) == $response)) {
            $this->error = "SMTP Server Error: expected $response, got $server_response";
            return false;
        }
        return true;
    }
}
?>
