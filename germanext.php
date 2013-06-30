<?php
/**
 * germanext Module
 *
 * @version 1.5.4 ** ONLY FOR PRESTASHOP VERSION 1.5.4 **
 *
 * @category  German Extension
 * @author    George June <j.june@silbersaiten.de>
 * @copyright Silbersaiten GbR <www.silbersaiten.de>
 *
 * Support: http://www.silbersaiten.de/
 *
 */
if ( ! defined('_PS_VERSION_')) {
	exit;
}

require_once(dirname(__FILE__) . '/defines.php');
    
class Germanext extends Module
{
	private static $_tblCache = array();
	private static $_fieldCache = array();
	private static $_languages;
	
	private $_html = '';
	private $_postErrors = array();

	private static $_templateOverrides = array(
		'ProductController',
		'AdminCustomersController',
		'AdminProductsController',
		'AdminOrdersController',
		'AuthController',
		'IdentityController',
		'OrderOpcController',
		'OrderController',
		'HistoryController',
		'OrderDetailController',
		'CompareController',
		'ContactController'
	);
    
	private static $_themeTplOverrides = array(
		'product-list.tpl',
		'order-steps.tpl',
		'shopping-cart-product-line.tpl'
	);
	
	private static $_pdfTranslations = array();
    
	public function __construct() {
		$this->name    = 'germanext';
		$this->tab     = 'administration';
		$this->version = '1.5.4';
		$this->author  = 'Silbersaiten';
		$this->module_key  = '868714cc747beb201e773f592f29e6b3';
        
		parent::__construct();
        
		$this->displayName = $this->l('Germanext');
	}
	
	
	/**************************************************************************
	 *                      INSTALL/UNINSTALL METHODS                         *
	 * These are the methods that are used to install or uninstall germanext  *
	 *                                                                        *
	 * Search points: install, create, setup, uninstall, destroy, purge       *
	 **************************************************************************/
	public function install() {
		$backup = $this->copyDir(_PS_ROOT_DIR_, GN_BACKUP_PATH, GN_INSTALL_COPY_PATH);

		if ( ! $backup
			|| ! $this->setConfigs(true)
			|| ! $this->setCMS()
			|| ! $this->makeDbChanges()
			|| ! $this->installTables()) {
			$this->uninstall();

			return false;
		}

		$filesCopied = $this->copyDir(GN_INSTALL_COPY_PATH, _PS_ROOT_DIR_);
        
		if ( ! $filesCopied) {
			$this->uninstall();
            
			return false;
		}

		$this->updateCountries();
		$this->setCountryZone();
		$this->updateOrderStates();
		$this->registerPaymentModules();

		if ( ! (
			parent::install()
			&& $this->registerHook('header')
			&& $this->registerHook('displayBackOfficeHeader')
			&& $this->registerHook('actionProductAttributeUpdate')
		)) {
			$this->uninstall();
			
			return false;
		}
		
		@unlink(_PS_CACHE_DIR_ . 'class_index.php');
		$this->_generateConfigXml();
		
		return true;
	}
    
	public function uninstall() {
		if (parent::uninstall()) {
			$this->copyDir(GN_BACKUP_PATH, _PS_ROOT_DIR_);
            
			$this->deleteDir($src, true);
            
			$this->uninstallTables();
			$this->setConfigs(false);
			
			@unlink(_PS_CACHE_DIR_ . 'class_index.php');
			$this->_generateConfigXml();
			
			return true;
		}
		
		return false;
	}
	
	public function disable($forceAll = false) {
		parent::disable($forceAll);

		@unlink(_PS_CACHE_DIR_ . 'class_index.php');
		$this->_generateConfigXml();
	}
	
	public function enable($forceAll = false) {
		parent::enable($forceAll);
		
		@unlink(_PS_CACHE_DIR_ . 'class_index.php');
		$this->_generateConfigXml();
	}
	
	protected function _generateConfigXml() {
		$xml = '<?xml version="1.0" encoding="UTF-8" ?>
		<module>
			<name>'.$this->name.'</name>
			<displayName><![CDATA['.Tools::htmlentitiesUTF8($this->displayName).']]></displayName>
			<version><![CDATA['.$this->version.']]></version>
			<description><![CDATA['.Tools::htmlentitiesUTF8($this->description).']]></description>
			<author><![CDATA['.Tools::htmlentitiesUTF8($this->author).']]></author>
			<tab><![CDATA['.Tools::htmlentitiesUTF8($this->tab).']]></tab>'.(isset($this->confirmUninstall) ? "\n\t".'<confirmUninstall>'.$this->confirmUninstall.'</confirmUninstall>' : '').'
			<is_configurable>'.(isset($this->is_configurable) ? (int)$this->is_configurable : 0).'</is_configurable>
			<need_instance>'.(int)$this->need_instance.'</need_instance>'.(isset($this->limited_countries) ? "\n\t".'<limited_countries>'.(count($this->limited_countries) == 1 ? $this->limited_countries[0] : '').'</limited_countries>' : '').'
			<is_installed>' . self::isInstalled($this->name) . '</is_installed>
			<is_enabled>' . self::isEnabled($this->name) . '</is_enabled>
		</module>';
		
		if (is_writable(_PS_MODULE_DIR_.$this->name.'/')) {
			file_put_contents(_PS_MODULE_DIR_ . $this->name . '/config.xml', $xml);
		}
	}
    
    
	/*
	* Assigns Germanext values to some Prestashop's config variables. Also
	* creates a few new variables.
	*
	* @access private
	*
	* @param bool $install - Set to "false" when uninstalling to revert
	*                        Germanext changes.
	*
	* @return bool
	*/
	private function setConfigs($install = true) {
		$languages = Language::getLanguages();
		// This is where the array with configuration variables is stored to
		// keep this file clean.
		require_once(GN_INSTALL_PATH . 'configs.inc.php');
        
		if (sizeof($_gn_configs)) {
			foreach ($_gn_configs as $config_name => $data) {
				if ( ! Validate::isConfigName($config_name)) {
					$this->_errors[] = $this->l('Invalid Configuration variable name:') . ' "' . $config_name . '"';
				}
				else {
					$key = $install ? 'install' : 'uninstall';
                    
					if (array_key_exists($key, $data)) {
						$value = $data[$key];
						
						if (array_key_exists('lang', $data) && $data['lang'] == true) {
							$value = array();
							
							foreach ($languages as $language) {
								$value[$language['id_lang']] = $data[$key];
							}
						}
						
						if ($install || ! array_key_exists('drop', $data)) {
							Configuration::updateValue($config_name, $value);
						}
						else {
							Configuration::deleteByName($config_name);
						}
					}
				}
			}
		}
        
		return ! sizeof($this->_errors);
	}
    
    
	/*
	* Creates Germanext CMS pages
	*
	* @access private
	*
	* @return void
	*/
	private static function setCMS() {
		// This is where the array with cms data is stored to keep this file
		// clean.
		require_once(GN_INSTALL_PATH . 'cms.inc.php');

		if (sizeof($_gn_cms)) {
			$languages = Language::getLanguages();
			$dir       = GN_INSTALL_PATH . 'cms/'; //format: cms_ID.html
            
			foreach ($_gn_cms as $type => $trans) {
				$file = $dir . 'cms_' . $type . '.html';
                
				if (file_exists($file)) {
					$cms = new CMS();
                   
					foreach ($languages as $language) {
						$id_lang = $language['id_lang'];
						$is_de = ($language['iso_code'] == 'de') ? true : false;
						
						$cms->meta_title[$id_lang]       = ($is_de) ? $trans['meta_title'] : $trans['name'];
						$cms->meta_description[$id_lang] = ($is_de) ? $trans['meta_description'] : $trans['name'];
						$cms->meta_keywords[$id_lang]    = ($is_de) ? $trans['meta_keywords']    : $trans['name'];
						$cms->link_rewrite[$id_lang]     = ($is_de) ? $trans['link_rewrite']     : $type;
						$cms->content[$id_lang]          = ($is_de) ? file_get_contents($file) : '';
                    			}
					
					$cms->active = 1;
					$cms->id_cms_category = 1;
                    
					if ($cms->add()) {
						Configuration::updateValue($trans['cms_id'], $cms->id);
					}
					else {
						$this->_errors[] = $this->l('Could not add a CMS page:') . ' "' . $trans['name'] . '"';
                        
						return false;
					}
				}
			}
            
			return true;
		}
        
		$this->_errors[] = $this->l('Unable to locate CMS pages to install');
        
		return false;
	}
    
    
	/*
	* Updates country names translation for German language.
	*
	* @access private
	*
	* @return void
	*/
	private function updateCountries() {
		// This is where the array with cms data is stored to keep this file
		// clean.
		require_once(GN_INSTALL_PATH . 'countries.inc.php');
        
		if (sizeof($_gn_countries)) {
			$id_lang_src  = Language::getIdByIso('en');
			$id_lang_dest = Language::getIdByIso('de');
			
			$countries = Db::getInstance()->ExecuteS('
				SELECT
					`id_country`,
					`name`
				FROM
					`' . _DB_PREFIX_ . 'country_lang`
				WHERE
					`id_lang` = ' . (int)$id_lang_src
			);
			
			if ($countries && sizeof($countries)) {
				foreach ($countries as $country) {
					if (array_key_exists($country['name'], $_gn_countries)) {
						Db::getInstance()->Execute('
							UPDATE
								`' . _DB_PREFIX_ . 'country_lang`
							SET
								`name` = "' . pSQL($_gn_countries[$country['name']]) . '"
							WHERE `id_lang` = ' . (int)$id_lang_dest . ' AND `id_country` = ' . (int)$country['id_country']
						);
					}
				}
			}
		}
	}
    
    
	/*
	* Disables all zones except Europe and all countries except Germany. This
	* is a germanext requirement.
	*
	* @access private
	*
	* @return void
	*/
	private function setCountryZone() {
		Db::getInstance()->Execute('UPDATE `' . _DB_PREFIX_ . 'zone` SET `active` = 1 WHERE `name` = "Europe"');
		
		Db::getInstance()->Execute('UPDATE `' . _DB_PREFIX_ . 'zone` SET `active` = 0 WHERE `name` <> "Europe"');
		
		Db::getInstance()->Execute('UPDATE `' . _DB_PREFIX_ . 'country` SET `active` = 1 WHERE `iso_code` = "DE"');
		
		Db::getInstance()->Execute('UPDATE `' . _DB_PREFIX_ . 'country` SET `active` = 0 WHERE `iso_code` <> "DE"'); 
	}
    
    
	/*
	* Updates or adds prestashop's default order states for use with Germanext.
	*
	* @access private
	*
	* @return void
	*/
	private function updateOrderStates() {
		$id_lang = Language::getIdByIso('de');
        
		if ( ! $id_lang || ! Validate::isUnsignedId($id_lang)) {
			$this->_errors[] = $this->l('Germanext requires German language to be installed');
            
			return false;
		}
        
		$orderStates = array(
			array(
				10, 'Information zu Ihrer Bestellung', 'bankwire'
			)
		);
       
		foreach ($orderStates as $key => $stateData) {
			$id       = $stateData[0];
			$name     = $stateData[1];
			$template = $stateData[2];
            
			if (Db::getInstance()->ExecuteS('SELECT 1 FROM `' . _DB_PREFIX_ . 'order_state_lang` WHERE `id_order_state` = ' . (int)$id . ' AND `id_lang` = ' . (int)$id_lang)) {
				$query = '
				UPDATE
					`' . _DB_PREFIX_ . 'order_state_lang`
				SET
					`name`     = "' . pSQL($name) . '",
					`template` = "' . pSQL($template) . '"
				WHERE
					`id_order_state` = ' . (int)$id . '
					AND
					`id_lang` = ' . (int)$id_lang . '
				LIMIT 1';
			}
			else {
				$query = '
				INSERT INTO
					`' . _DB_PREFIX_ . 'order_state_lang` (
						`id_order_state`,
						`id_lang`,
						`name`,
						`template`
					) VALUES (
						' . (int)$id . ',
						' . (int)$id_lang . ',
						"' . pSQL($name) . '",
						"' . pSQL($template) . '"
					)';
			}
            
			Db::getInstance()->Execute($query);
		}

		$orderStates = array();
		$orderStates[] = array(1, 'Warten aus Bestätigung');
		$orderStates[] = array(2, 'Warten auf Rücksendung');
		$orderStates[] = array(3, 'Sendung erhalten');
		$orderStates[] = array(4, 'Rücknahme abgelehnt');
		$orderStates[] = array(5, 'Rücknahme abgeschlossen');
       
		foreach ($orderStates as $key => $data) {
			$id = $data[0];
			$name = $data[1];
           
			if (Db::getInstance()->ExecuteS('SELECT 1 FROM `' . _DB_PREFIX_ . 'order_return_state_lang` WHERE `id_order_return_state` = ' . (int)$id . ' AND `id_lang` = ' . (int)$id_lang)) {
				$query = '
				    UPDATE
				        `' . _DB_PREFIX_ . 'order_return_state_lang`
				    SET
				        `name` = "' . pSQL($name) . '"
				    WHERE
				        `id_order_return_state` = ' . (int)$id . '
				    AND
				        `id_lang` = ' . (int)$id_lang . '
				    LIMIT 1';
			}
			else {
				$query = '
				INSERT INTO
				`' . _DB_PREFIX_ . 'order_return_state_lang` (
				    `id_order_return_state`,
				    `id_lang`,
				    `name`
				) VALUES (
				    ' . (int)$id . ',
				    ' . (int)$id_lang . ',
				    "' . pSQL($name) . '"
				)';
			}
            
			Db::getInstance()->Execute($query);
		}
	}
    
    
	/*
	* Alters default database structure by adding some fields to it that will
	* be used by Germanext later.
	*
	* @access private
	*
	* @return bool
	*/
	private function makeDbChanges() {
		// This is where the array with database sturture changes is stored to
		// keep this file clean.
		require_once(GN_INSTALL_PATH . 'db_alter.inc.php');

		if (sizeof($_gn_db_alter)) {
			self::cacheTableFields(array_keys($_gn_db_alter));

			foreach ($_gn_db_alter as $table => $alterData) {
				$table = _DB_PREFIX_ . $table;
                
				if ( ! is_array($alterData) || ! sizeof($alterData) || ! self::tableExists($table)) {
					continue;
				}
                
				foreach ($alterData as $field) {
					if ( ! array_key_exists('type', $field)) {
						continue;
					}
                    
					$query     = 'ALTER TABLE `' . pSQL($table) . '`';
					$fieldName = (array_key_exists('field', $field) && ! Tools::isEmpty($field['field']) && Validate::isTableOrIdentifier($field['field'])) ? $field['field'] : false;
					$data      = (array_key_exists('data', $field) && ! Tools::isEmpty($field['data'])) ? $field['data'] : false;
					$drop      = array_key_exists('drop', $field) && $field['drop'] === true;
					$after     = (array_key_exists('after', $field) && ! Tools::isEmpty($field['after']) && Validate::isTableOrIdentifier($field['after'])) ? $field['after'] : false;
                    
					switch ($field['type']) {
						case 'add':
							if ($fieldName && $data && ($drop || ! self::fieldExists($table, $fieldName))) {
								if ( ! $drop || self::alterDrop($table, $fieldName)) {
									$query.= ' ADD `' . pSQL($fieldName) .  '` ' . $data . ($after ? ' AFTER `' . pSQL($after) . '`' : '');
                                    
									if ( ! Db::getInstance()->Execute($query)) {
										$this->_errors[] = $this->l('Unable to alter table:') . ' "' . $table . '" ' . $this->l('to add a field') . ' "' . $fieldName . '"' . $query;
									}
								}
							}
						break;
						
						case 'change':
							if ($fieldName && $data && self::fieldExists($table, $fieldName)) {
								$query.= ' CHANGE `' . pSQL($fieldName) . '` ' . $data;
                                
								if ( ! Db::getInstance()->Execute($query)) {
									$this->_errors[] = $this->l('Unable to alter table:') . ' "' . $table . '" ' . $this->l('to change a field') . '"' . $fieldName . '"';
								}
							}
						break;
						
						case 'drop':
							if ($fieldName && self::fieldExists($table, $fieldName)) {
								self::alterDrop($table, $fieldName);
							}
						break;
					}
				}
			}
		}
        
		return ! sizeof($this->_errors);
	}
    
    
	/*
	* Installs Germanext's own database tables.
	*
	* @access private
	*
	* @return bool
	*/
	private function installTables() {
		require_once(GN_INSTALL_PATH . 'queries.inc.php');
        
		if (sizeof($_gn_queries)) {
			foreach ($_gn_queries as $table => $query) {
				$query = strtr($query, array('%PREFIX%' => _DB_PREFIX_, '%ENGINE%' => _MYSQL_ENGINE_));
                
				if ( ! Db::getInstance()->Execute($query)) {
					$this->_errors[] = $this->l('Unable to execute a query to create') . ' "' . $table . '" ' . $this->l('table');
				}
			}
		}
        
		return ! sizeof($this->_errors);
	}
    
    
	/*
	* Called during uninstall to drop Germanext tables.
	*
	* @access private
	*
	* @return void
	*/
	private function uninstallTables() {
		require_once(GN_INSTALL_PATH . 'queries.inc.php');
        
		if (sizeof($_gn_queries)) {
			foreach (array_keys($_gn_queries) as $table) {
				if ($table != 'base_unit' && self::tableExists(_DB_PREFIX_ . $table)) {
					Db::getInstance()->Execute('DROP TABLE `' . _DB_PREFIX_ . pSQL($table) . '`');
				}
			}
		}
	}
    
    
	/**************************************************************************
	 *                           DATABASE METHODS                             *
	 * Methods in this category deal with database - checking if tables and   *
	 * fields exist, dropping tables, etc.                                    *
	 *                                                                        *
	 * Search points: database, alter, SQL, exists                            *
	 **************************************************************************/
	/*
	* Checks whether a given table exists in the database
	*
	* @access private
	*
	* @scope static
	*
	* @param string $table - Table name
	*
	* @param bool $useCache - Whether or not to use cache. The recommended
	*                         value is "true", otherwise this method will
	*                         query database for tables everytime, even if
	*                         table names were already cached.
	*
	* @return bool
	*/
	private static function tableExists($table, $useCache = true) {
		if ( ! sizeof(self::$_tblCache) || ! $useCache) {
			$tmp = Db::getInstance()->ExecuteS('SHOW TABLES');
        
			foreach ($tmp as $entry) {
				reset($entry);
                
				$tableTmp = strtolower($entry[key($entry)]);
                
				if ( ! array_search($tableTmp, self::$_tblCache)) {
					self::$_tblCache[] = $tableTmp;
				}
			}
		}
        
		return array_search(strtolower($table), self::$_tblCache) ? true : false;
	}
	
	
	/*
	* Caches table fields during installation to speed it up, returns an array
	* of tables (only tables listed in db_alter.inc.php file are cached) as
	* keys with all the fields in them as values.
	*
	* @access private
	*
	* @scope static
	*
	* @param string $tables - Array of table names to cache
	*
	* @return void - results are assigned to $_fieldCache property.
	*/
	private static function cacheTableFields($tables) {
		if ( ! is_array($tables) || ! sizeof($tables)) {
			return false;
		}
		
		foreach ($tables as $table) {
			$table = _DB_PREFIX_ . $table;
			
			$fields = Db::getInstance()->ExecuteS('SHOW COLUMNS FROM `' . pSQL($table) . '`');
			
			if ($fields && sizeof($fields)) {
				foreach ($fields as $field) {
					$field_name = $field['Field'];
					
					if ( ! array_key_exists($table, self::$_fieldCache)) {
						self::$_fieldCache[$table] = array();
					}
					
					array_push(self::$_fieldCache[$table], $field_name);
				}
			}
		}
	}
    
    
	/*
	* Checks whether a given field exists in the table
	*
	* @access private
	*
	* @scope static
	*
	* @param string $table - Table name
	*
	* @param string $field - Field name
	*
	* @return bool
	*/
	private static function fieldExists($table, $field) {
		if ( ! self::tableExists($table)) {
			die(Tools::displayError('Table does not exist:') . ' ' . $table);
		}
		
		return (is_array(self::$_fieldCache) && array_key_exists($table, self::$_fieldCache) && array_search($field, self::$_fieldCache[$table]) !== false);
	}
    
    
	/*
	* Drops a field in a table. Does not drop the table itself.
	*
	* @access private
	*
	* @scope static
	*
	* @param string $table - Table name
	*
	* @param string $field - Field name
	*
	* @return bool
	*/
	private static function alterDrop($table, $field) {
		// Table check is performed in the fieldExists method, no need to check
		// here.
		if ( ! self::fieldExists($table, $field)) {
			return true;
		}
        
		if (Db::getInstance()->Execute('
			ALTER TABLE
				`' . pSQL($table) . '`
			DROP
				`' . pSQL($field) . '`'
		)) {
			if (is_array(self::$_fieldCache) && array_key_exists($table, self::$_fieldCache) && $key = array_search($field, self::$_fieldCache[$table])) {
				unset(self::$_fieldCache[$table][$key]);
			}
		}
        
		return true;
	}
	
	
	private function registerPaymentModules() {
		$existing_sql = Db::getInstance()->ExecuteS('SELECT * FROM `' . _DB_PREFIX_ . 'payment_cost`');
		$existing_modules = array();
		
		if ($existing_sql && sizeof($existing_sql)) {
			foreach ($existing_sql as $existing_module) {
				array_push($existing_modules, $existing_module['module']);
			}
		}
		
		$payment_dir = dirname(__FILE__) . '/payment/modules/';
		
		$modules = scandir($payment_dir);
		
		foreach ($modules as $module) {
			if (is_dir($payment_dir . $module) && ! in_array($module, array('.', '..')) && Validate::isModuleName($module) && ! in_array($module, $existing_modules)) {
				$id = self::getNextModuleId();
				
				$data = array(
					'id_payment' => (int)$id,
					'module'     => $module
				);
				
				Db::getInstance()->AutoExecute(_DB_PREFIX_ . 'payment_cost', $data, 'INSERT');
			}
		}
	}


	/**************************************************************************
	 *                             HOOK METHODS                               *
	 * These methods usually display something on one of the hooks that       *
	 * germanext is assigned to                                               *
	 *                                                                        *
	 * Search points: hook, registerhook, header, display                     *
	 **************************************************************************/
	/*
	* Executes early when loading a front-office page. Any content returned by
	* this method will be displayed in page's "<head>" tag.
	*
	* @access public
	*
	* @param array $params - Various prestashop parameters
	*
	* @return void
	*/
	public function hookHeader($params) {
		$is_mobile = $this->context->getMobileDevice();

		if ( ! method_exists($this->context, 'getMobileDevice') || ! $this->context->getMobileDevice()) {
			$this->context->controller->addCSS(_PS_CSS_DIR_.'jquery.fancybox-1.3.4.css', 'screen');
			$this->context->controller->addJqueryPlugin(array('fancybox'));
			$this->context->controller->addJs($this->_path . 'js/gn_tools.js');
		}

		$gn_configs = Configuration::getMultiple(array(
			'GN_FORCE_STAT_GATHER',
			'PS_PSTATISTIC',
			'PS_CONDITIONS',
			'PS_CMS_ID_CONDITIONS',
			'PS_CMS_ID_REVOCATION',
			'PS_PRIVACY',
			'PS_CMS_ID_PRIVACY',
			'PS_CMS_ID_DELIVERY',
			'PS_CMS_ID_IMPRINT',
			'GN_CHECK_PAYMENT'
		));
		
		$CMS_SHIPPING_LINK = self::getCmsLink((int)Configuration::get('PS_CMS_ID_DELIVERY'), (int)$this->context->language->id);
		
		$gn_configs['CMS_SHIPPING_LINK'] = $CMS_SHIPPING_LINK;
		$gn_configs['USTG'] = self::ustgInstalledAndActive();

		if (is_object($this->context)) {
			$this->context->controller->addCSS($this->_path . 'css/style.css', 'all');
			$this->context->smarty->assign('germanext_tpl', GN_THEME_PATH);
			$this->context->smarty->assign('germanext_tpl_mobile', GN_THEME_PATH . 'mobile/');
			$this->context->smarty->assign($gn_configs);
			// Check if we have a listener for this controller
			$contoller_class = get_class($this->context->controller);
			
			if ( ! Tools::isEmpty($contoller_class) && file_exists(LISTENERS_PATH . $contoller_class . ($is_mobile ? 'Mobile' : '') . 'Listener.php')) {
				$class_name = $contoller_class . ($is_mobile ? 'Mobile' : '') . 'Listener';
				
				require_once(LISTENERS_PATH . $class_name . '.php');
				
				if (class_exists($class_name, false)) {
					$listener = new $class_name();
					
					$listener->setGnRelativePath();
					
					return $listener->execute($this->context);
				}
			}
		}
	}
	
	protected function setGnRelativePath() {
		$this->_path = GN_REL_PATH;
	}
	
	
	/*
	* Initiated in <header> tag in Back Office.
	*
	* @access public
	*
	* @param array $params - Prestashop's parameters
	*
	* @return bool
	*/
	public function hookDisplayBackOfficeHeader($params) {
		if (is_object($this->context)) {
			// We might need those configs in smarty to use later in templates
			// in Back office (these are Germanext config variables, so they
			// aren't loaded by default)
			$gn_configs = Configuration::getMultiple(array(
				'GN_FORCE_STAT_GATHER',
				'PS_PSTATISTIC',
				'PS_ORDER_PROCESS_TYPE',
				'PS_CONDITIONS',
				'PS_CMS_ID_CONDITIONS',
				'PS_CMS_ID_REVOCATION',
				'PS_PRIVACY',
				'PS_CMS_ID_PRIVACY',
				'PS_CMS_ID_DELIVERY',
				'PS_CMS_ID_IMPRINT'
			));
			
			$gn_configs['USTG'] = self::ustgInstalledAndActive();
			
			$this->context->smarty->assign($gn_configs);
			
			// Germanext doesn't use the following code itself, but it might be
			// useful in the future: you can create a folder with a controller
			// name in modules/germanext/js/%CONTROLLER_NAME%/ and put js files
			// there - they will be appended to <header> on page load.
			$js_files = self::checkControllerJs(get_class($this->context->controller));

			if ($js_files && sizeof($js_files)) {
				foreach ($js_files as $js_file) {
					$this->context->controller->addJS($this->_path . $js_file);
				}
			}
		}
	}
	
	
	/**************************************************************************
	 *                       BACK END DISPLAY METHODS                         *
	 * These methods are used to display and process data in Germanext's      *
	 * back end.                                                              *
	 *                                                                        *
	 * Search points: display, bo, backoffice, process                        *
	 **************************************************************************/
	/*
	* Main display method for back office.
	*
	* @access public
	*
	* @return string - html content
	*/
	public function getContent() {
		$this->_html = '<h2>' . $this->displayName . '</h2>';
      
		$url = $this->getModuleLink();
		
		$this->_postProcess();
		
		$this->displayErrors();
		
		if ($m = Tools::getValue('m', false)) {
			switch ((int)$m) {
				case 1:
					$this->_html.= parent::displayConfirmation($this->l('Settings were updated successfully'));
					break;
			}
		}
		
		$languages = Language::getLanguages();
		$langDefault = (int)$this->context->language->id;
		
		$this->context->smarty->assign(array_merge(
			$this->getGnVars(),
			$this->getGnLangVars()
		));
      
		$this->context->smarty->assign(array(
			'GN_MAIL_CMS_TEXT' => array(
			   'PS_CMS_ID_CONDITIONS' => $this->l('Conditions of use terms and conditions CMS page'),
			   'PS_CMS_ID_REVOCATION' => $this->l('Conditions of use revocation CMS page'),
			),
			'GN_DISPLAY_NAME' => $this->displayName,
			'GN_REQUEST_URI'  => $url,
			'PS_IMG_PATH'     => _PS_IMG_,
			'PS_CSS_PATH'     => _PS_CSS_DIR_,
			'PS_JS_PATH'      => _PS_JS_DIR_,
			'GN_TOKEN'        => self::getPageToken('AdminCMSContent', (int)$this->context->employee->id),
			'GN_PATH'         => $this->_path,                             
			'languages'       => $languages,
			'defaultLang'     => $langDefault,
			'avNowFlags'      => $this->displayFlags($languages, $langDefault, 'available_now', 'available_now', true),
			'avLaterFlags'    => $this->displayFlags($languages, $langDefault, 'available_later', 'available_later', true),
			'regTextFlags'    => $this->displayFlags($languages, $langDefault, 'registration_text', 'registration_text', true),
			'base_units'      => self::getBaseUnits()
		));

		require_once(GN_PAYMENT_PATH . 'manager.php');
  
		GN_PaymentManager::getContentVars();
		
		$this->_html.= $this->context->smarty->fetch(GN_PATH . 'templates/gn_content.tpl');
		$this->_html.= $this->context->smarty->fetch(GN_PATH . 'templates/gn_base_units.tpl');
      
		return $this->_html;
	}
	
	
	/*
	* Get multilingual values from _POST superglobal.
	*
	* @access private
	*
	* @scope static
	*
	* @param string $postKey - post array key to look for
	*
	* @param mixed $fromArray - an optional array to use instead of _POST
	*
	* @return mixed - a found value or false
	*/
	private static function getLangIdFromPost($postKey, $fromArray = false) {
		$result = array();
	   
		$fromArray = $fromArray ? $fromArray : $_POST;
	   
		foreach ($fromArray as $key => $value) {
			if (self::stringStartsWith($key, $postKey)) {
				$tmp = explode('_', $key);
				
				$id_lang = (int)array_pop($tmp);
				
				if (self::isLanguageId($id_lang)) {
					$result[$id_lang] = $value;
				}
			}
		}
	   
		return sizeof($result) ? $result : false;
	}
	
	
	/*
	* Updates configuration vars listed in getContent method using values from
	* _POST superglobal.
	*
	* @access private
	*
	* @scope static
	*
	* @param array $data - array of values (_POST)
	*
	* @return void
	*/
	private static function updateLangConfigValues($data) {
		if ( ! is_array($data)) {
		   return false;
		}
	   
		$result = array();
	   
		foreach ($data as $configVar => $beginsWith) {
			if ($langData = self::getLangIdFromPost($beginsWith)) {
				if ( ! array_key_exists($configVar, $result)) {
					$result[$configVar] = $langData;
				}
			}
		}
	   
		if (sizeof($result)) {
			foreach ($result as $configVar => $langData) {
				Configuration::updateValue($configVar, $langData);
			}
		}
	}
	
	
	/*
	* Gets an id for next germanext payment module.
	*
	* @access private
	*
	* @scope static
	*
	* @return integer
	*/
	private static function getNextModuleId() {
		$currentId = (int)Db::getInstance()->getValue('
			SELECT
				MAX(`id_payment`)
			FROM
				`' . _DB_PREFIX_ . 'payment_cost`'
		);
	   
		return $currentId + 1;
	}
   
   
	/*
	* Tests if germanext module file exists
	*
	* @access private
	*
	* @scope static
	*
	* @param string $moduleName - module name to test
	*
	* @return boolean
	*/
	private static function checkNewModuleFiles($moduleName) {
		$dir = GN_PAYMENT_MODULES_PATH . $moduleName . '/';
		$file = $dir . $moduleName . '.php';
	   
		return (file_exists($dir) && is_dir($dir) && file_exists($file));
	}
	
	private function saveBaseUnits($units) {
		$new_units       = array();
		$existing_units  = array();
		$units_to_delete = array();
		$db_base_units   = self::getBaseUnits();
		
		foreach ($units as $unit) {
			$id_base_unit = (int)key($unit);
			$name         = $unit[$id_base_unit];

			if ( ! Tools::isEmpty($name)) {
				if ( ! Validate::isGenericName($name)) {
					$this->_postErrors[] = '"' . $name . '" ' . $this->l('is not a proper base unit');
				}
				else {
					if ($id_base_unit > 0) {
						$existing_units[$id_base_unit] = array(
							'id_base_unit' => $id_base_unit,
							'name'         => $name,
						);
					}
					else {
						array_push($new_units, array(
							'name'        => $name
						));
					}
				}
			}
		}
		
		if (sizeof($db_base_units)) {
			foreach ($db_base_units as $unit) {
				if ( ! sizeof($existing_units) || ! array_key_exists((int)$unit['id_base_unit'], $existing_units)) {
					$units_to_delete[] = (int)$unit['id_base_unit'];
				}
			}
		}
		
		if ( ! sizeof($this->_postErrors)) {
			if (sizeof($units_to_delete)) {
				Db::getInstance()->Execute('
					DELETE FROM 
						`' . _DB_PREFIX_ . 'base_unit`
					WHERE
						`id_base_unit` IN (' . implode(',', $units_to_delete) . ')'
				);
			}
			
			if (sizeof($new_units)) {
				foreach ($new_units as $new_unit) {
					Db::getInstance()->autoExecute(_DB_PREFIX_ . 'base_unit', $new_unit, 'INSERT');
				}
			}
			
			if (sizeof($existing_units)) {
				foreach ($existing_units as $existing_unit) {
					Db::getInstance()->autoExecute(_DB_PREFIX_ . 'base_unit', $existing_unit, 'UPDATE', '`id_base_unit` = ' . (int)$existing_unit['id_base_unit']);
				}
			}
			
			return true;
		}
		
		return false;
	}
	
	private static function checkPaymentModuleExists($name) {
		return Db::getInstance()->getValue('
			SELECT 
				`module` 
			FROM 
				`' . _DB_PREFIX_ . 'payment_cost` 
			WHERE 
				`module` = "' . pSQL($name) . '"'
		);
	}
	
	
	/*
	* Adds a new Germanext payment module
	*
	* @access private
	*
	* @param array $moduleData - module data provided in module's configuration
	*                            (name, impact, etc.)
	*
	* @return boolean
	*/
	private function addNewPaymentModule($moduleData) {
		$id = self::getNextModuleId();
		
		$name = array_key_exists('newGnModule_name', $moduleData) ? $moduleData['newGnModule_name'] : false;
	   
		if ( ! $name || trim($name) == '') {
		   return false;
		}
	   
		if ( ! self::checkNewModuleFiles($name)) {
		   $this->_postErrors[] = $this->l('Please add module files prior to registering it through germanext');
		   
		   return false;
		}
		
		if (self::checkPaymentModuleExists($name)) {
		   $this->_postErrors[] = $this->l('The module with this name has already been added');
		   
		   return false;
		}
 
		$paymentMessage = self::getLangIdFromPost('newGnModule_cost', $moduleData);
	
		$newModule = array(
			'id_payment'   => $id,
			'module'       => $name,
			'impact_dir'   => (int)$moduleData['newGnModule_impact'],
			'impact_type'  => (int)$moduleData['newGnModule_type'],
			'impact_value' => (float)$moduleData['newGnModule_value'],
			'active'       => 1
		);
	
		if ( ! sizeof($this->_postErrors)) {
			if ( ! Db::getInstance()->AutoExecute(_DB_PREFIX_ . 'payment_cost', $newModule, 'INSERT')) {
				$this->_postErrors[] = $this->l('Unable to add a new module');
				
				return false;
			}

			if ($paymentMessage) {
				foreach ($paymentMessage as $id_lang => $message) {
					$paymentData = array(
						'id_payment' => $id,
						'id_lang'    => $id_lang,
						'cost_name'  => $message
					);

					if ( ! Db::getInstance()->AutoExecute(_DB_PREFIX_ . 'payment_cost_lang', $paymentData, 'INSERT')) {
						$this->_postErrors[] = $this->l('Unable to add a message for module');
						
						return false;
					}
				}
			}
			
			return $id;
		}
		
		return false;
	}
	
	
	/*
	* Gets germanext configuration vars for use in getContent method.
	*
	* @access public
	*
	* @return array
	*/
	public function getGnVars() {
		return Configuration::getMultiple(array(
			'GN_MAIL_CMS',
			'PS_PRIVACY',
			'PS_PSTATISTIC',
			'GN_FORCE_STAT_GATHER',
			'GN_ALLOW_REORDER'
		));
	}
	
	
	/*
	* Gets multilingual germanext configuration vars.
	*
	* @access public
	*
	* @return array
	*/
	public function getGnLangVars() {
		$vars = array(
			'GN_AVAILABLE_NOW',
			'GN_AVAILABLE_LATER',
			'GN_REGISTRATION_TEXT'
		);
		
		$result = array();
		
		foreach ($vars as $var) {
			$result[$var] = Configuration::getInt($var);
		}
		
		return $result;
	}
	
	
	/*
	* Gets admin token for specified class. Used to get token for forms "action"
	* parameter.
	*
	* @access private
	*
	* @static
	*
	* @param string $classname - class name to get the token for, like
	*                            "AdminModules"
	*
	* @param integer $id_employee - employee id to get token for.
	*
	* @return string - token
	*/
	private static function getPageToken($classname, $id_employee) {
		return Tools::getAdminToken($classname . (int)Tab::getIdFromClassName($classname) . (int)$id_employee);
	}
    
    
	/*
	* Returns a module link, used for form actions, mostly.
	*
	* @access private
	*
	* @return string - module href
	*/
	private function getModuleLink() {
		$class = new ReflectionClass(get_class($this->context->controller));
		
		$currentIndex = $class->getStaticPropertyValue('currentIndex');

		return sprintf(
			'%s&configure=%s&token=%s&tab_module=%s&module_name=%s',
			$currentIndex,
			$this->name,
			self::getPageToken('AdminModules', (int)$this->context->employee->id),
			$this->tab,
			$this->name
		);
	}
	
	
	/*
	* Back office post-processing - this is where we save values provided in
	* getContent method.
	*
	* @access public
	*/
	public function _postProcess() {
		$url = $this->getModuleLink();
		
		if ( ! empty($_POST['GN_BTN_SAVE'])) {
			self::updateLangConfigValues(array(
				'GN_AVAILABLE_NOW'     => 'available_now',
				'GN_AVAILABLE_LATER'   => 'available_later',
				'GN_REGISTRATION_TEXT' => 'registration_text'
			));

			$GN_FORCE_STAT_GATHER = (int)Tools::getIsset('GN_FORCE_STAT_GATHER');
			$GN_ALLOW_REORDER     = (int)Tools::getIsset('GN_ALLOW_REORDER');
			$GN_MAIL_CMS          = Tools::getValue('mail_cms', false);

			Configuration::updateValue('GN_ALLOW_REORDER', $GN_ALLOW_REORDER);
			Configuration::updateValue('GN_FORCE_STAT_GATHER', $GN_FORCE_STAT_GATHER);
		   
			if ($GN_MAIL_CMS && Validate::isConfigName($GN_MAIL_CMS)) {
			   Configuration::updateValue('GN_MAIL_CMS', $GN_MAIL_CMS);
			}
		   
			require_once(GN_PAYMENT_PATH . 'manager.php');
		   
			$newModuleData = array();
		   
			foreach ($_POST as $key => $value) {
				if (self::stringStartsWith($key, 'newGnModule')) {
					$newModuleData[$key] = $value;
				}
			}
			
			$module_id = $this->addNewPaymentModule($newModuleData);
			
			GN_PaymentManager::postProcess($module_id);
		   
			if ( ! sizeof($this->_postErrors)) {
				Tools::redirectAdmin($url . '&m=1');
			}
			  
			return false;
		}
		elseif (Tools::isSubmit('saveBaseUnits')) {
			$base_units_post = Tools::getValue('base_units', false);
			
			if (is_array($base_units_post) && sizeof($base_units_post) && $this->saveBaseUnits($base_units_post)) {
				Tools::redirectAdmin($url . '&m=2');
			}
			
			return false;
		}
		
		return false;
	}
	
	
	/*
	 * Displays errors occured when saving settings in Back Office.
	 *
	 * @access private
	 *
	 * @return void
	 */
	private function displayErrors() {
		if (sizeof($this->_postErrors)) {
			foreach ($this->_postErrors as $error) {
				$this->_html.= parent::displayError($error);
			}
		}
	}
	
	
	/**************************************************************************
	 *                          FILE SYSTEM METHODS                           *
	 * The following methods deal with copying, creating and deleting files   *
	 * and directories                                                        *
	 *                                                                        *
	 * Search points: fs, filesystem                                          *
	 **************************************************************************/
	/*
	* Copies all the contents from directory $src to directory $dst.
	*
	* @access private
	*
	* @param string $src - Source directory
	*
	* @param string $dst - Destination directory
	*
	* @param mixed $list - A list of files to copy. If null, all files from
	*                      $src are copied.
	*
	* @param bool $delete_original - Whether or not to delete an original file
	*                                after it was copied to it's new
	*                                destination
	*
	* @return bool
	*/
	private function copyDir($src, $dst, $list = null, $delete_original = false) {
		$res = true;
		
		$src = rtrim(rtrim($src, '/'), '\\');
		$dst = rtrim(rtrim($dst, '/'), '\\');
		
		if (isset($list)) {
			$list = rtrim(rtrim($list, '/'), '\\');
		}
		
		if ( ! is_dir($dst) && ! mkdir($dst, 0755)) {
			$this->_errors[] = $this->l('Failed to create dir: '. $dst);
            
			return false;
		}
        
		if ( ! sizeof($this->_errors)) {
			if ($list == null) {
				$list = $src;
			}
         
			$objects = scandir($list);
            
			foreach ($objects as $obj) {
				if ( ! in_array($obj, array('.', '..', '.svn'))) {
					$ObjList = $list . '/' . ltrim(ltrim($obj, '/'), '\\');
					$ObjFrom = $src . '/' . ltrim(ltrim($obj, '/'), '\\');
					$ObjTo   = $dst . '/' . ltrim(ltrim($obj, '/'), '\\');
                    
					if (is_dir($ObjList)) {
						if (strcmp($obj, 'admin') == 0) {  
							if ($src == _PS_ROOT_DIR_) {
								$ObjFrom = PS_ADMIN_DIR;
							}
                            
							if ($dst == _PS_ROOT_DIR_) {
								$ObjTo   = PS_ADMIN_DIR;
							}
						}

						if ( ! is_dir($ObjTo) && ! mkdir($ObjTo, 0755)) {
							$this->_errors[] = $this->l('Failed to create dir: ' . $ObjTo);
							
							return false;
						}
                        
						$res &= $this->copyDir($ObjFrom, $ObjTo, $ObjList);
					}
					else {
						if (strcmp($ObjTo, 'themes/default') !== 0) {
							$ObjTo = str_replace('themes/default', 'themes/' . _THEME_NAME_, $ObjTo);
						}

						if (file_exists($ObjTo)) {
							$chmod = (int)substr(decoct(fileperms($ObjTo)), -3);
                            
							if ($chmod < 755) {
								chmod($ObjTo, 0755);
							}
						}
                  
						if (file_exists($ObjFrom)) {
							if ( ! copy($ObjFrom, $ObjTo)) {
								$this->_errors[] = $this->l('Failed to copy file: ' . $ObjFrom . ' -> ' . $ObjTo);
								
								$res = false;
							}  
							else {
								if ($delete_original) {
									@unlink($ObjFrom);
								}
								
								$chmod = (int)substr(decoct(fileperms($ObjTo)), -3);
								
								if ($chmod < 755) {
									chmod($ObjTo, 0755);  
								}
							}
						}
					}
				}
			}
            
			return $res;
		}
	}
	
	
	/*
	* Recursively deletes a directory. Optionally, can delete only files,
	* leaving the directory structure.
	*
	* @access private
	*
	* @param string $dir     - Directory to delete
	*
	* @param bool $filesOnly - If true, only files are deleted.
	*
	* @return bool
	*/
	private function deleteDir($dir, $filesOnly = false) {   
		if (is_dir($dir)) {
			$objects = scandir($dir);
            
			foreach ($objects as $object) {
				if ( ! in_array($object, array('.', '..', '.svn'))) {
					$path = implode('/', array($dir, $object));
                    
					if (is_dir($path)) {
						$this->deleteDir($path, $filesOnly);
					}
					else if ( ! unlink($path)) {
						return false;   
					}
				}
			}
		 
			reset($objects);
	  
			if ( ! $filesOnly) {
				rmdir($dir);
			}
		}
	  
		return true;
	}

	
	/**************************************************************************
	 *                            HELPER METHODS                              *
	 * These are helpers and miscellaneous methods required by germanext      *
	 *                                                                        *
	 * Search points: helper, miscellaneous, misc, others                     *
	 **************************************************************************/
	public function removeJs($context, $js) {
		foreach ($context->controller->js_files as $i => $file) {
			if (strstr($file, $js)) {
				unset($context->controller->js_files[$i]);
			}
		}
	}
	
	
	private static function ustgInstalledAndActive() {
		return (Module::isInstalled('smallscaleenterprise') && (int)Configuration::get('USTG_ACTIVE') == 1);
	}
	
	/*
	* Substitutes a default template with germanext template based on the given
	* controller name. See overrides/classes/controller/Controller.php,
	* "setTemplate" method for more details.
	*
	* @access public
	*
	* @scope static
	*
	* @param string $controller - Controller name
	*
	* @param string $template - Template name
	*
	* @return bool
	*/
	public static function getBaseUnits() {
		$prepared = array();
		$base_units = Db::getInstance()->ExecuteS('
			SELECT
				*
			FROM
				`' . _DB_PREFIX_ . 'base_unit`'
		);
		
		if ($base_units && sizeof($base_units)) {
			foreach ($base_units as $base_unit) {
				$prepared[$base_unit['id_base_unit']] = $base_unit;
			}
			
			return $prepared;
		}
		
		return false;
	}
	
	public static function getBaseUnitById($id) {
		return Db::getInstance()->getValue('
			SELECT
				`name`
			FROM
				`' . _DB_PREFIX_ . 'base_unit`
			WHERE
				`id_base_unit` = ' . (int)$id
		);
	}
	
	public static function getTemplateByController($controller, $template, $theme = false, $is_admin = false) {
		if ($is_admin) {
			$theme_path = GN_THEMES_PATH . 'admin/' . $theme . '/' . $controller . '/';
		}
		else {
			$theme_path = GN_THEME_PATH;
		}

		if (file_exists($theme_path) && array_search($controller, self::$_templateOverrides) !== false) {
			if (strpos($template, '/') !== false) {
				$template = substr(strrchr($template, '/'), 1);
			}

			if ($template) {
				$template_path = str_replace(array('\\', '/'), DIRECTORY_SEPARATOR, $theme_path . $template);

				if (file_exists($template_path)) {
					return ! $is_admin ? $template_path : strstr((substr($template_path, 0, strlen(strrchr($template_path, '/')) * -1) . '/'), 'modules/germanext/');
				}
			}
		}
        
		return false;
	}
    
    
	/*
	* Substitutes smarty templates with germanext templates. See
	* /tools/smarty/sysplugins/smarty_internal_compile_include.php file,
	* method "compile"
	*
	* @access public
	*
	* @scope static
	*
	* @param string $template_name - Smarty template (note that it's a
	*                                special string)
	*
	* @return string
	*/
	public static function getThemeTemplate($template_name) {
		$is_mobile      = Context::getContext()->getMobileDevice();
		$gn_path        = GN_THEME_PATH . ($is_mobile ? 'mobile/' : '');
		$gn_smarty_path = 'germanext_tpl' . ($is_mobile ? '_mobile' : '');

		if (is_array(self::$_themeTplOverrides)) {
			foreach (self::$_themeTplOverrides as $template) {
				if (stristr($template_name, $template) && file_exists($gn_path . $template)) {
					$template_name = '($_smarty_tpl->tpl_vars[\'' . $gn_smarty_path . '\']->value)."./' . $template . '"';
				}
			}
		}
		
		return $template_name;
	}
	
	
	/*
	* Tests if a string begins with substring.
	*
	* @access public
	*
	* @scope static
	*
	* @param string $haystack - initial string
	*
	* @param string $needle - substring to look for
	*
	* @return boolean
	*/
	public static function stringStartsWith($haystack, $needle) {
		$length = strlen($needle);
	   
		return (substr($haystack, 0, $length) === $needle);
	}
	
	
	/*
	* Tests if $id_lang is a real existing language id.
	*
	* @access public
	*
	* @scope static
	*
	* @param integer $id_lang - language id
	*
	* @return boolean
	*/
	public static function isLanguageId($id_lang) {
		if ( ! is_array(self::$_languages)) {
			self::$_languages = Language::getLanguages();
		}
	   
		foreach (self::$_languages as $language) {
			if ($id_lang == $language['id_lang']) {
				return true;
			}
		}
	   
		return false;
	}
	
	
	/*
	* Tries to find js files associated with a given controller in
	* modules/germanext/js folder. Stores found files in the array that it
	* returns
	*
	* @access public
	*
	* @scope static
	*
	* @param string $controller - Controller name
	*
	* @return mixed
	*/
	public static function checkControllerJs($controller) {
		$path        = GN_PATH . 'js/admin/';
		$detected_js = array();

		if ($controller && file_exists($path . $controller) && is_dir($path . $controller)) {
			$js_files = scandir($path . $controller);
			
			if ($js_files && sizeof($js_files)) {
				foreach ($js_files as $js_file) {
					if (strtolower(substr(strrchr($js_file, '.'), 1)) == 'js') {
						$detected_js[] = 'js/admin/' . $controller . '/' . $js_file;
					}
				}
			}
		}
		
		return sizeof($detected_js) ? $detected_js : false;
	}
	
	
	/*
	* Tests whether germanext newsletter module is installed and is active,
	* returns 0 or 1 for inactive (or uninstalled) and active state. 
	*
	* @access public
	*
	* @scope static
	*
	* @return integer
	*/
	public static function getGernamextNewsLetterState() {
		if ( ! Validate::isLoadedObject($newsletter = Module::getInstanceByName('blocknewslettergermanext'))) {
			return 0;
		}
		
		return (int)$newsletter->active;
	}
	
	
	/*
	* Gets a link to a CMS page. We could use native "Link" class, but it does
	* not take "content_only" into account
	*
	* @access public
	*
	* @scope static
	*
	* @param integer $id_cms - CMS ID
	*
	* @param mixed $id_lang  - Language ID (optional)
	*
	* @param bool  $ssl      - Whether or not the returned link should use SSL
	*                          protocol
	*
	* @return string
	*/
	public static function getCmsLink($id_cms, $id_lang = false, $ssl = false) {
		if ( ! Validate::isUnsignedId($id_cms)) {
			return false;
		}
		
		$rewrite = (int)Configuration::get('PS_REWRITING_SETTINGS');
		
		if ( ! $id_lang) {
			if (is_object($this->context)) {
				$id_lang = (int)$this->context->language->id;
			}
		}
		
		$cms = new CMS((int)$id_cms, (int)$id_lang);
		
		if (Validate::isLoadedObject($cms)) {
			$base = (($ssl && Configuration::get('PS_SSL_ENABLED')) ? Tools::getShopDomainSsl(true) : Tools::getShopDomain(true));
			
			$link = $base . __PS_BASE_URI__;
			
			if ($rewrite && Language::isMultiLanguageActivated()) {
				$link.= Language::getIsoById($id_lang) . '/';
			}
			
			$params = array(
				'id'            => $cms->id,
				'rewrite'       => $cms->link_rewrite,
				'meta_keywords' => empty($cms->meta_keywords) ? '' : Tools::str2url($cms->meta_keywords),
				'meta_title'    => empty($cms->meta_title) ? '' : Tools::str2url($cms->meta_title),
				'content_only'  => 1
			);
			
			return $link . Dispatcher::getInstance()->createUrl('cms_rule', $id_lang, $params);
		}
		
		return false;
	}
	
	/**************************************************************************
	 *                             MAIL METHODS                               *
	 * The following methods deal with mails that are sent by Prestastore     *
	 **************************************************************************/
	/*
	* Prepares mail parameters before the mail is sent. A $params array contains
	* default values passed by prestastore and can be overriden.
	*
	* @access public
	*
	* @scope static
	*
	* @param array $params - Mail parameters, passed by reference
	*
	* @return void
	*/
	public static function prepareMailSend(&$params) {
		$context = Context::getContext();
		$id_lang = (int)($params['id_lang']);
		$iso = Language::getIsoById($id_lang);
		$path = dirname(__FILE__) . '/mails/';
		$template = $params['template'];
		$tplPath = $path . $iso . '/' . $template;
		
		if ($params['templatePath'] == _PS_MAIL_DIR_ && file_exists($tplPath . '.txt') && file_exists($tplPath . '.html')) {
			$arrayConf = array(
				'PS_SHOP_NAME',
				'PS_SHOP_COMPANY',
				'PS_SHOP_ADDR1',
				'PS_SHOP_ADDR2',
				'PS_SHOP_CODE',
				'PS_SHOP_CITY',
				'PS_SHOP_STATE',
				'PS_SHOP_COUNTRY',
				'PS_SHOP_PHONE',
				'PS_SHOP_FAX',
				'PS_SHOP_EMAIL',
				'PS_SHOP_REPRESENTER',
				'PS_SHOP_REGISTER_COURT',
				'PS_SHOP_REGISTER_NUM',
				'PS_SHOP_SALES_TAX_ID',
				'PS_SHOP_BANK_NAME',
				'PS_SHOP_BANK_ACCOUNT',
				'PS_SHOP_BANK_CODE',
				'PS_SHOP_BANK_IBAN',
				'PS_SHOP_BANK_SWIFT',
				'PS_SHOP_DETAILS'
			);
			
			$conf = Configuration::getMultiple($arrayConf);
			
			$footer_vars = array();
			
			foreach ($arrayConf as $conf_name) {
				$footer_vars[$conf_name] = (array_key_exists($conf_name, $conf) && ! Tools::isEmpty($conf[$conf_name])) ? $conf[$conf_name] : '';
			}
			
			$context->smarty->assign($footer_vars);
			
			$file = $path . $iso . '/footer_html.tpl';
			$params['templateVars']['{mail_footer_html}'] = (file_exists($file)) ? $context->smarty->fetch($file) : '';
			
			$file = $path . $iso . '/footer_txt.tpl';
			$params['templateVars']['{mail_footer_txt}']  = (file_exists($file)) ? $context->smarty->fetch($file) : '';
			
			$agbCmsKey = Configuration::get('GN_MAIL_CMS');
			
			if ( ! $agbCmsKey) {
				$params['templateVars']['{cms_content}'] = '';
			}
			else {
				$agb_cms = new CMS((int)(Configuration::get($agbCmsKey))); 
				$params['templateVars']['{cms_content}'] = ($agb_cms) ? $agb_cms->content[$id_lang] : '';
			}

			$params['templatePath'] = $path;
		}
	}
	
	public function getPdfTranslation($string) {
		if ( ! sizeof(self::$_pdfTranslations)) {
			self::$_pdfTranslations = array(
				'Company' => $this->l('Company'),
				'Address' => $this->l('Address'),
				'Post/Zip code' => $this->l('Post/Zip code'),
				'City' => $this->l('City'),
				'State' => $this->l('State'),
				'Country' => $this->l('Country'),
				'Phone' => $this->l('Phone'),
				'Fax' => $this->l('Fax'),
				'Email' => $this->l('Email'),
				'Authorised representative' => $this->l('Authorised representative'),
				'Register court' => $this->l('Register court'),
				'Register number' => $this->l('Register number'),
				'Sales tax ID number' => $this->l('Sales tax ID number'),
				'Bank name' => $this->l('Bank name'),
				'Account number' => $this->l('Account number'),
				'Bank identifier code' => $this->l('Bank identifier code'),
				'IBAN' => $this->l('IBAN'),
				'SWIFT' => $this->l('SWIFT')
			);
		}
		
		return array_key_exists($string, self::$_pdfTranslations) ? self::$_pdfTranslations[$string] : $string;
	}
}
