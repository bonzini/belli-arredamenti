<?php require ('../common.php'); ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" > 
<html>
<head>
  <?php site_top ('Dove siamo'); ?>
    <script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=<?php echo GOOGLE_MAPS_KEY; ?>"
            type="text/javascript"></script>
    <script type="text/javascript">
    //<![CDATA[


    function load() {
      if (GBrowserIsCompatible()) {
        var map = new GMap2(document.getElementById("map"));
        var coords = new GLatLng(45.777596,9.624324);
        var center = new GLatLng(45.779945,9.621341);
        var marker = new GMarker(coords);
        map.setCenter(center, 16);
        map.addControl(new GLargeMapControl());
        map.addOverlay(marker);
        marker.openInfoWindowHtml("<span style=\"color: black\">Belli Arredamenti</span>");
      }
    }

    //]]>
    </script>
</head>
<body onload="load()" onunload="GUnload()">
<?php include_once('libs/analytics.php'); ?>

<div id="stretto">
<?php site_head (); ?>
<div id="main">
  <?php site_menu (); ?>
  <div id="content">
    <h2>Contatti</h2>

<!--
    <ul id="menu2">
      <li><a href="mail.php">Come contattarci</a></li>
      <li id="attivo">Dove trovarci</li>
    </ul>
-->

    <div id="map" style="width: 642px; height: 340px"></div>
  </div>
</div>
<?php site_foot (); ?>
</body>
</html>
