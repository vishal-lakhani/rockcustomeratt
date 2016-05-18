<?php
/**
 * @category   Manage Customer Attribute
 * @package    Rockatt_Attribute
 * @copyright  Copyright (c) 2016 Rocktechnolabs (http://www.rocktechnolabs.com)
 * @author     Rocktechnolabs <info@rocktechnolabs.com>
 */
?>
<?php
class Rockatt_Attribute_Block_Adminhtml_Attribute_Renderer_Customerfield extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	public function render(Varien_Object $row)
	{
		$value=$row->getData($this->getColumn()->getIndex());
		$data=Mage::getModel('customer/customer')->load($row->getId());
		$data=$data->getData();
		
		$attributeModel = Mage::getModel('eav/entity_attribute')->loadByCode(1, $this->getColumn()->getIndex());
		
		if($attributeModel->getFrontendInput()=="multiselect" || $attributeModel->getFrontendInput()=="select")
		{
			$attributeOptions = $attributeModel->getSource()->getAllOptions();
			
			$arr=explode(",",$data[$this->getColumn()->getIndex()]);
			$values_arr=array();
			
			foreach($arr as $value)
			{
				foreach ($attributeOptions as $option)
				{
					if($option['value']==$value)
					{
						$values_arr[]=$option['label'];
					}
				}
			}
			
			return implode(",",$values_arr);
		}
		else if($attributeModel->getFrontendInput()=="boolean")
		{
			if($data[$this->getColumn()->getIndex()])
			{
				return "yes";
			}
			else
			{
				return "no";
			}
		}
		else if($attributeModel->getFrontendInput()=="image")
		{
			$path=Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA)."customer".$data[$this->getColumn()->getIndex()];
			
			return "<img src='".$path."' height='50px' width='50px' />";
		}
		else
		{
			return $data[$this->getColumn()->getIndex()];
		}
	}
}
?>