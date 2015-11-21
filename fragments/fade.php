<script src="<?php echo $_root; ?>/js/prototype.js" type="text/javascript"></script>
<script src="<?php echo $_root; ?>/js/scriptaculous.js?load=effects" type="text/javascript"></script>
<script type="text/javascript">
var i = 1;
var wait = 4000;

function swapFade() {
  div = 'slide-' + i;
  Effect.Fade(div, { duration:1, from:1.0, to:0.0 });
  i++;
  if (!document.getElementById ('slide-' + i)) i = 1;
  div = 'slide-' + i;
  Effect.Appear(div, { duration:1, from:0.0, to:1.0 });
}
		
function startFade() {
  setInterval('swapFade()',wait);
}
</script>
