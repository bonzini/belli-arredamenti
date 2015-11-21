<?php require ('common.php'); ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" > 
<html>
<head>
  <?php site_top ('', array ('prefetch.php')); ?>
</head>
<body>
<?php include_once('libs/analytics.php'); ?>
<div id="stretto">
<?php site_head (); ?>
<div id="main">
  <?php site_menu (); ?>
  <div id="content">
    <h2>Benvenuti!</h2>

    <p>Nel nostro sito troverete informazioni sulla <a href="chi_siamo/">nostra
      azienda</a>, sul nostro modo di lavorare, sulla nostra produzione.</p>

    <p>Nelle sezioni <em><a href="galleria/">galleria</a></em> e
      <em><a href="cosa_facciamo/">cosa facciamo</a></em> potrete
      vedere una selezione dei nostri lavori.</p>

    <p>Per <!-- richiedere un preventivo o per --> venirci a trovare
      consultate invece la sezione <em><a href="dove_siamo/">dove siamo</a></em>.
      Saremo felici di rispondere alle vostre domande.</p>

    <p>Buona navigazione e a presto!</p>
  </div>
</div>
<?php site_foot (); ?>
</body>
</html>
