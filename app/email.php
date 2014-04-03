<?php
class Email
{
  public $to = null;
  public $from = null;
  public $subject = "";
  public $body = "";
  
  //send an email
  public function Send()
  {
    $result = new DataActionResult();

    $result->success = true;
    if (!$this->IsValidEmail($this->to))
    {
      $result->success = false;
      $result->message .= "The To email address is not valid. Please enter a valid email address.<br/>";
    }
    if (!$this->IsValidEmail($this->from))
    {
      $result->success = false;
      $result->message .= "The From email address is not valid. Please enter a valid email address.<br/>";
    }

    $badStrResult = $this->ContainsBadString($this->to);
    $result->success = $badStrResult->success;
    $result->message .= $badStrResult->message;
    $badStrResult = $this->ContainsBadString($this->from);
    $result->success = $badStrResult->success;
    $result->message .= $badStrResult->message;
    $badStrResult = $this->ContainsBadString($this->subject);
    $result->success = $badStrResult->success;
    $result->message .= $badStrResult->message;
    $badStrResult = $this->ContainsBadString($this->body);
    $result->success = $badStrResult->success;
    $result->message .= $badStrResult->message;

    $newLineResult = $this->ContainsNewLines($this->to);
    $result->success = $newLineResult->success;
    $result->message .= $newLineResult->message;
    $newLineResult = $this->ContainsNewLines($this->from);
    $result->success = $newLineResult->success;
    $result->message .= $newLineResult->message;
    $newLineResult = $this->ContainsNewLines($this->subject);
    $result->success = $newLineResult->success;
    $result->message .= $newLineResult->message;
  

    if ($result->success == true)
    {
      //the next two headers make it a HTML-formatted email
      $headers = "MIME-Version: 1.0"."\r\n";
      $headers .= "Content-type:text/html;charset=iso-8859-1"."\r\n";

      $headers .= "From: ".$this->from;
      $result->success = mail($this->to, $this->subject, $this->body, $headers);
      if ($result->success == true)
      {
        $result->message = "Email sent to ".$this->to;
      }
      else
      {
        $result->message = "ERROR: The message '".$this->subject."' from ".$this->from." to ".$this->to." could not be sent. There may be a problem with the email server. Please try again.";
        Logger::LogWarning($result->message);
      }
    }
    else
    {
        Logger::LogWarning($result->message);
    }

    return $result;
  }
  
  //basic test for a valid email address
  private function IsValidEmail($email)
  {
    return preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\.\\+=_-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $email);
  }

  //guard against injection attacks
  private function ContainsBadString($str)
  {
    $result = new DataActionResult();
    $result->success = true;

    $bad_strings = array(
      "content-type:"
      ,"mime-version:"
      ,"multipart/mixed"
      ,"content-transfer-encoding:"
      ,"bcc:"
      ,"cc:"
      ,"to:"
     );
  
    foreach($bad_strings as $bad_string)
    {
        if(strpos($bad_string, strtolower($str)) !== false)
      {
        $result->success = false;
        $result->message .= "Undesirable string found. Suspected injection attempt - mail will not be sent.";
      }
    }

    return $result;
  }
  
  //guard against injection attacks
  private function ContainsNewLines($str)
  {
    $result = new DataActionResult();

    if(preg_match("/(%0A|%0D|\\n+|\\r+)/i", $str) != 0)
    {
      $result->success = false;
      $result->message = "newline found in test string. Suspected injection attempt - mail will not be sent.";
    }
    else $result->success = true;

    return $result;
  }
}
?>
