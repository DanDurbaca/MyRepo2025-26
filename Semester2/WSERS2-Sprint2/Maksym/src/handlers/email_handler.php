<?php
if($_SERVER['REQUEST_METHOD']==='POST'){
  $name = strip_tags($_POST['name']);
  $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
  $message = strip_tags($_POST['message']);
  if(!$name||!$email||!$message) { http_response_code(400); exit; }
  $to = 'ilima987@school.lu';
  $subject = "Contact: $name";
  $body = "From: $name <$email>\n\n$message";
  $headers = "From: no-reply@altmarket.com\r\nReply-To: $email";
  mail($to, $subject, $body, $headers);

  header("Location: ../../public/pages/contact.php");
  exit();
}
?>
