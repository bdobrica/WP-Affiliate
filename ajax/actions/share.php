<?php
define ('WP_USE_THEMES', false);
include (dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))).'/wp-blog-header.php');

global $current_user;
get_currentuserinfo();
//if ($current_user->ID && (!current_user_can('manage_affiliation'))) die ('ERROR');

list ($url, $get) = explode ('?', $_SERVER['REQUEST_URI']);
$url = 'http://' . $_SERVER['SERVER_NAME'] . $url;

if ($_POST['signon']) {
	$credentials = array (
		'user_login' => $wpdb->escape($_POST['log']),
		'user_password' => $wpdb->escape($_POST['pwd']),
		'remember' => $_POST['rememberme'] ? 1 : 0
		);
	$user = wp_signon ($credentials, false);
	
	$out = '';
	$out .= '<div class="wp-affiliate-content">';
	$out .= '<div class="wp-affiliate-close"><img src="'.WP_AFFILIATE_URL.'/icons/close.png" /></div>';
	$out .= '<div class="wp-affiliate-box">';

	if (is_wp_error ($user)) {
		$out .= $user->get_error_message ();
		$out .= wp_affiliate_login ();
		}
	else {
		setcookie (WP_AFFILIATE_Cookie, $user->ID, time() + WP_AFFILIATE_Timeout, '/', '.traininguri.ro', FALSE, TRUE);

		$out .= '<p><strong>Salut '.$user->display_name.',</strong></p>';
		$out .= '<p>Copiaza link-ul de mai jos si foloseste-l pentru a promova cursurile si evenimentele Extreme Training! Pentru fiecare pagina a site-ului www.traininguri.ro vei avea un link diferit. Astfel incat iti oferim flexibilitatea in a alege locul in care ii redirectionezi pe vizitatorii tai pentru a converti cat mai eficient vizitele in clienti si implicit comision.</p>';
		$out .= '<p><input type="text" name="" value="xa='.$user->ID.'" class="wp-affiliate-url" /></p>';
		$out .= '<div class="wp-affiliate-box">';
		$out .= '<div class="wp-affiliate-left"><a href="/wp-admin/admin.php?page=wp_affiliate_reports" target="_blank" title="Rapoarte afiliere">Rapoarte afiliere &raquo;</a></div>';
		$out .= '<div class="wp-affiliate-right"><a href="/wp-admin/profile.php" target="_blank" title="Profilul tau">Profilul tau &raquo;</a></div>';
		$out .= '<div style="clear: both;"></div>';
		$out .= '</div>';
		}

	$out .= '</div>';
	$out .= '</div>';

	echo $out;
	exit (0);
	}

if ($_POST['register']) {
	$out = '';
	$out .= '<div class="wp-affiliate-content">';
	$out .= '<div class="wp-affiliate-close"><img src="'.WP_AFFILIATE_URL.'/icons/close.png" /></div>';
	$out .= '<div class="wp-affiliate-box">';

	$error = false;
	if (!$_POST['agree']) {
		$out .= '<p><strong>EROARE:</strong> Pentru a intra in programul de afiliere Extreme Training trebuie sa accepti <a href="/program-afiliere/termeni-si-conditii/" target="_blank">termenii si conditiile acestuia</a>.</p>';
		$error = true;
		}

	$username = $wpdb->escape($_POST['username']);
	if(empty($username)) {
		$out .= '<p><strong>EROARE:</strong> Numele de utilizator este obligatoriu!</p>';
		$error = true;
		}

	$email = $wpdb->escape($_POST['email']);
	if(!preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/", $email)) {
		$out .= '<p><strong>EROARE:</strong> Adresa de email nu este valida!</p>';
		$error = true;
		}
		
	if ($error)
		$out .= wp_affiliate_register ();
	else {
		$random_password = wp_generate_password( 8, false );
		$user_id = wp_create_user( $username, $random_password, $email );
		if ( is_wp_error($user_id) ) {
			$out .= '<p><strong>EROARE:</strong> Alegeti alt nume de utilizator. '.$username.' este deja folosit.</p>';
			$out .= wp_affiliate_register ();
			$error = true;
			}
		else {
			wp_update_user (array (
				'ID' => $user_id,
				'role' => 'wp_affiliate',
				));

			$content = file_get_contents (WP_AFFILIATE_Cache . '/template/registration.html');
			$content = str_replace (array ('{username}', '{password}'), array ($username, $random_password), $content);

			wp_crm_mail ($email, 'Bine ai venit in programul de afiliere Extreme Training!', $content);

			$out .= '<p><strong>SUCCES!</strong> Verifica-ti adresa de email. Vei primi in cel mai scurt timp parola pe care o vei putea folosi pentru autentificare:</p>';
			$out .= wp_affiliate_login ();
			}
		}
	$out .= '</div>';
	$out .= '</div>';

	echo $out;
	exit (0);
	}

if ($_POST['a'] == 'display') {
	$out = '';
	$out .= '<div class="wp-affiliate-content">';
	$out .= '<div class="wp-affiliate-close"><img src="'.WP_AFFILIATE_URL.'/icons/close.png" /></div>';

	if ($current_user->ID) {
		$out .= '<p><strong>Salut '.$current_user->display_name.',</strong></p>';
		$out .= '<p>Copiaza link-ul de mai jos si foloseste-l pentru a promova cursurile si evenimentele Extreme Training! Pentru fiecare pagina a site-ului www.traininguri.ro vei avea un link diferit. Astfel incat iti oferim flexibilitatea in a alege locul in care ii redirectionezi pe vizitatorii tai pentru a converti cat mai eficient vizitele in clienti si implicit comision.</p>';
		$out .= '<p><input type="text" name="" value="xa='.$current_user->ID.'" class="wp-affiliate-url" /></p>';
		$out .= '<div class="wp-affiliate-box">';
		$out .= '<div class="wp-affiliate-left"><a href="/wp-admin/admin.php?page=wp_affiliate_reports" target="_blank" title="Rapoarte afiliere">Rapoarte afiliere &raquo;</a></div>';
		$out .= '<div class="wp-affiliate-right"><a href="/wp-admin/profile.php" target="_blank" title="Profilul tau">Profilul tau &raquo;</a></div>';
		$out .= '<div style="clear: both;"></div>';
		$out .= '</div>';
		}
	else {
		$out .= '<div class="wp-affiliate-box">';
		$out .= '<div class="wp-affiliate-left wp-affiliate-login"><p><strong>Sunt deja inscris in programul de afiliere Extreme Training. Login:</strong></p><img src="'.WP_AFFILIATE_URL.'/icons/key.png"></div>';
		$out .= '<div class="wp-affiliate-right"><a style="color: #000; text-decoration: none;" href="http://www.traininguri.ro/partener-afiliat-extreme-training/" title="Detalii afiliere Extreme Training"><p><strong>Doresc sa aflu mai multe detalii despre programul de afiliere Extreme Training.</strong></p><img src="'.WP_AFFILIATE_URL.'/icons/medal.png"></a></div>';
		$out .= '</div>';
		$out .= '<div style="clear: both;"></div>';
		}

	$out .= '</div>';

	echo $out;
	exit (0);
	}

if ($_POST['a'] == 'login') {
	$out = '';
	$out .= '<div class="wp-affiliate-content">';
	$out .= '<div class="wp-affiliate-close"><img src="'.WP_AFFILIATE_URL.'/icons/close.png" /></div>';
	$out .= '<div class="wp-affiliate-box">';
	$out .= wp_affiliate_login();
	$out .= '</div>';
	$out .= '<div style="clear: both;"></div>';
	$out .= '<div style="text-align: center; color: #c00;" class="wp-affiliate-register">Apasa aici pentru a te inscrie gratuit in programul de afiliere Extreme Training.</div>';
	$out .= '<div style="clear: both;"></div>';
	$out .= '</div>';

	echo $out;
	exit (0);
	}

if ($_POST['a'] == 'register') {
	$out = '';
	$out .= '<div class="wp-affiliate-content">';
	$out .= '<div class="wp-affiliate-close"><img src="'.WP_AFFILIATE_URL.'/icons/close.png" /></div>';
	$out .= '<div class="wp-affiliate-box">';
	$out .= wp_affiliate_register();
	$out .= '</div>';
	$out .= '<div style="clear: both;"></div>';
	$out .= '<div style="text-align: center; color: #c00;" class="wp-affiliate-login">Apasa aici daca esti deja inscris in programul de afiliere Extreme Training.</div>';
	$out .= '<div style="clear: both;"></div>';
	$out .= '</div>';

	echo $out;
	exit (0);
	}

print_r($_POST);

die ('ERROR');
?>
