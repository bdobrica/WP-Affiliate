<?php
class WP_AFL_Affiliate {
	private $current;
	private $affiliations;
	private $products;

	private $person;
	private $company;
	private $sites;
	private $events;
	private $clicks;
	
	public function __construct ($data = null) {
		if (is_numeric($data))
			$this->current = get_userdata ((int) $data);
		else
			$this->current = wp_get_current_user();
		$this->products = new WP_AFL_List ('products', $this->current->ID);
		}

	public function add ($what = null, $data = null) {
		global $wpdb;
		if (is_object($what)) {
			if (get_class($what) == 'WP_CRM_Product') {
				$sql = $wpdb->prepare ('insert into `'.$wpdb->prefix.'affiliates` (uid,pid,series,number) values (%d,%d,%s,%d);', $this->current->ID, $what->get(), $what->get('current series'), $what->get('current number'));
				$wpdb->query ($sql);
				}
			if (get_class($what) == 'WP_CRM_Person') {
				add_user_meta ($this->current->ID, 'wp_affiliate_person', $what->get(), true);
				}
			if (get_class($what) == 'WP_CRM_Company') {
				add_user_meta ($this->current->ID, 'wp_affiliate_company', $what->get(), true);
				}
			}
		if (is_string($what)) {
			if ($what == 'site')
				add_user_meta ($this->current->ID, 'wp_affiliate_sites', $data, false);
			}
		}

	public function del ($what = null) {
		global $wpdb;
		if (is_object($what)) {
			if (get_class($what) == 'WP_CRM_Product') {
				$sql = $wpdb->prepare ('delete from `'.$wpdb->prefix.'affiliates` where uid=%d and pid=%d;', $this->current->ID, $what->get());
				$wpdb->query ($sql);
				}
			}
		if (is_string($what)) {
			if ($what == 'site')
				delete_user_meta ($this->current->ID, 'wp_affiliate_sites', $data);
			}
		}

	public function get ($key = '') {
		if ($key == 'name') return $this->current->display_name;
		if ($key == 'products') return $this->products;
		if ($key == 'person') {
			if (!$this->person) $this->person = get_meta_data ($this->current->ID, 'wp_affiliate_person', true);
			return $this->person;
			}
		if ($key == 'company') {
			if (!$this->company) $this->company = get_meta_data ($this->current->ID, 'wp_affiliate_company', true);
			return $this->company;
			}
		if ($key == 'sites') {
			if (!$this->sites) $this->sites = get_meta_data ($this->current->ID, 'wp_affiliate_sites', false);
			return $this->sites;
			}
		if ($key == 'events') {
			if (!$this->events) $this->events = new WP_AFL_List ('events', $this->current->ID);
			return $this->events;
			}
		if ($key == 'events count')
			return $this->get('events')->get('size');
		if ($key == 'clicks') {
			if (!$this->clicks) $this->clicks = new WP_AFL_List ('clicks', $this->current->ID);
			return $this->clicks;
			}
		if ($key == 'clicks count')
			return $this->get('clicks')->get('size');
		if ($key == 'pending sales') {
			$sales = 0;
			foreach ($this->get('events')->get() as $event)
				$sales += $event->get('pending sales');
			return $sales;
			}
		if ($key == 'pending value') {
			$value = 0;
			foreach ($this->get('events')->get() as $event)
				$value += $event->get('pending value');
			return $value;
			}
		if ($key == 'sales') {
			$sales = 0;
			foreach ($this->get('events')->get() as $event)
				$sales += $event->get('sales');
			return $sales;
			}
		if ($key == 'value') {
			$value = 0;
			foreach ($this->get('events')->get() as $event)
				$value += $event->get('value');
			return $value;
			}
		if ($key == 'mail hash') {
			return md5(trim(strtolower($this->current->user_email)));
			}
		return $this->current->ID;
		}

	public function set ($key = '', $value = '') {
		if ($key == 'products') {
			
			}
		}

	public function is ($what = '') {
		if ($what == 'known') return ($this->get ('person') || $this->get('company')) ? TRUE : FALSE;
		if ($what == 'company') return ($this->get ('company')) ? TRUE : FALSE;
		if ($what == 'person') return ($this->get ('person') && (!$this->get ('company'))) ? TRUE : FALSE;
		return FALSE;
		}

	public function can ($what = 'manage_affiliation') {
		return user_can ($this->current, $what);
		}
	
	public function __destruct () {
		}
	
	};

class WP_AFL_Affiliation {

	public function __construct () {
		}

	public function get () {
		}

	public function set () {
		}
	
	public function __destruct () {
		}
	
	};

class WP_AFL_Event {
	private $ID;
	private $user;
	private $invoice;
	private $referer;
	private $address;
	private $cookie;
	private $stamp;
	private $paid;

	public function __construct ($data = null) {
		global $wpdb;
		if (is_numeric($data)) {
			$sql = $wpdb->prepare ('select * from `'.$wpdb->prefix.'affiliate_log` where id=%d;', (int) $data);
			$data = $wpdb->get_row ($sql);

			if ($data) {
				$this->ID = (int) $data->id;
				$this->user = new WP_AFL_Affiliate ((int) $data->uid);
				$this->invoice = new WP_CRM_Invoice ((int) $data->iid);
				$this->referer = $data->referer;
				$this->address = $data->address;
				$this->cookie = $data->cookie;
				$this->stamp = $data->stamp;
				$this->paid = $data->flags;
				}
			}
		else
		if (is_array($data)) {
			$this->user = new WP_AFL_Affiliate ((int) $data['user']);
			$this->invoice = new WP_CRM_Invoice ((int) $data['invoice']);
			$this->referer = $data['referer'];
			$this->address = $data['address'];
			$this->cookie = $data['cookie'];
			$this->stamp = is_numeric($data['stamp']) ? (int) $data['stamp'] : strtotime($data['stamp']);
			$this->paid = (int) $data['paid'];
			}
		}

	public function get ($key = '', $value = '') {
		global $wpdb;
		if ($key == 'user id') return (int) $this->user->get();
		if ($key == 'user') return $this->user;
		if ($key == 'invoice id') return (int) $this->invoice->get('id');
		if ($key == 'invoice') return $this->invoice;
		if ($key == 'referer') return $this->referer;
		if ($key == 'address') return $this->address;
		if ($key == 'cookie') return $this->cookie;
		if ($key == 'stamp' || $key == 'time') return $this->stamp;
		if ($key == 'date') return date($value, $this->stamp);
		if ($key == 'paid') {
			if (!$this->invoice->get('id')) return FALSE;
			if ($this->paid) return TRUE;
			if ($this->invoice->is('paid') && !$this->invoice->is('partial paid')) {
				$this->paid = 1;
				$wpdb->query ($wpdb->prepare ('update `'.$wpdb->prefix.'affiliate_log` set flags=%d where id=%d;', $this->paid, $this->ID));
				return TRUE;
				}
			return FALSE;
			}
		if ($key == 'pending sales') {
			if (!$this->invoice->get('id')) return 0;
			return $this->invoice->get('value');
			}
		if ($key == 'pending value') {
			if (!$this->invoice->get('id')) return 0;
			return WP_AFFILIATE_Percent * $this->invoice->get('value');
			}
		if ($key == 'sales') {
			if (!$this->invoice->get('id')) return 0;
			if ($this->get('paid')) return $this->invoice->get('paid value');
			return 0;
			}
		if ($key == 'value') {
			if (!$this->invoice->get('id')) return 0;
			if ($this->get('paid')) return WP_AFFILIATE_Percent * $this->invoice->get('paid value');
			return 0;
			}
		return $this->ID;
		}

	public function set ($key = '', $value = '') {
		global $wpdb;
		}

	public function save () {
		global $wpdb;
		if ($this->ID) return FALSE;
		$wpdb->query ($wpdb->prepare ('insert into `'.$wpdb->prefix.'affiliate_log` (uid,iid,referer,address,cookie,stamp,flags) values (%d,%d,%s,%s,%d,%d,%d);', array (
			$this->user->get(),
			$this->invoice->get('id'),
			$this->referer,
			$this->address,
			$this->cookie,
			$this->stamp,
			$this->paid)));
		$this->ID = $wpdb->insert_id;
		return TRUE;
		}
	
	public function __destruct () {
		}
	
	};

class WP_AFL_Click {
	private $ID;
	private $affiliate;
	private $stamp;
	private $address;
	private $referer;

	public function __construct ($data = null) {
		global $wpdb;
		if (is_numeric($data)) {
			$sql = $wpdb->prepare ('select * from `'.$wpdb->prefix.'affiliate_clicks` where id=%d;', (int) $data);
			$data = $wpdb->get_row ($sql);
			$this->ID = (int) $data->id;
			$this->affiliate = (int) $data->uid;
			$this->stamp = (int) $data->stamp;
			$this->address = (int) $data->ip;
			$this->referer = $data->referer;
			}
		else {
			$this->affiliate = (int) $data['affiliate'];
			$this->stamp = is_numeric($data['stamp']) ? (int) $data['stamp'] : strtotime($data['stamp']);
			$this->address = $data['address'];
			$this->referer = $data['referer'];
			}
		}

	public function get ($key = '', $value = '') {
		if ($key == 'affiliate') return $this->affiliate;
		if ($key == 'stamp' || $key == 'time') return $this->stamp;
		if ($key == 'date') return date($value, $this->stamp);
		if ($key == 'address' || $key == 'ip') return $this->address;
		if ($key == 'referer') return $this->referer;
		return $this->ID;
		}

	public function save () {
		global $wpdb;
		if ($this->ID) return FALSE;
		$sql = $wpdb->prepare ('insert into `'.$wpdb->prefix.'affiliate_clicks` (uid,stamp,ip,referer) values (%d,%d,%s,%s);', array (
			$this->affiliate,
			$this->stamp,
			$this->address,
			$this->referer,
			));
		$wpdb->query ($sql);
		$this->ID = $wpdb->insert_id;
		if ($this->ID) return TRUE;
		return FALSE;
		}

	public function __destruct () {
		}
	}

class WP_AFL_List {
	private $list;
	private $filter;

	public function __construct ($type = null, $filter = array()) {
		global $wpdb;
		$this->list = array ();
		$this->filter = $filter;
		if ($type == 'products') {
			if (!is_array($this->filter) && is_object($this->filter)) $this->filter = array ('affiliate' => $this->filter->get());
			if (!is_array($this->filter) && is_numeric($this->filter)) $this->filter = array ('affiliate' => (int) $this->filter);
			if (!isset($this->filter['affiliate'])) return FALSE;
			$sql = $wpdb->prepare ('select pid,series,number from `'.$wpdb->prefix.'affiliates` where uid=%d;', $this->filter['affiliate']);
			$products = $wpdb->get_results ($sql);
			if (!empty($products))
				foreach ($products as $product)
					$this->list[] = new WP_CRM_Product (array (
						'series' => $product->series,
						'number' => $product->number,
						));
			}
		if ($type == 'events') {
			if (!is_array($this->filter) && is_object($this->filter)) $this->filter = array ('affiliate' => $this->filter->get());
			if (!is_array($this->filter) && is_numeric($this->filter)) $this->filter = array ('affiliate' => (int) $this->filter);
			if (!isset($this->filter['affiliate'])) return FALSE;
			$sql = $wpdb->prepare ('select id from `'.$wpdb->prefix.'affiliate_log` where uid=%d order by stamp desc;', $this->filter['affiliate']);
			$events = $wpdb->get_col ($sql);
			if (!empty($events))
				foreach ($events as $event)
					$this->list[] = new WP_AFL_Event ((int) $event);
			}
		if ($type == 'clicks') {
			if (!is_array($this->filter) && is_object($this->filter)) $this->filter = array ('affiliate' => $this->filter->get());
			if (!is_array($this->filter) && is_numeric($this->filter)) $this->filter = array ('affiliate' => (int) $this->filter);
			if (!isset($this->filter['affiliate'])) return FALSE;
			$sql = $wpdb->prepare ('select id from `'.$wpdb->prefix.'affiliate_clicks` where uid=%d order by stamp desc;', $this->filter['affiliate']);
			$clicks = $wpdb->get_col ($sql);
			if (!empty($clicks))
				foreach ($clicks as $click)
					$this->list[] = new WP_AFL_Click ((int) $click);
			}

		if ($type == 'affiliates') {
			$affiliates = new WP_User_Query ( array ('role' => 'wp_affiliate', 'fields' => array ('ID'), 'orderby' => 'registered') );
			foreach ($affiliates->get_results() as $affiliate)
				$this->list[] = new WP_AFL_Affiliate ((int) $affiliate->ID);
			}
		}

	public function is ($key = '') {
		if ($key == 'empty') return empty($this->list) ? TRUE : FALSE;
		}

	public function get ($key = '') {
		if ($key == 'size' || $key == 'count') return count($this->list);
		return $this->list;
		}

	public function set () {
		}
	
	public function __destruct () {
		}
	};
?>
