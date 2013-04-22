<?php
class AdminCartsController extends AdminCartsControllerCore
{
    public function __construct()
    {
	$this->table = 'cart';
	$this->className = 'Cart';
	$this->lang = false;
	$this->explicitSelect = true;

	$this->addRowAction('view');
	$this->addRowAction('delete');
	$this->allow_export = true;
        
        $forceStatistics = (int)Configuration::get('GN_FORCE_STAT_GATHER') == 1;

	$on_customer = '(c.`id_customer` = a.`id_customer`';
        
	if ($forceStatistics || Configuration::get('PS_PSTATISTIC')) {
			$on_customer.= ( ! $forceStatistics ? ' AND c.`statistic` = 1' : '');
        }
        
        $on_customer.= ')';

	$this->_select = 'CONCAT(LEFT(c.`firstname`, 1), \'. \', c.`lastname`) `customer`, a.id_cart total, ca.name carrier, o.id_order, IF(co.id_guest, 1, 0) id_guest';
	$this->_join = '
        LEFT JOIN '._DB_PREFIX_.'customer c ON ' . $on_customer . '
	LEFT JOIN '._DB_PREFIX_.'currency cu ON (cu.id_currency = a.id_currency)
	LEFT JOIN '._DB_PREFIX_.'carrier ca ON (ca.id_carrier = a.id_carrier)
	LEFT JOIN '._DB_PREFIX_.'orders o ON (o.id_cart = a.id_cart)
	LEFT JOIN `'._DB_PREFIX_.'connections` co ON (a.id_guest = co.id_guest AND TIME_TO_SEC(TIMEDIFF(NOW(), co.`date_add`)) < 1800)';

	$this->fields_list = array(
	    'id_cart' => array(
		    'title' => $this->l('ID'),
		    'align' => 'center',
		    'width' => 25
	    ),
	    'id_order' => array(
		    'title' => $this->l('Order ID'),
		    'align' => 'center', 'width' => 25
	    ),
	    'customer' => array(
		    'title' => $this->l('Customer'),
		    'width' => 'auto',
		    'filter_key' => 'c!lastname'
	    ),
	    'total' => array(
		    'title' => $this->l('Total'),
		    'callback' => 'getOrderTotalUsingTaxCalculationMethod',
		    'orderby' => false,
		    'search' => false,
		    'width' => 80,
		    'align' => 'right',
		    'prefix' => '<b>',
		    'suffix' => '</b>',
	    ),
	    'carrier' => array(
		    'title' => $this->l('Carrier'),
		    'width' => 50,
		    'align' => 'center',
		    'callback' => 'replaceZeroByShopName',
		    'filter_key' => 'ca!name'
	    ),
	    'date_add' => array(
		    'title' => $this->l('Date'),
		    'width' => 150,
		    'align' => 'right',
		    'type' => 'datetime',
		    'filter_key' => 'a!date_add'
	    ),
	    'id_guest' => array(
		    'title' => $this->l('Online'),
		    'width' => 40,
		    'align' => 'center',
		    'type' => 'bool',
		    'havingFilter' => true,
		    'icon' => array(0 => 'blank.gif', 1 => 'tab-customers.gif')
	    )
	);
	$this->shopLinkType = 'shop';

	AdminController::__construct();
    }
}
