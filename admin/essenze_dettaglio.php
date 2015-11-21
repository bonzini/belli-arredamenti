<?php

require ('../common.php');
require ('form.php');
require ('thumb.php');

site_top ('Inserisci/modifica essenza');

$f =& new Form ('essenze', 'id_essenza');
new TextField ($f, 'nome', 'Nome');
new ThumbnailField ($f, 'cosa_facciamo/materiali/foto', 'Campioni');

$f->execute ('essenze.php');
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
