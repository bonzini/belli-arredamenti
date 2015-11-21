<?php

require ('../common.php');
require ('form.php');

site_top ('Inserisci/modifica categoria');

$f =& new Form ('categorie', 'id_categoria');
new TextField ($f, 'nome', 'Nome');

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
