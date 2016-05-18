<?php
/**
 * @category   Manage Customer Attribute
 * @package    Rockatt_Attribute
 * @copyright  Copyright (c) 2016 Rocktechnolabs (http://www.rocktechnolabs.com)
 * @author     Vishal Lakhani <vishal.lakhani@yahoo.co.in>
 */
?>
<?php
class Rockatt_Attribute_Block_Adminhtml_Attribute_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
	public function __construct()
    {
        parent::__construct();
		
		$this->_objectId = 'attribute_id';
        $this->_blockGroup = 'attribute';
        $this->_controller = 'adminhtml_attribute';

		$this->_addButton('saveandcontinue', array(
            'label' => Mage::helper('attribute')->__('Save And Continue Edit'),
            'onclick' => 'saveAndContinueEdit()',
            'class' => 'save',
                ), -100);
		
        $this->_updateButton('save', 'label', 'Save Attribute');

        if (! $this->getRequest()->getParam('id')) {
            $this->_removeButton('delete');
        } else {
            $this->_updateButton('delete', 'label', 'Delete Attribute');
        }
		
		 $this->_formScripts[] = "
            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if (Mage::registry('customer_attributedata')->getId()) {
            $frontendLabel = Mage::registry('customer_attributedata')->getFrontendLabel();
            if (is_array($frontendLabel)) {
                $frontendLabel = $frontendLabel[0];
            }
            return 'Edit Customer Attribute '.$this->escapeHtml($frontendLabel);
        }
        else {
            return 'New Customer Attribute';
        }
    }

    public function getSaveUrl()
    {
        return $this->getUrl('*/'.$this->_blockGroup.'/save', array('_current'=>true, 'back'=>null));
    }
}
?>