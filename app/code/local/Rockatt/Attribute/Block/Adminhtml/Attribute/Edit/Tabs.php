<?php
/**
 * @category   Manage Customer Attribute
 * @package    Rockatt_Attribute
 * @copyright  Copyright (c) 2016 Rocktechnolabs (http://www.rocktechnolabs.com)
 * @author     Vishal Lakhani <vishal.lakhani@yahoo.co.in>
 */
?>
<?php
class Rockatt_Attribute_Block_Adminhtml_Attribute_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
	public function __construct()
    {
        parent::__construct();
        $this->setId('attribute_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('attribute')->__('Attribute Information'));
	}
	
	protected function _beforeToHtml()
    {
        $this->addTab('main', array(
            'label'     => 'Properties',
            'title'     => 'Properties',
            'content'   => $this->getLayout()->createBlock('attribute/adminhtml_attribute_edit_tab_main')->toHtml(),
            'active'    => true
        ));
		
		//$model = Mage::registry('entity_attribute');
		$model=Mage::registry('customer_attributedata');	

        $this->addTab('labels', array(
            'label'     => 'Manage Label / Options',
            'title'     => 'Manage Label / Options',
            'content'   => $this->getLayout()->createBlock('attribute/adminhtml_attribute_edit_tab_options')->toHtml(),
        ));
        
        /*if ('select' == $model->getFrontendInput()) {
            $this->addTab('options_section', array(
                'label'     => Mage::helper('catalog')->__('Options Control'),
                'title'     => Mage::helper('catalog')->__('Options Control'),
                'content'   => $this->getLayout()->createBlock('adminhtml/catalog_product_attribute_edit_tab_options')->toHtml(),
            ));
        }*/

        return parent::_beforeToHtml();
    }
}
?>