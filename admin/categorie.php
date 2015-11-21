<?php

require ('../common.php');
require ('table.php');

$t = &new Table ('categorie', array ('order' => 'nome'), 'id_categoria');
new URLAction ($t, 'categorie_dettaglio.php?id_categoria=', 'mod');
new DeleteAction ($t, 'canc');
new TemplateColumn ($t, 'Nome', '<strong>@nome</strong>');

$st = &new SubTable ($t, 'sottocategorie', 'id_categoria',
		     array ('order' => 'nome'), 'id_sottocategoria', 3);
$st->set_query ('select sottocategorie.*, count(galleria.nome) num_foto
	        from sottocategorie left join galleria
		on sottocategorie.id_sottocategoria = galleria.id_sottocategoria
		group by sottocategorie.id_sottocategoria');

new URLAction ($st, 'sottocategorie_dettaglio.php?id_sottocategoria=', 'mod');
new DeleteAction ($st, 'canc',
		  array ('galleria_essenze' => 'id_galleria',
			 'galleria_lavorazioni' => 'id_galleria'));
new URLAction ($st, 'galleria_dettaglio.php?id_sottocategoria=', 'foto');
new TemplateColumn ($st, 'Nome', '@nome (@num_foto foto caricate)');

$f =& new Footer ($st, 4);
new URLAction ($f, 'sottocategorie_dettaglio.php?id_categoria=',
	       'Inserisci sottocategoria...');

$f =& new Footer ($t, 3);
new URLAction ($f, 'categorie_dettaglio.php',
	       'Inserisci categoria...');

$t->execute ();

site_top ('Galleria');
?>
<div id="stretto">
<?php admin_head (); ?>
<div id="main">
  <?php admin_menu (); ?>
  <div id="content">
    <?php $t->render (); ?>
  </div>
</div>
<?php site_foot (); ?>
</body>
</html>
