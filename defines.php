<?php
define('GN_PATH',                   _PS_MODULE_DIR_ . 'germanext/');
define('LISTENERS_PATH',            GN_PATH . 'listeners/');
define('GN_INSTALL_PATH',           GN_PATH . 'install/');
define('GN_INSTALL_COPY_PATH',      GN_INSTALL_PATH . 'copy/');
define('GN_BACKUP_PATH',            GN_PATH . 'backup/');
define('GN_PAYMENT_PATH',           GN_PATH . 'payment/');
define('GN_PDF_PATH',               GN_PATH . 'pdf/');
define('GN_PAYMENT_MODULES_PATH',   GN_PAYMENT_PATH . 'modules/');
define('GN_THEMES_PATH',            GN_PATH . 'themes/');
define('GN_REL_PATH',               _MODULE_DIR_ . 'germanext/');
define('GN_REL_THEMES_PATH',        GN_REL_PATH . 'themes/');

if ( ! defined('GN_THEME_PATH')) {
	if (file_exists(GN_THEMES_PATH . _THEME_NAME_ . '/')) {
		define('GN_THEME_PATH', GN_THEMES_PATH . _THEME_NAME_ . '/');
		define('GN_REL_THEME_PATH', GN_REL_THEMES_PATH . _THEME_NAME_ . '/');
	}
	elseif (file_exists(GN_THEMES_PATH . 'default/')) {
		define('GN_THEME_PATH', GN_THEMES_PATH . 'default/');
		define('GN_REL_THEME_PATH', GN_REL_THEMES_PATH . 'default/');
	}
	else {
		define('GN_THEME_PATH', _PS_ALL_THEMES_DIR_ . _THEME_NAME_ . '/');
		define('GN_REL_THEME_PATH', _THEME_DIR_);
	}
}
