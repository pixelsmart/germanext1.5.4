<?php
$_gn_queries = array(
    'payment_cost' =>
        'CREATE TABLE IF NOT EXISTS `%PREFIX%payment_cost` (
            `id_payment` INT( 10 ) NOT NULL ,
            `module` VARCHAR( 100 ) NOT NULL ,
            `impact_dir` TINYINT( 1 ) NOT NULL DEFAULT \'0\',
            `impact_type` TINYINT( 1 ) NOT NULL DEFAULT \'0\',
            `impact_value` DECIMAL ( 17,2 ) NOT NULL DEFAULT \'0.00\',
            `active` TINYINT( 1 ) NOT NULL DEFAULT \'1\'
        ) ENGINE=%ENGINE% DEFAULT CHARSET=utf8;',
        
    'payment_cost_lang' => 
        'CREATE TABLE IF NOT EXISTS `%PREFIX%payment_cost_lang` (
            `id_payment` INT(10) NOT NULL,
            `id_lang` INT(10) NOT NULL,
            `cost_name` TEXT
        ) ENGINE=%ENGINE% DEFAULT CHARSET=utf8;',
        
    'base_unit' =>
        'CREATE TABLE IF NOT EXISTS `%PREFIX%base_unit` (
            `id_base_unit` INT(10) unsigned NOT NULL AUTO_INCREMENT,
            PRIMARY KEY (`id_base_unit`)
        )  ENGINE=%ENGINE% DEFAULT CHARSET=utf8;',
	
    'base_unit_lang' => 
        'CREATE TABLE IF NOT EXISTS `%PREFIX%base_unit_lang` (
            `id_base_unit` INT(10) NOT NULL,
            `id_lang` INT(10) NOT NULL,
            `name` VARCHAR(12) NOT NULL
        ) ENGINE=%ENGINE% DEFAULT CHARSET=utf8;',
);
