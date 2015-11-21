<?php

# Interfaccia per mandare posta via SMTP.
# Scritto in un luminoso pomeriggio di aprile (6 aprile 2004 per la precisione)
# da Paolo Bonzini.
# Attachment & MIME aggiunti in un buio mattino di aprile (19 aprile 2004)
# Supporto per host == NULL aggiunto in un caldo mezzogiorno di giugno (29 giugno 2004)

# esempio d'uso:
# --------------
#
#   $mail = new SMTP ('web@polimi.it', 'prova', 'mail.polimi.it');
#   $mail->to ('paolo.bonzini@polimi.it');
#   $mail->body ('prova
#   ... prova 2
#   prova 3');
#   $mail->send ();
#
# note sull'implementazione
# -------------------------
#
# il protocollo SMTP e' molto semplice.  bisogna fare in pratica:
#    HELO nome dell'host che invia                   *
#    MAIL FROM: <indirizzo del mittente>             *
#    RCPT TO: <indirizzo del primo destinatario>     *
#    RCPT TO: <indirizzo del secondo destinatario>   *
#    ...
#    DATA                                            *
#    prima riga
#    seconda riga
#    ...
#    ultima riga
#    . (un punto)                                    *
#    QUIT                                            *
#
# dopo le righe con * bisogna aspettare un reply.  questo e' # del tipo
#    NNN-prima riga
#    NNN-seconda riga
#    NNN-terza riga
#    NNN ultima riga
#
# NNN e' un numero; fino a 400 e' buono, sopra e' un errore
#
# Inoltre, gli indirizzi da passare vanno filtrati. la regex che ho usato
# non implementa ovviamente tutto l'RFC ma funziona in casi come questi:
#   pinco@panco.it (commento)             --> pinco@panco.it
#   nome <pinco@panco.it> (commento)      --> pinco@panco.it
#   (comm) <pinco@panco.it> (comm)        --> pinco@panco.it
#   (comm) nome <pinco@panco.it> (comm)   --> pinco@panco.it
#   (comm) pinco@panco.it (comm)          --> pinco@panco.it
#
# Se host e' null, viene usato mail (il che significa che sendmail deve
# essere installato sul computer).  In questo caso bisogna omettere gli
# header To e Subject, e Cc viene convertito in To.
#
# Uso l'output buffer per preparare le stringhe con gli header e il body.

class SMTP {
  var $helo;
  var $from;
  var $to;
  var $cc;
  var $host;
  var $port;
  var $body;
  var $subject;
  var $mime;

  function SMTP ($from = "", $subject = "", $host = NULL, $port = 25)
  {
    if ($_SERVER['HTTP_HOST'] == "")
      {
        $this->helo = gethostbyaddr ("127.0.0.1");
        $domain = '[' . $this->helo . ']';
      }
    else
      $this->helo = $domain = $_SERVER['HTTP_HOST'];

    $this->from = $from == "" ? 'root@' . $domain : $from;
    $this->subject = $subject == "" ? $_SERVER['PHP_SELF'] : $subject;
    $this->host = $host;
    $this->port = $port;
    $this->to = array ();
    $this->cc = array ();
  }

  function host ($host, $port = 25)
  {
    $this->host = $host;
    $this->port = $port;
  }

  function from ($from)
  {
    $this->from = $from;
  }

  function to ($to)
  {
    if (is_array ($to))
      $this->to = $to;
    else
      $this->to[] = $to;
  }

  function cc ($to)
  {
    if (is_array ($to))
      $this->cc = $to;
    else
      $this->cc[] = $to;
  }

  function body ($body)
  {
    $this->body = $body;
  }

  function attach ($file, $name)
  {
    $this->mime[$file] = $name;
  }
    
  function get_reply ($sock)
  {
    fflush ($sock);
    do
      {
        $response = fgets ($sock,1);
        $bytes_left = socket_get_status ($sock);
        if ($bytes_left['unread_bytes'] > 0)
          $response .= fread ($sock, $bytes_left['unread_bytes']);
      }
    while ($response[3] == '-');

    if (substr ($response, 0, 3) < 400)
      return true;

    fclose ($sock);
    return false;
  }

  function get_headers ($recipient)
  {
    ob_start ();
    echo 'From: ' . $this->from . "\r\n";
    if ($recipient)
      {
        echo 'To: ' . implode (', ', $this->to) . "\r\n";
        echo 'Cc: ' . implode (', ', $this->cc) . "\r\n";
        echo 'Subject: ' . $this->subject . "\r\n";
      }

    if ($this->mime)
      {
        echo "MIME-Version: 1.0\r\n";
	echo "Content-Type: multipart/mixed; boundary=\"=_EndPart\"\r\n";
      }
    else
      {
        echo "Content-Type: text/plain\r\n";
        echo "Content-Transfer-Encoding: 8bit\r\n";
      }

    $headers = ob_get_contents ();
    ob_end_clean ();
    return $headers;
  }

  function get_body ($dots)
  {
    ob_start ();
    if ($this->mime)
      {
        echo "\r\n--=_EndPart\r\n";
        echo "Content-Type: text/plain\r\n";
        echo "Content-Transfer-Encoding: 8bit\r\n\r\n";
      }

    foreach (explode ("\n", $this->body) as $line)
      {
        if ($line[0] == '.' && $dots)
	  echo '.';
	echo $line;
	echo "\r\n";
      }

    if ($this->mime)
      {
	foreach ($this->mime as $file => $name)
	  {
            echo "--=_EndPart\r\n";
	    $this->send_attachment ($file, $name);
	  }
        echo "--=_EndPart--\r\n";
      }

    $body = ob_get_contents ();
    ob_end_clean ();
    return $body;
  }

  function send_attachment ($file, $name)
  {
    echo "Content-Type: application/octet-stream; name=\"$name\"\r\n";
    echo "Content-Transfer-Encoding: base64\r\n";
    echo "Content-Disposition: attachment; name=\"$name\"\r\n\r\n";
    $fp = fopen ($file, 'r');
    $content = fread ($fp, filesize ($file));
    echo chunk_split (base64_encode ($content));
    fclose ($fp);
  }

  function filter_email ($recipient)
  {
    return preg_replace ('/(?>
			      (?:\s+|\(.*?\))    # spazi o commenti
			   )*
			   (.*?<(.*?)>		 # mail tra angolari
			  |[^<()\s]+)	   	 # o fuori dalle parentesi se non ce ne sono
        		   (?>
			      (?:\s+|\(.*?\))    # spazi o commenti
			   )*$/x', '$1', $recipient);
  }

  function send ()
  {
    if (count ($this->to) == 0 && count ($this->cc) == 0)
      return;

    if ($this->host === NULL)
      return $this->do_sendmail ();
    else
      return $this->do_smtp ();
  }

  function do_sendmail ()
  {
    $headers = $this->get_headers(false);
    $body = $this->get_body(false);
    mail (implode (',', $this->to) . ',' .  implode (',', $this->cc),
	  $this->subject, $body, $headers);
    return true;
  }

  function do_smtp ()
  {
    $sock = fsockopen ($this->host, $this->port, $errno, $errstr, 15);
    if (!$sock || !$this->get_reply ($sock))
      return false;

    fputs ($sock, 'HELO ' . $this->helo . "\r\n");
    if (!$this->get_reply ($sock))
      return false;

    $sender = $this->filter_email ($this->from);
    fputs ($sock, 'MAIL FROM: <' . $sender . ">\r\n");
    if (!$this->get_reply ($sock))
      return false;

    foreach ($this->to as $recipient)
      {
        $recipient = $this->filter_email ($recipient);
        fputs ($sock, 'RCPT TO: <' . $recipient . ">\r\n");
        if (!$this->get_reply ($sock))
          return false;
      }
    foreach ($this->cc as $recipient)
      {
        $recipient = $this->filter_email ($recipient);
        fputs ($sock, 'RCPT TO: <' . $recipient . ">\r\n");
        if (!$this->get_reply ($sock))
          return false;
      }

    fputs ($sock, "DATA\r\n");
    if (!$this->get_reply ($sock))
      return false;

    $headers = $this->get_headers(true);
    $body = $this->get_body(true);
    fputs ($sock, "$headers\r\n$body\r\n.\r\n");
    if (!$this->get_reply ($sock))
      return false;

    fputs ($sock, "QUIT\r\n");
    if (!$this->get_reply ($sock))
      return false;

    fclose ($sock);
    return true;
  }
}

?>
