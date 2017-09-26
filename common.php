<?php

require ('libs/db.php');

ob_start ();

function site_top ($title, $files = array ())
{
  global $_path, $_root, $_title;

  $_title = $title;
  $_path = $_SERVER['REQUEST_URI'];
  if ($_path == '')
    $_root = '';
  else
    {
      $_root = preg_replace (',(^/)?[^/]+/+,', '../', $_path);
      $_root = preg_replace (',[^/]+$,', '', $_root);
      $_root = substr ($_root, 0, strlen ($_root) - 1);
    }

  echo '<title>Belli Arredamenti', ($title ? ' &mdash; ' : ''), $title, '</title>
<!--[if lt IE 8]><script src="http://ie7-js.googlecode.com/svn/version/2.0(beta3)/IE8.js" type="text/javascript"></script><![endif]-->';

  foreach ($files as $file)
    require ('fragments/' . $file);

  echo '<style type="text/css">@import url(', $_root, '/styles/style.css);</style>';
  # echo '<link rel="stylesheet" type="text/css" media="all" title="pialla" href="', $_root, '/styles/style.css"/>';
  # echo '<link rel="alternate stylesheet" type="text/css" media="all" title="legna" href="', $_root, '/styles/style_2.css"/>';
  # echo '<link rel="stylesheet" type="text/css" media="print" href="', $_root, '/styles/print.css"/>';
  echo '</head>';
}

function site_head ($x = '')
{
  global $_root;
  if ($x != '')
    $x = ' - ' . $x;
?>
<div id="head">
  <h1><?php site_link ('<strong>belli</strong> arredamenti' . $x, '/'); ?></h1>
  <img src="<?php echo $_root; ?>/images/pialla_testa.png">
  <div><address><a href="<?php echo $_root; ?>/dove_siamo/#map">
    via fienili 15<br/>
    24010 Sedrina (BG)<br/>
    tel. 0345 60138<br/>
    fax 0345 62924<br/></a></address>
    <a href="mailto:info@belli-arredamenti.it">info@belli-arredamenti.it</a>
  </div>
</div>
<?php
}

function site_menu ()
{
?>
<div id="menu">
  <ul><?php
    echo '<li>';
    site_link ('chi siamo', '/chi_siamo/');
    echo '</li><li>';
    site_link ('cosa facciamo', '/cosa_facciamo/');
    echo '</li><li>';
    site_link ('galleria', '/galleria/');
    echo '</li><li>';
    site_link ('dove siamo', '/dove_siamo/');
    echo '</li>';
  ?></ul>
</div>
<?php
}

function admin_head ()
{
  site_head ('amministrazione sito');
}

function admin_menu ()
{
?>
<div id="menu">
  <ul><?php
    echo '<li>';
    site_link ('galleria', '/admin/categorie.php');
    echo '</li><li>';
    site_link ('essenze', '/admin/essenze.php');
    echo '</li><li>';
    site_link ('lavorazioni', '/admin/lavorazioni.php');
    echo '</li><li>';
    site_link ('mostra sito', '/');
    echo '</li>';
  ?></ul>
</div>
<?php
}

function site_link ($title, $path)
{
  global $_path, $_root, $_title;

  if ($path == $_path || strtolower ($title) == strtolower ($_title))
    echo '<span>', $title, '</span>';
  else
    echo '<a href="', $_root, ($path == '/' ? '' : $path), '">', $title, '</a>';
}

function site_foot ()
{
?>
  <div id="foot">
    <p>Copyright (C) 2008<?php
      if (date ('Y') != 2008) echo '-', date ('Y'); ?> Belli Arredamenti</p>
  </div>
<?php
}

function site_deny ()
{
  header('WWW-Authenticate: Basic realm="Belli Arredamenti"');
  header('HTTP/1.0 401 Unauthorized');
  ?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title>401 Unauthorized</title>
</head>
<body>
<h1>401 Unauthorized</h1>
<p>Sorry, this page is not yet publicly available.</p>
<hr/>
<address><?php echo $_SERVER['SERVER_SIGNATURE']; ?></address>
</body>
</html>
  <?php
  exit;
}


define ('PROTEGGI', 0);

if (!isset ($_SERVER['PHP_AUTH_USER']) || !isset ($_SERVER['PHP_AUTH_PW']))
  {
    $db_user = 'db';
    $db_password = $_ENV['MYSQL_PASSWORD']';
  }
else
  {
    $db_user = 'dbrw';
    $db_password = $_SERVER['PHP_AUTH_PW'];
  }

if ((PROTEGGI || strstr ($_SERVER['PHP_SELF'], "admin") !== FALSE)
    && (!isset ($_SERVER['PHP_AUTH_USER']) || !isset ($_SERVER['PHP_AUTH_PW'])))
  site_deny ();

define ('GOOGLE_MAPS_KEY', 'ABQIAAAAJg_ZgaoUTrvXe74lk2pZCBSLA_W6cd0fa8ielUp9C1k46k7_xxSvd-pnpHj3vSI6Yrux_by5hhtrjA');
($sqlconn = mysqli_connect($_ENV['MYSQL_PORT'], 
	                   $db_user, $db_password)) or die(mysqli_connect_error());
mysqli_query($sqlconn, 'USE belliarredamenti') or die(mysqli_error($sqlconn));

?>
