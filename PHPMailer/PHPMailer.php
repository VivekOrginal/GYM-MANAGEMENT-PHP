<?php
namespace PHPMailer\PHPMailer;

class PHPMailer {
    public $Host;
    public $SMTPAuth;
    public $Username;
    public $Password;
    public $SMTPSecure;
    public $Port;
    public $Subject;
    public $Body;
    private $from_email;
    private $from_name;
    private $to_email;

    public function isSMTP() {}
    
    public function setFrom($email, $name = '') {
        $this->from_email = $email;
        $this->from_name = $name;
    }
    
    public function addAddress($email) {
        $this->to_email = $email;
    }
    
    public function send() {
        $headers = "From: {$this->from_name} <{$this->from_email}>\r\n";
        $headers .= "Reply-To: {$this->from_email}\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        
        return mail($this->to_email, $this->Subject, $this->Body, $headers);
    }
}
?>