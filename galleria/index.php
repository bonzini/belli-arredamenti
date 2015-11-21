<?php require ('../common.php'); ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" > 
<html>
<head>
  <?php site_top ('Galleria', array ('lightbox.php')); ?>
</head>
<body>
<?php include_once('libs/analytics.php'); ?>

<div id="stretto">
<?php site_head (); ?>
<div id="main">
  <?php site_menu (); ?>
  <div id="content">
    <h1>Galleria</h1>

    <table class="noborder"><tr>
    <?php
      $q = mysql_execute ($sqlconn, 'select count(distinct categorie.id_categoria)
			    from categorie
			    inner join sottocategorie
			    on categorie.id_categoria = sottocategorie.id_categoria
			    inner join galleria
			    on sottocategorie.id_sottocategoria = galleria.id_sottocategoria');
      list ($num_cats) = mysqli_fetch_row($q);
      $split_at = ceil ($num_cats / 2) + 1;

      $q = mysql_execute ($sqlconn, 'select sottocategorie.id_sottocategoria,
			    categorie.nome categoria,
			    sottocategorie.nome sottocategoria,
			    galleria.nome nome, galleria.testo testo
			    from categorie
			    inner join sottocategorie
			    on categorie.id_categoria = sottocategorie.id_categoria
			    inner join galleria
			    on sottocategorie.id_sottocategoria = galleria.id_sottocategoria
			    order by categoria, sottocategoria, id_galleria');

      $prev_sottocat = ''; $prev_cat = '';
      $close_sottocat = ''; $close_cat = '';
      $close_column = '';
      $index = 1;
      $num_cat = 1;
      while ($row = mysqli_fetch_assoc($q))
	{
	  if ($row['categoria'] != $prev_cat)
	    {
	      echo $close_sottocat, $close_cat;
	      if ($num_cat == 1 || $num_cat == $split_at)
		{
		  echo $close_column, '<td width="50%"><ul>';
	          $close_column = '</ul></td>';
		}
	      $num_cat++;

	      echo '<li>', $row['categoria'], '<ul><li>';
	      $close_sottocat = '</li>';
	      $close_cat = '</ul></li>';
	      $prev_cat = $row['categoria'];
	      $prev_sottocat = $row['sottocategoria'];
	      $index = 1;
	    }
	  else if ($row['sottocategoria'] != $prev_sottocat)
	    {
	      echo $close_sottocat, '<li>';
	      $close_sottocat = '</li>';
	      $prev_sottocat = $row['sottocategoria'];
	      $index = 1;
	    }

	  if ($index == 2)
	    {
	      echo '<span class="nascosto">';
	      $close_sottocat = '</span>' . $close_sottocat;
	    }
	  if ($index == 1)
	    $testo = $row['sottocategoria'];
	  else
	    $testo = $index;

	  if ($row['testo'] == '')
	    $didascalia = '&nbsp;';
	  else
	    $didascalia = htmlspecialchars ($row['testo']);
	  echo '<a href="foto/', $row['id_sottocategoria'], '/',
		$row['nome'], '" rel="lightbox[', $row['id_sottocategoria'],
		']" title="', $didascalia, '">',
		$testo, '</a> ';
	  $index++;
	}

      echo $close_sottocat, $close_cat, $close_column;
    ?>
    </tr></table>
  </div>
</div>
<?php site_foot (); ?>
</body>
</html>
