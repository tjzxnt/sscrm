<?php

//error_reporting(E_ALL);
//error_reporting(E_STRICT);

date_default_timezone_set('Asia/ShangHai');
//date_default_timezone_set(date_default_timezone_get());

include_once('class.phpmailer.php');
//include("class.smtp.php"); // optional, gets called from within class.phpmailer.php if not already loaded

$mail             = new PHPMailer();

$body             = $mail->getFile('examples/contents.html');
$body             = eregi_replace("[\]",'',$body);


$mail->SMTPAuth   = true; 

$mail->IsSMTP(); // telling the class to use SMTP
$mail->Host       = "smtp.sina.com"; // SMTP server

$mail->From       = "zhangyan1984715@sina.com";


$mail->Username   = "zhangyan1984715@sina.com";

$mail->Password   = "1984715";
$mail->FromName   = "zy";

$mail->Subject    = "PHP";

//$mail->AltBody    = "hahaha!"; // optional, comment out and test

$mail->MsgHTML($body);

//$mail->AddReplyTo("zhangyan1984715@sina.com","yutian");

$mail->AddAddress("zhangyan1984715@sina.com", "yutian");

//$mail->AddAttachment("images/phpmailer.gif");             // attachment

if(!$mail->Send()) {
  echo "Mailer Error: " . $mail->ErrorInfo;
} else {
  echo "Message sent!";
}

?> 