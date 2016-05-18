<?php
/**
 * @category   Manage Customer Attribute
 * @package    Rockatt_Attribute
 * @copyright  Copyright (c) 2016 Rocktechnolabs (http://www.rocktechnolabs.com)
 * @author     Vishal Lakhani <vishal.lakhani@yahoo.co.in>
 */
?>
<?php
class Rockatt_Attribute_Block_Attribute extends Mage_Core_Block_Template
{
	public function _construct()
	{	
		
	}
	
	public function getAttribute()
	{
		return "attribute";
	}
	
	public function getAttributeForReg()
	{
		$array=array();
		$attr_col=Mage::getModel('attribute/attribute')->getCollection();
		$attr_col->getSelect()->order('sort_order','ASC');
		$attr_data=$attr_col->getData();
		
		foreach($attr_data as $value)
		{
			if($value["show_on_reg_page"]==1)
			{
				$array[]=$value;
			}
		}
		
		return $array;
	}
	
	public function getAttributeForAccInfo()
	{
		$array=array();
		$attr_col=Mage::getModel('attribute/attribute')->getCollection();
		$attr_col->getSelect()->order('sort_order','ASC');
		$attr_data=$attr_col->getData();
		
		foreach($attr_data as $value)
		{
			if($value["show_on_acc_info_page"]==1)
			{
				$array[]=$value;
			}
		}
		
		return $array;
	}
	
	public function getAttributeForBilling()
	{
		$array=array();
		$attr_col=Mage::getModel('attribute/attribute')->getCollection();
		$attr_col->getSelect()->order('sort_order','ASC');
		$attr_data=$attr_col->getData();
		
		foreach($attr_data as $value)
		{
			if($value["show_on_billing_page"]==1)
			{
				$array[]=$value;
			}
		}
		
		return $array;
	}
	
	public function getAttributeForViewOrder()
	{
		$array=array();
		$attr_col=Mage::getModel('attribute/attribute')->getCollection();
		$attr_col->getSelect()->order('sort_order','ASC');
		$attr_data=$attr_col->getData();
		
		foreach($attr_data as $value)
		{
			if($value["show_on_order_view_page"]==1)
			{
				$array[]=$value;
			}
		}
		
		return $array;
	}
	
	public function getAttributeOption($attr_code)
	{
		$attributeModel = Mage::getModel('eav/entity_attribute')->loadByCode(1, $attr_code);
		$attributeOptions = $attributeModel->getSource()->getAllOptions();
		
		return $attributeOptions;
	}
	
	public function getAttributeValue($attr_code)
	{
		$customer = Mage::getSingleton('customer/session')->getCustomer();
		$data=$customer->getData();
		
		$customerData = Mage::getModel('customer/customer')->load($data["entity_id"])->getData($attr_code);
		
		return $customerData;
	}
	
	public function getImageValue($attr_code)
	{
		
	}
	
	public function getOrderAttributeData($order_id,$att_code)
	{
		$attr_data=Mage::getModel("sales/order")->load($order_id);
		$data=$attr_data->getData();
		return $data[$att_code];
	}
	
	public function isShowExtraInfoTab($form_name)
	{
		$array=array();
		$attr_col=Mage::getModel('attribute/attribute')->getCollection();
		$attr_col->getSelect()->order('sort_order','ASC');
		$attr_data=$attr_col->getData();
		
		foreach($attr_data as $value)
		{
			if($value[$form_name]==1)
			{
				$array[]=$value;
			}
		}
		
		foreach($array as $arr)
		{
			return true;
		}
		
		return false;
	}
}
?>