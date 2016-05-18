<?php
/**
 * @category   Manage Customer Attribute
 * @package    Rockatt_Attribute
 * @copyright  Copyright (c) 2016 Rocktechnolabs (http://www.rocktechnolabs.com)
 * @author     Vishal Lakhani <vishal.lakhani@yahoo.co.in>
 */
?>
<?php
class Rockatt_Attribute_Block_Adminhtml_Js extends Mage_Adminhtml_Block_Template
{
	public function __construct()
    {
        parent::__construct();
        $this->setTemplate('attribute/js.phtml');
    }
}
?>