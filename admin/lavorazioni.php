<?php

require ('../common.php');
require ('table.php');

$t = &new Table ('lavorazioni', array ('order' => 'nome'), 'id_lavorazione');
new URLAction ($t, 'lavorazioni_dettaglio.php?id_lavorazione=', 'mod');
new DeleteAction ($t, 'canc', array ('galleria_lavorazioni' => 'id_lavorazione'));
new TemplateColumn ($t, 'Nome', '<strong>@nome</strong>');

$f =& new Footer ($t, 3);
new URLAction ($f, 'lavorazioni_dettaglio.php',
	       'Inserisci lavorazione...');

$t->execute ();

site_top ('Categorie');
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
