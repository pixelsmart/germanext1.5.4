<?php
$_gn_db_alter = array(
    'product' => array(
        array(
            'type'  => 'drop',
            'field' => 'reduction_reason'
        ),
        array(
            'type'  => 'add',
            'field' => 'unit_net',
            'data'  => 'DECIMAL(20, 3) NOT NULL DEFAULT \'0.000\'',
            'after' => 'unit_price_ratio',
            'drop'  => false
        )
    ),
    'product_lang' => array(
        array(
            'type'  => 'add',
            'field' => 'reduction_reason',
            'data'  => 'VARCHAR(128) CHARACTER SET utf8 COLLATE utf8_general_ci NULL',
            'after' => 'name',
            'drop'  => true
        )
    ),
    'product_attribute' => array(
        array(
            'type'  => 'add',
            'field' => 'unit_net_impact',
            'data'  => 'DECIMAL(20, 3) NOT NULL DEFAULT \'0.000\'',
            'after' => 'unit_price_impact',
            'drop'  => false
        )
    ),
    'product_attribute_shop' => array(
        array(
            'type'  => 'add',
            'field' => 'unit_net_impact',
            'data'  => 'DECIMAL(20, 3) NOT NULL DEFAULT \'0.000\'',
            'after' => 'unit_price_impact',
            'drop'  => false
        )
    ),
    'attribute_impact' => array(
        array(
            'type'  => 'add',
            'field' => 'net',
            'data'  => 'DECIMAL(20, 3) NOT NULL DEFAULT \'0.000\'',
            'after' => 'weight',
            'drop'  => false
        )
    ),
    'cart' => array(
        array(
            'type'  => 'add',
            'field' => 'id_payment',
            'data'  => 'INT(10) NOT NULL DEFAULT \'0\'',
            'after' => 'id_currency',
            'drop'  => true
        )
    ),
    'orders' => array(
        array(
            'type'  => 'add',
            'field' => 'total_payment_tax_excl',
            'data'  => 'DECIMAL(17, 2) NOT NULL DEFAULT \'0.00\'',
            'after' => 'module',
            'drop'  => true
        ),
        array(
            'type'  => 'add',
            'field' => 'total_payment_tax_incl',
            'data'  => 'DECIMAL(17, 2) NOT NULL DEFAULT \'0.00\'',
            'after' => 'total_payment_tax_excl',
            'drop'  => true
        ),
        array(
            'type'  => 'add',
            'field' => 'payment_message',
            'data'  => 'VARCHAR(128) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL',
            'after' => 'total_payment_tax_incl',
            'drop'  => true
        )
    ),
    'order_payment' => array(
        array(
            'type'  => 'add',
            'field' => 'total_payment_tax_excl',
            'data'  => 'DECIMAL(17, 2) NOT NULL DEFAULT \'0.00\'',
            'after' => 'payment_method',
            'drop'  => true
        ),
        array(
            'type'  => 'add',
            'field' => 'total_payment_tax_incl',
            'data'  => 'DECIMAL(17, 2) NOT NULL DEFAULT \'0.00\'',
            'after' => 'total_payment_tax_excl',
            'drop'  => true
        ),
        array(
            'type'  => 'add',
            'field' => 'payment_message',
            'data'  => 'VARCHAR(128) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL',
            'after' => 'total_payment_tax_incl',
            'drop'  => true
        )
    ),
    'customer' => array(
        array(
            'type'  => 'add',
            'field' => 'statistic',
            'data'  => 'TINYINT(1) UNSIGNED NOT NULL DEFAULT \'0\'',
            'after' => 'newsletter',
            'drop'  => false
        ),
        array(
            'type'  => 'change',
            'field' => 'email',
            'data'  => '`email` VARCHAR(128) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL'
        )
    )
);
