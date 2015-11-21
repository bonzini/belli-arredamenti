<?php

require ('../common.php');
require ('form.php');
require ('thumb.php');

site_top ('Inserisci/modifica foto');

$q = 'select nome from sottocategorie where id_sottocategoria = ?';
$nome = mysql_execute_fetch_val ($sqlconn, $q, $_GET['id_sottocategoria']);

$f =& new Form ('sottocategorie', 'id_sottocategoria');

new HiddenField ($f, 'id_sottocategoria');
new LinkedThumbnailField ($f, 'galleria/foto', 'Immagini ' . $nome, 'galleria',
		         'id_galleria', 'nome',
		         array ('id_sottocategoria' => $f->get_value ('id_sottocategoria')),
		         'foto_dettaglio.php?id_galleria=');

$f->execute ('galleria_dettaglio.php?id_sottocategoria=' . urlencode ($f->get_value ('id_sottocategoria')));
?>
<div id="stretto">
<?php admin_head (); ?> 
<div id="main">
  <?php admin_menu (); ?>
  <div id="content">
    <?php $f->render (); ?>
  </div>
</div>
<?php site_foot (); ?>
</body>
</html>
