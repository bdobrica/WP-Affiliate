// wp-affiliate-products
jQuery(document).ready(function(){
	jQuery('.wp-afl-check-all').click(function(e){
		var q = '';
		jQuery(this).after(jQuery('<img src="/ro/wp-content/plugins/wp-crm/images/loading.gif" alt="" title="" width="16" height="16" />'));
		jQuery('.wp-afl-ajax-bin').attr('checked', jQuery(e.target).is(':checked') ? true : false).each(function(n,i){
			if (!jQuery(e.target).is(':checked')) return;
			q += jQuery(i).attr('name').replace('wp-aff-product-','') + ',';
			});
		jQuery.post('/wp-content/plugins/wp-crm/ajax/actions/save.php', 'wp-aff-products='+q, function(d){
			jQuery(e.target).next().remove();
			});
		});
	jQuery('.wp-afl-ajax-bin').click(function(e){
		jQuery(this).after(jQuery('<img src="/ro/wp-content/plugins/wp-crm/images/loading.gif" alt="" title="" width="16" height="16" />'));
		jQuery.post('/ro/wp-content/plugins/wp-crm/ajax/actions/save.php', jQuery(e.target).attr('name') + '=' +(jQuery(e.target).is(':checked') ? 1 : 0), function(d){
			jQuery(e.target).next().remove();
			});
		});
	});
