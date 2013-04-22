<?php
/*
* 2007-2013 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2013 PrestaShop SA
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

// Include some alias functions
require_once(dirname(__FILE__).'/alias.php');

$gn_config_file = dirname(__FILE__) . '/../modules/germanext/config.xml';
$gn_installed = false;

if (file_exists($gn_config_file) && $xml_conf = simplexml_load_file($gn_config_file)) {
	$is_installed = (int)$xml_conf->is_installed;
	$is_active    = (int)$xml_conf->is_enabled;
    
	if ($is_installed && $is_active) {
		$gn_installed = true;
	}
}

if ($gn_installed && file_exists(dirname(__FILE__).'/../classes/GNAutoload.php')) {
	require_once(dirname(__FILE__).'/../classes/GNAutoload.php');
    
	spl_autoload_register(array(GNAutoload::getInstance(), 'load'));
} else {
	require_once(dirname(__FILE__).'/../classes/Autoload.php');
    
	spl_autoload_register(array(Autoload::getInstance(), 'load'));
}
