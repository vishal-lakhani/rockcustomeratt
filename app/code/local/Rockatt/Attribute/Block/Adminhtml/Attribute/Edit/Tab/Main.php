<?php
/**
 * @category   Manage Customer Attribute
 * @package    Rockatt_Attribute
 * @copyright  Copyright (c) 2016 Rocktechnolabs (http://www.rocktechnolabs.com)
 * @author     Vishal Lakhani <vishal.lakhani@yahoo.co.in>
 */
?>
<?php
class Rockatt_Attribute_Block_Adminhtml_Attribute_Edit_Tab_Main extends Mage_Eav_Block_Adminhtml_Attribute_Edit_Main_Abstract
{
	
	protected function _prepareForm()
	{
		parent::_prepareForm();
		$id=$this->getRequest()->getParam('id');
		$data=Mage::getModel('attribute/attribute')->load($id);
		$attributeObject = $this->getAttributeObject();
 
        $form = new Varien_Data_Form();
        $this->setForm($form);
		
        $fieldset = $form->addFieldset('attribute_attribute', array('legend' => Mage::helper('attribute')->__('Attribute Properties'),'expanded'=>true));
 
		$yesno = array(
            array(
                'value' => 0,
                'label' => Mage::helper('catalog')->__('No')
            ),
            array(
                'value' => 1,
                'label' => Mage::helper('catalog')->__('Yes')
            ));
		
		if($id)
		{
			$field_disabled=true;
			$fieldset->addField('attribute_code', 'text', array(
			  'label'     => Mage::helper('attribute')->__('Attribute Code'),
			  'class'     => 'required-entry',
			  'required'  => true,
			  'readonly'=>true,
			  'name'      => 'attribute_code',
			  'note'=>'For internal use. Must be unique with no spaces',
			));
		}
		else
		{
			$field_disabled=false;
			$fieldset->addField('attribute_code', 'text', array(
			  'label'     => Mage::helper('attribute')->__('Attribute Code'),
			  'class'     => 'required-entry',
			  'required'  => true,
			  'name'      => 'attribute_code',
			  'note'=>'For internal use. Must be unique with no spaces',
			));
		}
		
		 $field =$fieldset->addField('store_view', 'multiselect', array(
            'name'      => 'store_view[]',
            'label'     => Mage::helper('attribute')->__('Store View'),
            'title'     => Mage::helper('attribute')->__('Store View'),
            'required'  => true,
            'values'    => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true),
        ));
        $renderer = $this->getLayout()->createBlock('adminhtml/store_switcher_form_renderer_fieldset_element');
        $field->setRenderer($renderer);
		
		$fieldset->addField('frontend_input', 'select', array(
            'name' => 'frontend_input',
            'label' => Mage::helper('attribute')->__('Catalog Input Type for Store Owner'),
            'title' => Mage::helper('attribute')->__('Catalog Input Type for Store Owner'),
			'onchange'=>'modifyTargetElement(this)',
            'options' => array(
                'text'=> Mage::helper('attribute')->__('Text Field'),
                'textarea'=> Mage::helper('attribute')->__('Text Area'),
                'date'=> Mage::helper('attribute')->__('Date'),
                'boolean'=> Mage::helper('attribute')->__('Yes/No'),
                'multiselect'=> Mage::helper('attribute')->__('Multiple Select'),
                'select'=> Mage::helper('attribute')->__('Dropdown'),
				'file'=> Mage::helper('attribute')->__('Single File Upload'),
				'image'=> Mage::helper('attribute')->__('Image'),
            ),
        ));
		
		$fieldset->addField('default_value', 'text', array(
          'label'     => Mage::helper('attribute')->__('Default Value'),
          'name'      => 'default_value',
        ));
		
		$fieldset->addField('is_unique', 'select', array(
            'name' => 'is_unique',
            'label' => Mage::helper('attribute')->__('Unique Value'),
            'title' => Mage::helper('attribute')->__('Unique Value'),
            'values' =>$yesno,
			'note'=>'Not shared with other customers',
        ));
		
		$fieldset->addField('is_required', 'select', array(
            'name' => 'is_required',
            'label' => Mage::helper('attribute')->__('Value Required'),
            'title' => Mage::helper('attribute')->__('value Required'),
            'values' =>$yesno,
        ));
		
		$fieldset->addField('is_readonly', 'select', array(
            'name' => 'is_readonly',
            'label' => Mage::helper('attribute')->__('Is Read Only'),
            'title' => Mage::helper('attribute')->__('Is Read Only'),
            'values' =>$yesno,
        ));
		
		$this->setChild('form_after', $this->getLayout()
		->createBlock('adminhtml/widget_form_element_dependence')
		->addFieldMap('frontend_input', 'frontend_input')
        ->addFieldMap('default_value', 'default_value')
		->addFieldMap('is_unique', 'is_unique')
		->addFieldMap('is_readonly', 'is_readonly')
        ->addFieldDependence('default_value', 'frontend_input', array('text','textarea','date','label'))
		->addFieldDependence('is_unique', 'frontend_input', array('text','textarea','date','label'))
		->addFieldDependence('is_readonly', 'frontend_input', array('text','textarea','date','label')));
		
		$fieldset1 = $form->addFieldset('attribute_attribute1', array('legend' => Mage::helper('attribute')->__('Attribute Configuration')));
		
		$fieldset1->addField('show_on_customer_grid_new', 'select', array(
            'name' => 'show_on_customer_grid',
            'label' => Mage::helper('attribute')->__('Show On Customer Grid'),
            'title' => Mage::helper('attribute')->__('Show On Customer Grid'),
            'values' =>$yesno,
			'note'=>'Set it yes to show attribute on customer grid',
        ));
		
		$fieldset1->addField('show_on_order_grid', 'select', array(
            'name' => 'show_on_order_grid',
            'label' => Mage::helper('attribute')->__('Show On Order Grid'),
            'title' => Mage::helper('attribute')->__('Show On Order Grid'),
            'values' =>$yesno,
			'note'=>'Set it to yes for show attribute on order grid',
        ));
		
		$fieldset1->addField('show_on_order_view_page', 'select', array(
            'name' => 'show_on_order_view_page',
            'label' => Mage::helper('attribute')->__('Show On Order View Page'),
            'title' => Mage::helper('attribute')->__('Show On Order View Page'),
            'values' =>$yesno,
			'note'=>'In the Account Information block at the Backend',
        ));
		
		$fieldset1->addField('show_on_acc_info_page', 'select', array(
            'name' => 'show_on_acc_info_page',
            'label' => Mage::helper('attribute')->__('Show On Account Information Page'),
            'title' => Mage::helper('attribute')->__('Show On Account Infromation Page'),
            'values' =>$yesno,
			'note'=>'Set it yes to show attribute on the Frontend',
        ));
		
		$fieldset1->addField('show_on_billing_page', 'select', array(
            'name' => 'show_on_billing_page',
            'label' => Mage::helper('attribute')->__('Show On Billing Page'),
            'title' => Mage::helper('attribute')->__('Show On Billing Page'),
            'values' =>$yesno,
			'note'=>'Set it yes to show attribute During Checkout',
        ));
		
		$fieldset1->addField('show_on_reg_page', 'select', array(
            'name' => 'show_on_reg_page',
            'label' => Mage::helper('attribute')->__('Show On Customer Registration Page'),
            'title' => Mage::helper('attribute')->__('Show On Customer Registration Page'),
            'values' =>$yesno,
			'note'=>'Set it yes to show attribute During customer registration',
        ));
		
		$fieldset1->addField('sort_order', 'text', array(
            'name' => 'sort_order',
            'label' => Mage::helper('attribute')->__('Sorting Order'),
            'title' => Mage::helper('attribute')->__('Sorting Order'),
			'note'=>'The order to display field on frontend',
        ));
		
        $form->setValues($data);
    }
	
	public function getAttributeObject()
    {
        return Mage::registry('customer_attributedata');
    }
}
?>