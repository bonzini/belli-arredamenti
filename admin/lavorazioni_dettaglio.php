<?php

require ('../common.php');
require ('form.php');
require ('thumb.php');

site_top ('Inserisci/modifica lavorazione');

$f =& new Form ('lavorazioni', 'id_lavorazione');
new TextField ($f, 'nome', 'Nome');
new ThumbnailField ($f, 'cosa_facciamo/lavorazioni/foto', 'Esempi');

$f->execute ('lavorazioni.php');
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
