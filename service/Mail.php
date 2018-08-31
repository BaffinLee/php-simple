<?php

class  MailService extends Service
{
    private $socket = null;
    private $config = null;

    public function __construct()
    {
        $config = Config::load('smtp');
        $this->config = $config;
    }

    public function sendMail ($to, $subject, $body, $type = 'HTML', $cc = null, $bcc = null)
    {
        $body = mb_ereg_replace("(^|(\r\n))(\\.)", "\\1.\\3", $body);
        $subject = "=?UTF-8?B?".base64_encode($subject)."?=";
        $header = $this->getHeader($to, $subject, $body, $type, $cc);
        $this->send($to, $header, $body);
        if ($cc) $this->send($cc, $header, $body);
        if ($bcc) $this->send($bcc, $header, $body);
    }

    private function getHeader ($to, $subject, $body, $type = 'HTML', $cc = null)
    {
        $header = '';
        $header .= "MIME-Version:1.0\r\n";
        if ($type == "HTML") $header .= 'Content-type: text/html; charset=utf-8'."\r\n";
        $header .= "To: ".$to."\r\n";
        if ($cc) $header .= "Cc: ".$cc."\r\n";
        $header .= "From: ".$this->config['user']."\r\n";
        $header .= "Subject: ".$subject."\r\n";
        $header .= "Date: ".date("r")."\r\n";
        $header .= "X-Mailer:By (PHP/".phpversion().")\r\n";
        return $header;
    }

    private function open ()
    {
        $this->socket = fsockopen(
            $this->config['host'],
            $this->config['port'],
            $errno,
            $errstr,
            $this->config['timeout']
        );
        if ($this->socket === false) {
            Logger::error('smtp connect error: '.$errno.' '.$errstr);
            throw new Exception('smtp connect error');
        }
    }

    private function close ()
    {
        fclose($this->socket);
    }

    private function send ($to, $header, $body)
    {
        $this->open();
        $this->write('HELO', 'localhost');
        $this->write('AUTH LOGIN');
        $this->write(base64_encode($this->config['user']));
        $this->write(base64_encode($this->config['password']));
        $this->write('MAIL FROM:', '<'.$this->config['user'].'>');
        $this->write('RCPT TO:', '<'.$to.'>');
        $this->write('DATA');
        $this->write($header);
        $this->write($body."\r\n.");
        $this->write('QUIT');
        $this->close();
    }

    private function write ($cmd = '', $arg = '')
    {
        $data = $arg ? ($cmd.' '.$arg) : $cmd;
        $res = fputs($this->socket, $data."\r\n");
        if ($res === false) {
            Logger::error('smtp command failed: '.$data);
            throw new Exception('smtp write data failed');
        }
        $this->verify('smtp command failed');
    }

    private function verify ($errmsg)
    {
        $response = fgets($this->socket, 512);
        Logger::debug('smtp response: '.$response);
        if ($response !== false && ($response[0] == 2 || $response[0] == 3)) return true;
        throw new Exception($errmsg);
    }
}