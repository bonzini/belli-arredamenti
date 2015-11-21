<?php require ('../common.php'); ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" > 
<html>
<head>
  <?php site_top ('Contatti'); ?>
</head>
<body>
<?php include_once('libs/analytics.php'); ?>

<div id="stretto">
<?php site_head (); ?>
<div id="main">
  <?php site_menu (); ?>
  <div id="content">
    <h2>Contatti</h2>

    <ul id="menu2">
      <li id="attivo">Come contattarci</li>
      <li><a href="indirizzo.php#map">Dove trovarci</a></li>
    </ul>

    <form action="invia-mail.php" method="post">
      Prova
    </form>
  </div>
</div>
<?php site_foot (); ?>
</body>
</html>
