<?php
/*
Plugin Name: WP Affiliate
Plugin URI: http://www.traininguri.ro
Description: Pentru Chief
Version: 0.1
Author: Bogdan Dobrica
Author URI: http://www.ublo.ro
*/

define (WP_AFFILIATE_URL, WP_PLUGIN_URL . '/' . basename(dirname(__FILE__)));
define (WP_AFFILIATE_DIR, dirname(__FILE__));
define (WP_AFFILIATE_Cache, dirname(__FILE__).'/cache');
define (WP_AFFILIATE_Cookie, 'WP_AFFILIATE_COOKIE');
define (WP_AFFILIATE_Timeout, 62208000);
define (WP_AFFILIATE_Percent, 0.2);
define (WP_AFFILIATE_Child_Percent, 0.1);

include (dirname(__FILE__).'/libs/class.afl.php');

function wp_affiliate_parse_url ($url) {
	$parsed = parse_url($url);
	return $parsed['scheme'] . '://' . $parsed['host'];
	}

function wp_affiliate_cut ($string, $length) {
	if (strlen($string) < $length) return $string;
	return substr($string, 0, $length) . '...';
	}

function wp_affiliate_register ($echo = FALSE) {
	global $wpdb;
	$out = '';

	$out .= '<form action="" method="post">
<input type="hidden" name="register" value="1" />
<p>
<label>Nume utilizator:</label><br />
<input type="text" name="username" value="" />
</p>
<p>
<label>Adresa de email:</label><br />
<input type="text" name="email" value="" />
</p>
<p>
<input type="checkbox" name="agree" value="1" id="wp-affiliate-agree" /><label for="wp-affiliate-agree"> Da, sunt de acord cu <a href="/program-afiliere/termeni-si-conditii/" target="_blank">termenii si conditiile</a> programului de afiliere Extreme Training.</label>
</p>
<p style="text-align: center;">
<input type="button" class="wp-affiliate-submit" name="register" value="Inscrie-te!" />
</p>
</form>';
	$out .= '<p style="text-align: justify;">Pentru a confirma inscrierea ta in programul de afiliere Extreme Training si pentru a verifica validitatea adresei de email, vei primi prin email o parola temporara. Dupa ce te vei autentifica prima data, vei putea schimba parola folosind bara de meniu din susul paginii.</p>';

	if (!$echo) return $out;
	echo $out;
	}

function wp_affiliate_login ($echo = FALSE) {
	$out = '';

	$out .= '<form name="loginform" id="loginform" action="" method="post">
	<input type="hidden" name="signon" value="1" />
	<p>
		<label>Nume utilizator:</label><br />
		<input type="text" name="log" id="user_login" class="input" value="" size="20" tabindex="10" /></label>
	</p>
	<p>
		<label>Parola:</label><br />
		<input type="password" name="pwd" id="user_pass" class="input" value="" size="20" tabindex="20" /></label>
	</p>
	<p class="forgetmenot"><input name="rememberme" type="checkbox" id="rememberme" value="forever" tabindex="90" /> <label for="rememberme">Pastreaza-ma autentificat pe acest calculator.</label></p>
	<p style="text-align: center;">
		<input type="button" name="login" class="wp-affiliate-submit" value="Autentificare" tabindex="100" />
		<input type="hidden" name="testcookie" value="1" />
	</p>
</form>
<p id="nav">
<a href="'.get_option('home').'/wp-login.php?action=lostpassword" title="Password Lost and Found">Ti-ai pierdut parola?</a>
</p>';

	if (!$echo) return $out;
	echo $out;
	}

function wp_affiliate_cookie () {
	if (!isset($_COOKIE[WP_AFFILIATE_Cookie]) && $_GET['xa']) {
		$iam = new WP_AFL_Affiliate ($_GET['xa']);
		if ($iam->get())
			setcookie (WP_AFFILIATE_Cookie, $iam->get(), time() + WP_AFFILIATE_Timeout, '/', '.traininguri.ro', FALSE, TRUE);
		}
	else
	if (isset($_COOKIE[WP_AFFILIATE_Cookie]))
		setcookie (WP_AFFILIATE_Cookie, $_COOKIE[WP_AFFILIATE_Cookie], time() + WP_AFFILIATE_Timeout, '/', '.traininguri.ro', FALSE, TRUE);

	if ($_GET['xa']) {
		$affiliate = new WP_AFL_Affiliate ((int) $_GET['xa']);
		if ($affiliate->can()) {
			$click = new WP_AFL_Click (array (
				'stamp' => time(),
				'referer' => $_COOKIE[WP_CRM_Track_Cookie],
				'address' => $_SERVER['REMOTE_ADDR'],
				'affiliate' => $affiliate->get()
				));
			$click->save ();
			}
		}
	}

function wp_affiliate_filter ($text) {
	if (is_single()) {
		if (strpos($text, '<!--affiliate-register-->') !== FALSE)
			$text = str_replace ('<!--affiliate-register-->', '<div class="wp-affiliate-register wp-affiliate-inline"><img src="' . WP_AFFILIATE_URL . '/icons/medal.png" title="Inscrie-te in programul de afiliere Extreme Training" alt="Inscrie-te in programul de afiliere Extreme Training" /><span>Click aici pentru a-ti crea cont!</span></div>', $text);

		$text .= '<div style="clear: both;"></div>
	<div class="wp-affiliate-button"><img src="'.WP_AFFILIATE_URL.'/icons/profit-share.png" alt="Afiliere Extreme Training" title="Afiliere Extreme Training" /></div>
	<div class="wp-affiliate-content"></div>
<div style="clear: both;"></div>';
		}
	return $text;
	}



function wp_affiliates () {
	$affiliates = new WP_AFL_List ('affiliates');

	$out = '';

	$out .= '<h2>Affiliates</h2>';

	$c = 1;
	$rows = array ();
	$cols = array (
		'#',
		'XA=',
		'Name',
		'Clicks',
		'Leads',
		'Pending Sales',
		'Sales',
		'Pending Commision',
		'Commision',
		);
	$total = array ();
	foreach ($affiliates->get() as $affiliate) {
		$rows[] = array (
			$c++,
			$affiliate->get(),
			$affiliate->get('name'),
			$affiliate->get('clicks count'),
			$affiliate->get('events count'),
			$affiliate->get('pending sales').' lei',
			$affiliate->get('sales').' lei',
			$affiliate->get('pending value').' lei',
			$affiliate->get('value').' lei',
			);
		$total['clicks count'] += $affiliate->get('clicks count');
		$total['events count'] += $affiliate->get('events count');
		$total['pending sales'] += $affiliate->get('pending sales');
		$total['sales'] += $affiliate->get('sales');
		$total['pending value'] += $affiliate->get('pending value');
		$total['value'] += $affiliate->get('value');
		}
	$rows[] = array (
		'',
		'',
		'Total',
		$total['clicks count'],
		$total['events count'],
		$total['pending sales'].'lei',
		$total['sales'].'lei',
		$total['pending value'].'lei',
		$total['value'].'lei',
		);

	$out .= wp_crm_display_table ($cols, $rows, array ('class' => 'widefat wp-crm-table wp-crm-raised nofooter'));

	echo $out;
	}

function wp_affiliate () {
	global $wpdb;
	$iam = new WP_AFL_Affiliate ();
	$events = new WP_AFL_List ('events', $iam);
	$clicks = new WP_AFL_List ('clicks', $iam);

	$vals = array ();
	foreach ($events->get() as $event) {
		if ($event->get('paid')) {
			$vals['approved-sales'] += (float) $event->get('sales');
			}
		else {
			$vals['pending-sales'] += (float) $event->get('pending sales');
			}
		$vals['total-sales'] += (float) $event->get('pending sales');
		}

	$out = '';
	$out .= '<h2>Bine ai venit, '.$iam->get('name').'!</h2>';

	$out .= '<div class="wp-afl-excerpt">';
	$out .= '<p><span style="background: #f26;"></span> Clickuri: <strong>'.$clicks->get('size').'</strong></p>';
	$out .= '<p><span style="background: #00c;"></span> Clienti: <strong>'.$events->get('size').'</strong></p>';
	$out .= '<p><span style="background: #526;"></span> Vanzari in asteptare: <strong>'.$vals['pending-sales'].' lei</strong></p>';
	$out .= '<p><span style="background: #fc0;"></span> Vanzari aprobate: <strong>'.$vals['approved-sales'].' lei</strong></p>';
	$out .= '<p><span style="background: #ccc;"></span> Vanzari anulate: <strong>0 lei</strong></p>';
	$out .= '<p><span style="background: #f60;"></span> Vanzari finale: <strong>'.$vals['approved-sales'].' lei</strong></p>';
	$out .= '<p><span style="background: #0cc;"></span> Comisioane in asteptare: <strong>'.round($vals['pending-sales'] * WP_AFFILIATE_Percent, 2).' lei</strong></p>';
	$out .= '<p><span></span> Comisioane incasate: <strong>0 lei</strong></p>';
	$out .= '<p><span style="background: #0c0;"></span> Rest de plata: <strong>'.round($vals['approved-sales'] * WP_AFFILIATE_Percent, 2).' lei</strong></p>';
	$out .= '</div>';

	#$post = get_post (10548);
	#$out .= $post->post_content_filtered;

/*	$out .= 'Poti adauga un nou cod de afiliere pe pagina ta de internet apasand <a href="">aici</a>, sau poti verifica stadiul in care se afla contul tau in raportul de mai jos. Datele se actualizeaza in timp real!';

	$out .= '<h2>Pasul 1: Alege un curs!</h2><ul class="wp-afl-products">';
	$out .= '<li><input type="checkbox" id="wp-afl-check-all" class="wp-afl-check-all" name="wp-afl-check-all" /><label for="wp-afl-check-all"> sau Toate cursurile!</label>';

	$wp_crm_products = new WP_CRM_List ('products');
	foreach (($wp_crm_products->get()) as $product) {
		if (!$product->is('active')) continue;
		$out .= '<li><input type="checkbox" class="wp-afl-ajax-bin" id="wp-afl-product-'.$product->get().'" name="wp-aff-product-'.$product->get().'" value="'.$product->get().'"><label for="wp-aff-product-'.$product->get().'" /> '.$product->get('name').'</label></li>';
		}

	$out .= '</ul><div style="clear: both;"></div>';
	$out .= '<h2>Pasul 2: Copiaza codul de mai jos in site-ul tau!</h2>';
	$out .= '<textarea id="wp-afl-product-code"></textarea>';*/
	
	echo $out;
	}

function wp_affiliate_reports () {
	$iam = new WP_AFL_Affiliate ();

	$out = '';

	$c = 1;
	$cols = array (
		'Nr.',
		'Data',
		'Referer',
		'IP',
		'Client',
		'Produse',
		'Valoare Factura',
		'Comision in Asteptare',
		'Data Incasarii',
		'Comision Aprobat',
		);
	$rows = array ();

	$comm = array ('real' => 0.00, 'virtual' => 0.00);
	$events = new WP_AFL_List ('events', $iam);
	if (!$events->is('empty')) {
		foreach ($events->get() as $event) {
			if ($event->get('invoice')->get('value')) {
				$rows[] = array (
					$c++,
					$event->get('date', 'd-m-Y H:i'),
					'<a href="'.$event->get('referer').'" target="_blank">'.wp_affiliate_parse_url($event->get('referer')).'</a>',
					$event->get('address'),
					substr ($event->get('invoice')->buyer->get('name'), 0, 4) . '...',
					$event->get('invoice')->get('affiliate products'),
					$event->get('invoice')->get('value') . ' lei',
					round($event->get('invoice')->get('value') * WP_AFFILIATE_Percent, 2).' lei',
					$event->get('paid') ?
						date('d-m-Y H:i', $event->get('invoice')->get('paid date')) :
						'n/a',
					$event->get('paid') ?
						('<span style="color: #0c0;">'.$event->get('value').' lei</span>') :
						('<span style="color: #c00;">'.$event->get('value').' lei</span>')
					);
				}
			else {
				$rows[] = array (
					$c++,
					$event->get('date', 'd-m-Y H:i'),
					'<a href="'.$event->get('referer').'" target="_blank">'.wp_affiliate_parse_url($event->get('referer')).'</a>',
					$event->get('address'),
					substr ($event->get('invoice')->buyer->get('name'), 0, 4) . '...',
					$event->get('invoice')->get('affiliate products'),
					'<strike>0 lei</strike> (anulat)',
					'<strike>0 lei</strike> (anulat)',
					'n/a',
					'<strike>0 lei</strike> (anulat)',
					);
				}
			
			$comm['real'] += $event->get('value');
			$comm['virtual'] += round ($event->get('invoice')->get('value') * WP_AFFILIATE_Percent, 2);
			}

		$out .= wp_crm_display_table ($cols, $rows, array ('class' => 'widefat wp-crm-table wp-crm-raised nofooter'));
		}
	else
		$out .= '<p>Deocamdata nu s-au inregistrat evenimente in contul tau de afiliere. Fii creativ si rezultatele nu vor inceta sa apara!</p>';

	$out = '<h2>Bine ai venit, '.$iam->get('name').'!</h2><p>In prezent ai trimis <strong>'.($c-1).'</strong> lead'.($c > 2 ? '-uri' : '').' pe www.traininguri.ro. Din acestea, poti incasa maximum <strong>'.$comm['virtual'].' lei</strong> si ai in contul de afiliere <strong>'.$comm['real'].' lei</strong>. '.($comm['real'] < $comm['virtual'] ? 'Diferenta de comision apare deoarece intotdeauna sunt intarzieri intre plasarea comenzii si incasarea contravalorii serviciilor oferite.' : '').'</p>' . $out;
	$out .= '<p>Pragul pentru retragerea comisionului este 1000 de lei.</p>';

	echo $out;	
	}

function wp_affiliate_guide () {
	global $wpdb;
	$iam = new WP_AFL_Affiliate ();

	$out = '';
	$out .= '<h2>Bine ai venit, '.$iam->get('name').'!</h2>';
	$out .= '<div class="wp-afl-content">' . apply_filters ('the_content', $wpdb->get_var ($wpdb->prepare ('select post_content from `'.$wpdb->prefix.'posts` where id=%d;', 10555))) . '</div>';
	
	echo $out;
	}

function wp_affiliate_disclaimer () {
	global $wpdb;
	$iam = new WP_AFL_Affiliate ();

	$out = '';
	$out .= '<h2>Bine ai venit, '.$iam->get('name').'!</h2>';
	$out .= '<div class="wp-afl-content">' . apply_filters ('the_content', $wpdb->get_var ($wpdb->prepare ('select post_content from `'.$wpdb->prefix.'posts` where id=%d;', 10557))) . '</div>';
	
	echo $out;
	}

function wp_affiliate_contact () {
	global $wpdb;
	$iam = new WP_AFL_Affiliate ();

	$out = '';
	$out .= '<h2>Bine ai venit, '.$iam->get('name').'!</h2>';
	$out .= '<div class="wp-afl-content">' . apply_filters ('the_content', $wpdb->get_var ($wpdb->prepare ('select post_content from `'.$wpdb->prefix.'posts` where id=%d;', 10561))) . '</div>';
	
	echo $out;
	}

function wp_affiliate_details () {
	$iam = new WP_AFL_Affiliate ();

	$out = '';
	$out .= '<h2>Bine ai venit, '.$iam->get('name').'!</h2>';
	
	echo $out;
	}

function wp_affiliate_scripts () {
	wp_enqueue_script ('wp-affiliate-share', WP_AFFILIATE_URL . '/scripts/wp-affiliate-share.js', array('jquery'), '0.2');
	wp_enqueue_style  ('wp-affiliate-share', WP_AFFILIATE_URL . '/style/wp-affiliate-share.css', '0.2');
	}

function wp_affiliate_admin_scripts () {
	wp_enqueue_script ('wp-affiliate', WP_AFFILIATE_URL . '/scripts/wp-affiliate.js', array('jquery'), '0.1');
	wp_enqueue_style  ('wp-affiliate', WP_AFFILIATE_URL . '/style/wp-affiliate.css', '0.1');
	}

function wp_affiliate_admin_menu () {
	add_menu_page ('WP Affiliates', 'WP Affiliates', 'list_users', 'wp_affiliates', 'wp_affiliates');
	add_menu_page ('Rezumat Afiliere', 'Rezumat Afiliere', 'manage_affiliation', 'wp_affiliate', 'wp_affiliate');
	add_submenu_page ('wp_affiliate', 'Rapoarte', 'Rapoarte', 'manage_affiliation', 'wp_affiliate_reports', 'wp_affiliate_reports');
	add_submenu_page ('wp_affiliate', 'Ghid Afiliat', 'Ghid Afiliat', 'manage_affiliation', 'wp_affiliate_guide', 'wp_affiliate_guide');
	add_submenu_page ('wp_affiliate', 'Termeni si Conditii', 'Termeni si Conditii', 'manage_affiliation', 'wp_affiliate_disclaimer', 'wp_affiliate_disclaimer');
	add_submenu_page ('wp_affiliate', 'Contact', 'Contact', 'manage_affiliation', 'wp_affiliate_contact', 'wp_affiliate_contact');
	add_submenu_page ('wp_affiliate', 'Date Facturare', 'Date Facturare', 'manage_affiliation', 'wp_affiliate_details', 'wp_affiliate_details');
#	add_submenu_page ('wp_affiliate', 'Unelte Promovare', 'Unelte Promovare', 'manage_affiliation', 'wp_affiliate_tools', 'wp_affiliate_tools');
	}

function wp_affiliate_firstrun () {
        add_role ('wp_affiliate', 'WP Affiliate', array (
                'read' => true,
                'manage_affiliation' => true,
                ));
        }

function wp_affiliate_lastrun () {
	}

register_activation_hook (__FILE__, 'wp_affiliate_firstrun');
register_deactivation_hook (__FILE__, 'wp_affiliate_lastrun');

add_action ('init', 'wp_affiliate_cookie');
add_action ('wp_enqueue_scripts', 'wp_affiliate_scripts');
add_action ('admin_enqueue_scripts', 'wp_affiliate_admin_scripts');
add_action ('admin_menu', 'wp_affiliate_admin_menu');

add_filter ('the_content', 'wp_affiliate_filter');
?>
