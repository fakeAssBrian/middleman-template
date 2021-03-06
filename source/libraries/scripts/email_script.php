<?
  /*
    -DOCUMENTATION-

    --FORM--
    Submit form to /libraries/scripts/email_script.php using method POST
    If you want to use it with ajax, add this input `<input type="hidden" name="ajax" />`. This will return either 200 (success) or 400 (error).
    If you want to include a honeypot, add this input `<input type="hidden" name="honeypot" />`. This will prevent the form from sending if the field has a value that's not blank.

    --SCRIPT--
    Tweak the settings below. The script must use SMTP. Mandrill host, port, and security type is already prefilled.
    For more mailer options, re-enable the commented out `$mail->` options located towards the end of the script. You can set reply-to, non-html body, and handle attachments.
  */

  // SETTINGS
  $from_email     = "from@email.com";
  $from_name      = "From Name";
  $to_email       = "to@email.com";
  $to_name        = "To Name";

  $smtp_host      = 'smtp.mandrillapp.com';
  $smtp_secure    = 'tls';
  $smtp_port      = 587;
  $smtp_auth      = true;
  $smtp_username  = 'user@whatever.com';
  $smtp_pass      = "******API-KEY*******";

  $debugging      = 0;    // 0=off, 1=client, 2=client/server
  $success_text   = "Thank you! We'll get back to you soon.";
  $error_text     = "Oops! Something went wrong. Please give us a call instead.";


  // LOAD
  require __DIR__ . '/../vendor/autoload.php';
  date_default_timezone_set('Etc/UTC');


  // FUNCTIONS
  function prepare_data($post_variables) {
    $data = array();
    foreach($post_variables as $k => $v) {
      if($k != "submit" && $v != "" && $v != null && $k != "ajax") {
        $data[$k] = filter_var(trim($v), FILTER_SANITIZE_STRING);
      }
    }
    return $data;
  }

  function prettify_label($label) {
    $label = str_replace("_", " ", $label);
    $label = str_replace("-", " ", $label);
    $label = ucwords($label);
    $label = $label . ":";
    return $label;
  }

  function build_body($email_data) {
    $message = "";
    foreach($email_data as $k => $v){
      $message .= prettify_label($k) . " " . nl2br($v) . "\n<br/>";
    }
    return $message;
  }


  // INIT
  $mail = new PHPMailer;
  $mail->isSMTP();


  // DEBUGGING
  $mail->SMTPDebug = $debugging;
  $mail->Debugoutput = 'html';


  // SMTP SETTINGS
  $mail->Host       = $smtp_host;
  $mail->Port       = $smtp_port;
  $mail->SMTPSecure = $smtp_secure;
  $mail->SMTPAuth   = $smtp_auth;
  $mail->Username   = $smtp_username;
  $mail->Password   = $smtp_pass;


  // EMAIL
  $mail->setFrom($from_email, $from_name);                           // from email
  $mail->addAddress($to_email, $to_name);                            // to email
  $mail->isHTML(true);
  $mail->Subject  = "New submission from {$_SERVER['HTTP_HOST']}";   // subject
  $mail->Body    = "Hi! You got a new email from your website. Please review the information below:<br/><br/>";
  $mail->Body    .= build_body( prepare_data($_POST) );              // html body
  // $mail->addReplyTo('replyto@example.com', 'First Last');         // (optional) reply-to email
  // $mail->AltBody = 'alternative non-html body';                   // (optional) non-html alternative body
  // $mail->addAttachment('images/phpmailer_mini.png');              // (optional) attachments


  // SEND
  if ($debugging) :
    echo (!$mail->send()) ? "Mailer Error: {$mail->ErrorInfo}" : "Message Sent!";
  else :

    $error_url = preg_replace('/\?.*/', '', $_SERVER["HTTP_REFERER"]) . "?error=" . urlencode($error_text);
    $success_url = preg_replace('/\?.*/', '', $_SERVER["HTTP_REFERER"]) . "?success=" . urlencode($success_text);

    if (empty($_POST['honeypot']) && count($_POST) > 0) :
      if (!$mail->send()) :
        isset($_POST['ajax']) ? header("HTTP/1.1 400 Bad Request", true, 400) :  header("Location: $error_url") ;
      else :
        isset($_POST['ajax']) ? header("HTTP/1.1 200 OK", true, 200) : header("Location: $success_url") ;
      endif;
    else :
      isset($_POST['ajax']) ? header("HTTP/1.1 400 Bad Request", true, 400) :  header("Location: $error_url") ;
    endif;

  endif;
?>