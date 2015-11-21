<?php require ('../common.php'); ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" > 
<html>
<head>
  <?php site_top ('Chi siamo', array ('fade.php')); ?>
</head>
<body onload="startFade()">
<?php include_once('libs/analytics.php'); ?>

<div id="stretto">
<?php site_head (); ?>
<div id="main">
  <?php site_menu (); ?>
  <div id="content">
    <h2>Chi siamo</h2>

	<div id="slideshow">
	<img src="luigi.jpg" id="slide-1">
	<img src="miriam.jpg" id="slide-2" style="display: none">
	<img src="caterina.jpg" id="slide-3" style="display: none">
	<img src="quasi-pronto.jpg" id="slide-4" style="display: none">
	</div>

    <p>La nostra &egrave; un'azienda familiare, fondata nel 1959. 
      
      Nelle foto qui a fianco ci vedete al lavoro.  <a
      href="mailto:info@belli-arredamenti.it">Scriveteci</a> o
      <a href="../dove_siamo/">venite a trovarci</a>. Saremo felici
      di rispondere alle vostre richieste.
     
    <p>Luca, Luigi, Angela, Miriam, Caterina</p>

    <p>Buona navigazione e a presto!</p>
  </div>
</div>
<?php site_foot (); ?>
</body>
</html>
