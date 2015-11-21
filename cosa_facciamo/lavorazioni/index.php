<?php require ('../../common.php'); ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" > 
<html>
<head>
  <?php site_top ('Lavorazioni', array ('lightbox.php')); ?>
</head>
<body>
<?php include_once('libs/analytics.php'); ?>

<div id="stretto">
<?php site_head (); ?>
<div id="main">
  <?php site_menu (); ?>
  <div id="content">
    <h2>Lavorazioni</h2>

    <ul id="menu2">
      <li><a href="../materiali">Materiali</a></li>
      <li id="attivo">Lavorazioni</li>
    </ul>

    <table class="pictab"><tr>
    <?php
      $cell = 0;
      $q = mysql_execute ($sqlconn, 'select lavorazioni.nome lavorazione, lavorazioni.id_lavorazione id_lavorazione,
			    galleria.id_galleria id_galleria,
			    galleria.id_sottocategoria id_sottocategoria,
			    galleria.nome nome, galleria.testo testo
			    from lavorazioni
			    left join galleria_lavorazioni
			    on lavorazioni.id_lavorazione = galleria_lavorazioni.id_lavorazione
			    left join galleria
			    on galleria_lavorazioni.id_galleria = galleria.id_galleria
			    order by lavorazione, galleria.id_galleria');
      $prev = '';
      $close = '';
      $index = 1;
      while ($row = mysqli_fetch_assoc($q))
	{
	  $f = FALSE;
	  if ($row['lavorazione'] != $prev)
	    {
	      echo $close;
	      if ($cell++ % 3 == 0 && $cell > 0)
		echo '</tr><tr>';
	      echo '<td><span>';
	      $close = '</span></td>';

	      $path = FILEBASE . 'cosa_facciamo/lavorazioni/foto/' . $row['id_lavorazione'];
	      $d = @opendir ($path);
	      if ($d)
		{
		  while ($f = readdir ($d))
		    if ($f != '.' && $f != '..')
		      break;
		  closedir ($d);
		}

	      $testo = $row['lavorazione'];
	      if ($f)
		$testo = '<img src="foto/thumb/' . $row['id_lavorazione'] . '/' . $f . '"> ' . $testo;

	      $prev = $row['lavorazione'];
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
		    '" rel="lightbox[', $row['id_lavorazione'],
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
