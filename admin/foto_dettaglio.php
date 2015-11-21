<?php

require ('../common.php');
require ('form.php');

site_top ('Inserisci/modifica dettagli foto', array ('lightbox.php'));

$f =& new Form ('galleria', 'id_galleria');
new TemplateField ($f, '<a rel="lightbox[@id_sottocategoria]" href="../galleria/foto/@id_sottocategoria/@nome"><img align="right" src="../galleria/foto/thumb/@id_sottocategoria/@nome"></a>');
new TextAreaField ($f, 'testo', 'Testo');
new HiddenField ($f, 'id_sottocategoria');
new HTMLField ($f, '<table class="noborder"><tr><td width="50%">');
new ManyManyField ($f, 'galleria_essenze', 'id_essenza', 'essenze', '@nome',
		   'Essenze:', 'Aggiungi essenza:');
new HTMLField ($f, '</td><td width="50%">');
new ManyManyField ($f, 'galleria_lavorazioni', 'id_lavorazione', 'lavorazioni', '@nome',
		   'Lavorazioni:', 'Aggiungi lavorazione:');
new HTMLField ($f, '</td></tr></table>');

$f->execute ('galleria_dettaglio.php?id_sottocategoria=' . $f->get_value ('id_sottocategoria'));
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
