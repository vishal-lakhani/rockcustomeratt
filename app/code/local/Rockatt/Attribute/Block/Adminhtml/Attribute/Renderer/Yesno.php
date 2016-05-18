<?php
/**
 * @category   Manage Customer Attribute
 * @package    Rockatt_Attribute
 * @copyright  Copyright (c) 2016 Rocktechnolabs (http://www.rocktechnolabs.com)
 * @author     Rocktechnolabs <info@rocktechnolabs.com>
 */
?>
<?php
class Rockatt_Attribute_Block_Adminhtml_Attribute_Renderer_Yesno extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	public function render(Varien_Object $row)
	{
		$value=$row->getData($this->getColumn()->getIndex());
		
		if($value>0)
		{
			return "Yes";
		}
		else
		{
			return "No";
		}
	}
}
?>