<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'PHPMeiler-master/src/Exeception.php';
require 'PHPMeiler-master/src/PHPMeiler.php';
require 'PHPMeiler-master/src/SMTP.php';
$codigo = rand(100,999);
$mail = new PHPMailer();

//Config
$mail->Mailer = "smtp";
$mail->IsSMTP();
$mail->CharSet = 'UTF-8';
$mail->SMTPDebug = 1;
$mail->SMTPAuth = true;
$mail->SMTPSecure = 'ssl';
$mail->Host = 'smtp.gmail.com';
$mail->Port = 465;

//Detalhes envio do E-mail

$mail->Username = 'buscavet';
$mail->Password = 'senhaforte';
$mail->SetFrom('buscavet@gmail.com', 'BUSCAVET');

$mail->addAddress('destinatario@gmail.com', '');
$mail->Subject = "Assunto do E-mail";

$mail->msgHTML = $codigo;

if(!$mail->Send()) { 
    echo "Mailer Error: " . $mail->ErrorInfo; 
 } else { 
    echo "Mensagem enviada com sucesso"; 
 } 
?>