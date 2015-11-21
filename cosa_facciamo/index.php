<?php require ('../common.php'); ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" > 
<html>
<head>
  <?php site_top ('Cosa facciamo', array ('fade.php')); ?>
</head>
<body onload="startFade()">
<?php include_once('libs/analytics.php'); ?>

<div id="stretto">
<?php site_head (); ?>
<div id="main">
  <?php site_menu (); ?>
  <div id="content">
    <h2>Cosa facciamo</h2>

	<div id="slideshow">
	<img src="progetto.jpg" id="slide-1">
	<img src="scelta.jpg" id="slide-2" style="display: none">
	<img src="bozzetto-1.jpg" id="slide-3" style="display: none">
	<img src="falegnameria-1.jpg" id="slide-4" style="display: none">
	<img src="bozzetto-2.jpg" id="slide-5" style="display: none">
	<img src="falegnameria-2.jpg" id="slide-6" style="display: none">
	</div>

    <p>Ci occupiamo personalmente di tutte le fasi della produzione di mobili su misura: 
      sopralluogo, misurazione degli spazi, progettazione, realizzazione, consegna, montaggio 
      e rifinitura in loco.</p>

    <p>Lavoriamo il legno nelle <a href="materiali/">essenze</a> da voi preferite,
       ma utilizziamo anche vetro, marmo, granito per completare i nostri mobili 
      in base alle esigenze funzionali e alle preferenze estetiche del cliente.</p>
      
    <p>Possiamo inoltre realizzare <a href="lavorazioni/">finiture personalizzate</a>, per
      esempio utilizzando piastrelle, intarsi, vetro decorato, legno dipinto a mano.</p>
      
  </div>
</div>
<?php site_foot (); ?>
</body>
</html>
