<?php
require_once '../config.php';

class ScriptBase
{
	const DEFAULT_LANGUAGE = 'english';

	protected $_fileName;
	protected $_log;
	protected $_registry;
	protected $db;

	public function __construct()
	{
		date_default_timezone_set('UTC');
		require_once DIR_SYSTEM . 'engine/registry.php';
		require_once DIR_SYSTEM . 'engine/loader.php';
		require_once DIR_SYSTEM . 'library/config.php';
		require_once DIR_SYSTEM . 'library/db.php';
		require_once DIR_SYSTEM . 'library/language.php';
		require_once DIR_SYSTEM . 'library/cache.php';
		require_once DIR_SYSTEM . 'library/length.php';
		require_once DIR_SYSTEM . 'library/customer.php';
		require_once DIR_SYSTEM . 'library/url.php';
		require_once(DIR_SYSTEM . 'library/request.php');
		require_once(DIR_SYSTEM . 'library/template.php');
		require_once 'Class_Currency.php';
		require_once DIR_SYSTEM . 'engine/model.php';
		require_once DIR_SYSTEM . 'library/log.php';
		require_once DIR_SYSTEM . 'helper/utf8.php';

		$this->_registry = new Registry();
		$loader = new Loader($this->_registry);
		$this->_registry->set('load', $loader);
		$db = new DB(DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
		$language = new Language(self::DEFAULT_LANGUAGE);
		$language->load(self::DEFAULT_LANGUAGE);
		$this->_registry->set('language', $language);
		$this->_registry->set('db', $db);
		$this->db = $db;
		$config = new Config();
		$config->set('config_store_id', 0);
		$query = $db->query("SELECT * FROM " . DB_PREFIX . "setting WHERE store_id = '0' OR store_id = '" . (int)$config->get('config_store_id') . "' ORDER BY store_id ASC");
		foreach ($query->rows as $setting)
		{
			if (!$setting['serialized'])
			{
				$config->set($setting['key'], $setting['value']);
			}
			else
			{
				$config->set($setting['key'], unserialize($setting['value']));
			}
		}
		$config->set('config_url', HTTP_SERVER);
		$config->set('config_ssl', HTTPS_SERVER);
		$config->set('config_language_id', 1);
		$this->_registry->set('config', $config);

		$url = new Url($config->get('config_url'), $config->get('config_secure') ? $config->get('config_ssl') : $config->get('config_url'));
		$this->_registry->set('url', $url);

		// Customer
		$this->_registry->set('customer', new Customer($this->_registry));

		$log = new Log($config->get('config_error_filename'));
		$this->_registry->set('log', $log);
		$this->_registry->set('cache', new Cache());
		$length = new Length($this->_registry);
		$this->_registry->set('length', $length);
		$this->_log = $this->_registry->get('log');
		$this->_registry->set('currency', new Class_Currency($this->_registry));
		$this->_registry->set('request', new Request());
	}

	public function __get($name)
	{
		return $this->_registry->get($name);
	}
}
?>
