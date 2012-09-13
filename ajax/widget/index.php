<?php
define ('WP_USE_THEMES', false);
include (dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))).'/wp-blog-header.php');

$_GET['xa'] = (int) $_GET['xa'];
$iam = new WP_AFL_Affiliate ($_GET['xa']);

if (!$iam->get()) die ('ERROR: Invalid Affiliate Code');

#$products = $iam->get('products');
$products = new WP_CRM_List ('products', array ('active'));
if ($products->is('empty')) die ('ERROR: Invalid Product List');

$out = array ();
foreach (($products->get()) as $product) {
	$code = $product->get('current code');
	$image = current ($product->get('image'));
	$out[] = array (
		'p' => round ($product->get('price', $code) * (100 + $product->get('vat', $code)) * 0.01, 2),
		'c' => round ($product->get('full price', $code) * (100 + $product->get('vat', $code)) * 0.01, 2),
		'i' => $image,
		'n' => $product->get('name', $code),
		'u' => add_query_arg('xa', $iam->get(), $product->get('link')),
		);
	}
?>
<html>
<head>
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.1/jquery.min.js"></script>
</head>
<body>
<script type="text/javascript">
var EAWdata=<?php echo json_encode($out); ?>;document.write('<div id="ext-affiliate-widget"></div>');
jQuery('#ext-affiliate-widget').css({'width':'300px','height':'200px','overflow':'hidden','border':'1px solid #ccc','border-radius':'3px', 'background':'#fff'}).each(function(n,w){
jQuery(w).empty();
jQuery.each(EAWdata,function(m,p){
	if (p.i) jQuery(w).append('<div style="text-align: center; overflow: hidden; position: relative;"><a style="text-decoration: none; color: #000; font-family: Calibri, Arial, Verdana, sans-serif; font-size: 11pt;" href="'+p.u+'" title="'+p.n+'"><img style="height: '+jQuery('#ext-affiliate-widget').height()+'px;" src="'+p.i+'" alt="'+p.n+'" title="'+p.n+'" /><h3 style="margin: 0; padding: 5px 10px; position: absolute; top: 5px; background: #fff; border-radius: 3px; opacity: .9;">'+p.n+'</h3><p><span style="display: block; color: #000; text-decoration: line-through; font-weight: bold; position: absolute; right: 5px; bottom: 58px; background: #fff; border-radius: 3px; opacity: .9; padding: 5px 10px;">'+p.c+' lei</span><span style="display: block; color: #c00; font-size: 23pt; font-weight: bold; position: absolute; right: 5px; bottom: 5px; background: #fff; border-radius: 3px; opacity: .9; padding: 5px 10px;">'+p.p+' lei</span></p></a></div>');
	});
jQuery('#ext-affiliate-widget div').each(function(n,d){
	jQuery(d).width(jQuery(d).parent().width());
	jQuery(d).height(jQuery(d).parent().height());
	});
window.setInterval (function(){
	var m = parseInt(jQuery('#ext-affiliate-widget div:first').css('margin-top'));
	var h = jQuery('#ext-affiliate-widget').height();
	if (h - m < jQuery('#ext-affiliate-widget div').size()*h) m -= h; else m = 0;
	jQuery('#ext-affiliate-widget div:first').animate({'margin-top': m+'px'}, 1000);
	}, 15000);
});
</script>
</body>
</html>
