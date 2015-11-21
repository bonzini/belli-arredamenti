<?php require ('../../common.php'); ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" > 
<html>
<head>
  <?php site_top ('Materiali', array ('lightbox.php')); ?>
</head>
<body>
<?php include_once('libs/analytics.php'); ?>

<div id="stretto">
<?php site_head (); ?>
<div id="main">
  <?php site_menu (); ?>
  <div id="content">
    <h2>Materiali</h2>

    <ul id="menu2">
      <li id="attivo">Materiali</li>
      <li><a href="../lavorazioni">Lavorazioni</a></li>
    </ul>

    <table class="pictab"><tr>
    <?php
      $cell = 0;
      $q = mysql_execute ($sqlconn, 'select essenze.nome essenza, essenze.id_essenza id_essenza,
			    galleria.id_galleria id_galleria,
			    galleria.id_sottocategoria id_sottocategoria,
			    galleria.nome nome, galleria.testo testo
			    from essenze
			    left join galleria_essenze
			    on essenze.id_essenza = galleria_essenze.id_essenza
			    left join galleria
			    on galleria_essenze.id_galleria = galleria.id_galleria
			    order by essenza, galleria.id_galleria');
      $prev = '';
      $close = '';
      $index = 1;
      while ($row = mysqli_fetch_assoc($q))
	{
	  $f = FALSE;
	  if ($row['essenza'] != $prev)
	    {
	      echo $close;
	      if ($cell++ % 3 == 0 && $cell > 0)
		echo '</tr><tr>';
	      echo '<td><span>';
	      $close = '</span></td>';

	      $path = FILEBASE . 'cosa_facciamo/materiali/foto/' . $row['id_essenza'];
	      $d = @opendir ($path);
	      if ($d)
		{
		  while ($f = readdir ($d))
		    if ($f != '.' && $f != '..')
		      break;
		  closedir ($d);
		}

	      $testo = $row['essenza'];
	      if ($f)
		$testo = '<img src="foto/thumb/' . $row['id_essenza'] . '/' . $f . '"> ' . $testo;

	      $prev = $row['essenza'];
	      $index = 1;
	    }
	  else
	    $testo = $index;

	  if ($index == 2)
	    {
	      echo '<span class="nascosto">';
	      $close = '</span>' . $close;
	    }

	  if ($row['id_galleria'])
	    {
	      if ($row['testo'] == '')
		$didascalia = '&nbsp;';
	      else
		$didascalia = htmlspecialchars ($row['testo']);
	      echo '<a href="', $_root, '/galleria/foto/',
		    $row['id_sottocategoria'], '/', $row['nome'],
		    '" rel="lightbox[', $row['id_essenza'],
		    ']" title="', $didascalia, '">';

	      echo $testo, '</a> ';
	    }
	  else if ($index == 1)
	    echo $testo;

	  $index++;
	}

      echo $close;
    ?>
    </tr></table>
  </div>
</div>
<?php site_foot (); ?>
</body>
</html>
