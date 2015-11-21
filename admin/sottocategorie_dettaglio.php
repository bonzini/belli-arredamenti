<?php

require ('../common.php');
require ('form.php');

site_top ('Inserisci/modifica sottocategoria');

$f =& new Form ('sottocategorie', 'id_sottocategoria');
new TextField ($f, 'nome', 'Nome');
new HiddenField ($f, 'id_categoria');

$f->execute ('categorie.php');
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
