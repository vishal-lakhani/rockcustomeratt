<?php
/**
 * @category   Manage Customer Attribute
 * @package    Rockatt_Attribute
 * @copyright  Copyright (c) 2016 Rocktechnolabs (http://www.rocktechnolabs.com)
 * @author     Vishal Lakhani <vishal.lakhani@yahoo.co.in>
 */
?>
<?php
class Rockatt_Attribute_Block_Adminhtml_Attribute extends Mage_Adminhtml_Block_Widget_Grid_Container
{
	public function __construct()
	{
        $this->_controller = 'adminhtml_attribute';
        $this->_blockGroup = 'attribute';
        $this->_headerText = Mage::helper('attribute')->__('Customer Attribute Manager');
        parent::__construct();
    }
}
?>