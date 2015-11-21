<?php

require ('../common.php');
require ('table.php');

$t = &new Table ('essenze', array ('order' => 'nome'), 'id_essenza');
new URLAction ($t, 'essenze_dettaglio.php?id_essenza=', 'mod');
new DeleteAction ($t, 'canc', array ('galleria_essenze' => 'id_essenza'));
new TemplateColumn ($t, 'Nome', '<strong>@nome</strong>');

$f =& new Footer ($t, 3);
new URLAction ($f, 'essenze_dettaglio.php',
	       'Inserisci essenza...');

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
