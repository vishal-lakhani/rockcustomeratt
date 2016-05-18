<?php
/**
 * @category   Manage Customer Attribute
 * @package    Rockatt_Attribute
 * @copyright  Copyright (c) 2016 Rocktechnolabs (http://www.rocktechnolabs.com)
 * @author     Vishal Lakhani <vishal.lakhani@yahoo.co.in>
 */
?>
<?php
$installer=$this;

$installer->startSetup();

$installer->run("
DROP TABLE IF EXISTS {$this->getTable('attribute')};
CREATE TABLE {$this->getTable('attribute')}(
	id int(11) unsigned NOT NULL auto_increment,
	store_view varchar(70) NOT NULL,
	frontend_input varchar(50) NOT NULL,
	default_value varchar(250) NOT NULL,
	is_required varchar(3) NOT NULL,
	show_on_customer_grid varchar(3) NOT NULL,
	show_on_order_grid varchar(3) NOT NULL,
	show_on_order_view_page varchar(3) NOT NULL,
	show_on_acc_info_page varchar(3) NOT NULL,
	show_on_billing_page varchar(3) NOT NULL,
	show_on_reg_page varchar(3) NOT NULL,
	sort_order int(11),
	attribute_label varchar(250),
	option_value varchar(500) NOT NULL,
	PRIMARY KEY(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->endSetup();

Mage::app();
$installer = new Mage_Customer_Model_Entity_Setup('core_setup');

$installer->startSetup();

$installer->getConnection()->addColumn(
    'customer_eav_attribute',
    'store_view',
    array(
        'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'  => 3,
        'comment' => 'customer custom column'
    )
);

$installer->getConnection()->addColumn(
    'customer_eav_attribute',
    'show_on_customer_grid_new',
    array(
        'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'  => 3,
        'comment' => 'customer custom column'
    )
);

$installer->getConnection()->addColumn(
    'customer_eav_attribute',
    'show_on_order_grid',
    array(
        'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'  => 3,
        'comment' => 'customer custom column'
    )
);

$installer->getConnection()->addColumn(
    'customer_eav_attribute',
    'show_on_order_view_page',
    array(
        'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'  => 3,
        'comment' => 'customer custom column'
    )
);

$installer->getConnection()->addColumn(
    'customer_eav_attribute',
    'show_on_acc_info_page',
    array(
        'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'  => 3,
        'comment' => 'customer custom column'
    )
);

$installer->getConnection()->addColumn(
    'customer_eav_attribute',
    'show_on_billing_page',
    array(
        'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'  => 3,
        'comment' => 'customer custom column'
    )
);

$installer->getConnection()->addColumn(
    'customer_eav_attribute',
    'show_on_reg_page',
    array(
        'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'  => 3,
        'comment' => 'customer custom column'
    )
);

$installer->getConnection()->addColumn(
    'customer_eav_attribute',
    'show_on_reg_page',
    array(
        'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'  => 3,
        'comment' => 'customer custom column'
    )
);

$installer->endSetup();
?>