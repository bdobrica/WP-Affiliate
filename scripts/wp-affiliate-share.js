jQuery(document).ready(function(){
	var u = '/wp-content/plugins/wp-affiliate';
	jQuery('body').append('<div id="wp-affiliate-shadow" style="z-index: 9998;"></div>');
	jQuery('body').append('<div id="wp-affiliate-window" style="z-index: 9999;"></div>');
	var w = jQuery('#wp-affiliate-window');
	var z = '<img alt="Se incarca ..." title="Se incarca ..."  src="'+u+'/icons/loading.gif" />';

	var WPAShadow = function (o) {
		if (o) jQuery('#wp-affiliate-shadow').css('opacity',0).height(jQuery(document).height()).width(jQuery(document).width()).animate({'opacity':0.7});
		else jQuery('#wp-affiliate-shadow').animate({'opacity':0}, function(){jQuery('#wp-affiliate-shadow').height(0).width(0);});
		}

	var WPAInit = function () {
		jQuery('.wp-affiliate-close').click(function(f){ WPAShadow(0);w.hide('slow');w.empty(); });
		jQuery('.wp-affiliate-login').click(function(f){
			WPAShadow(1);
			w.show('slow');
			jQuery('html, body').animate({'scrollTop' : 0});
			jQuery.post(u + '/ajax/actions/share.php', {'a': 'login'}, function(d){
				w.html(d);
				WPAInit();
				});
			});
		jQuery('.wp-affiliate-register').click(function(f){
			WPAShadow(1);
			w.show('slow');
			jQuery('html, body').animate({'scrollTop' : 0});
			jQuery.post(u + '/ajax/actions/share.php', {'a': 'register'}, function(d){
				w.html(d);
				WPAInit();
				});
			});
		jQuery('.wp-affiliate-submit').click(function(f){
			WPAShadow(1);
			w.show('slow');
			var g = jQuery(f.target).closest('form');
			jQuery.post(g.attr('action') ? g.attr('action') : (u + '/ajax/actions/share.php'), g.serialize(), function(d){
				w.html(d);
				WPAInit();
				});
			});
		jQuery('.wp-affiliate-url').each(function(n,i){
			i = jQuery(i);
			if (window.location.href.indexOf('?') < 0)
				i.val(window.location.href + '?' + i.val());
			else
				i.val(window.location.href + '&' + i.val());
			});
		}

	jQuery('.wp-affiliate-button').click(function(e){
		jQuery('html, body').animate({'scrollTop' : 0});
		WPAShadow(1);
		jQuery.post(u + '/ajax/actions/share.php', {'a': 'display'}, function(d){
			w.html(d);
			w.show('slow');
			WPAInit();
			});
		});

	WPAInit();
	});
