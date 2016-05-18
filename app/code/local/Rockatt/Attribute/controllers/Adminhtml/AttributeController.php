<?php
/**
 * @category   Manage Customer Attribute
 * @package    Rockatt_Attribute
 * @copyright  Copyright (c) 2016 Rocktechnolabs (http://www.rocktechnolabs.com)
 * @author     Vishal Lakhani <vishal.lakhani@yahoo.co.in>
 */
?>
<?php
class Rockatt_Attribute_Adminhtml_AttributeController extends Mage_Adminhtml_Controller_Action
{
	protected $_entityTypeId;

    public function preDispatch()
    {
        parent::preDispatch();
        $this->_entityTypeId = Mage::getModel('eav/entity')->setType(Mage::getModel('eav/config')->getEntityType('customer'))->getTypeId();
    }
	
	public function indexAction()
	{
		$this->_initAction()
            ->_addContent($this->getLayout()->createBlock('attribute/adminhtml_attribute'))
            ->renderLayout();
	}
	
	protected function _initAction()
    {
        $this->_title($this->__('Rockatt'))
             ->_title($this->__('Attributes'))
             ->_title($this->__('Manage Attributes'));

        if($this->getRequest()->getParam('popup')) {
            $this->loadLayout('popup');
        } else {
            $this->loadLayout()
                ->_setActiveMenu('customer/attribute')
                ->_addBreadcrumb('Attribute', 'Attribute')
                ->_addBreadcrumb(
                    'Manage Customer Attributes',
                    'Manage Customer Attributes');
        }
        return $this;
    }
	
	public function newAction()
    {
        $this->_forward('edit');
    }
	
	public function editAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('attribute/attribute')->setEntityTypeId($this->_entityTypeId);
		
		Mage::register('customer_attributedata', $model);
        if ($id) {
            $model->load($id);

            if (! $model->getId()) {
                Mage::getSingleton('adminhtml/session')->addError(
                    'This attribute no longer exists');
                $this->_redirect('*/*/');
                return;
            }

            // entity type check
            if ($model->getEntityTypeId() != $this->_entityTypeId) {
                Mage::getSingleton('adminhtml/session')->addError(
                    'This attribute cannot be edited.');
                $this->_redirect('*/*/');
                return;
            }
        }

        // set entered data if was error when we do save
        $data = Mage::getSingleton('adminhtml/session')->getAttributeData(true);
		
        if (! empty($data)) {
            $model->addData($data);
        }
		
        $this->_initAction();

        $this->_title($id ? $model->getName() : $this->__('New Attribute'));

        $item = $id ? 'Edit Product Attribute'
                    : 'New Product Attribute';

        $this->_addBreadcrumb($item, $item);
	
		$this->_addContent($this->getLayout()->createBlock('attribute/adminhtml_attribute_edit'))
                ->_addLeft($this->getLayout()->createBlock('attribute/adminhtml_attribute_edit_tabs'));

        $this->renderLayout();

    }
	
	public function validateAction()
    {
        $response = new Varien_Object();
        $response->setError(false);

        $attributeCode  = $this->getRequest()->getParam('attribute_code');
        $attributeId    = $this->getRequest()->getParam('id');
        $attribute = Mage::getModel('attribute/attribute')
            ->loadByCode('attribute', $attributeCode);

        if ($attribute->getId() && !$attributeId) {
            Mage::getSingleton('adminhtml/session')->addError(
                'Attribute with the same code already exists');
            $this->_initLayoutMessages('adminhtml/session');
            $response->setError(true);
            $response->setMessage($this->getLayout()->getMessagesBlock()->getGroupedHtml());
        }

        $this->getResponse()->setBody($response->toJson());
    }
	
	protected function _filterPostData($data)
    {
        if ($data) {
            /** @var $helperCatalog Mage_Catalog_Helper_Data */
            $helperCatalog = Mage::helper('attribute');
            //labels
            foreach ($data['frontend_label'] as & $value) {
                if ($value) {
                    $value = $helperCatalog->stripTags($value);
                }
            }

            if (!empty($data['option']) && !empty($data['option']['value']) && is_array($data['option']['value'])) {
                foreach ($data['option']['value'] as $key => $values) {
                    $data['option']['value'][$key] = array_map(array($helperCatalog, 'stripTags'), $values);
                }
            }
        }
        return $data;
    }
	
	public function saveAction()
	{
		if ($data = $this->getRequest()->getPost())
        {
			//validate attribute code
			if(!$this->getRequest()->getParam('id'))
			{
				$d=Mage::getModel('eav/entity_attribute')->getCollection()->addFieldToFilter('attribute_code', $data["attribute_code"]);
				
				if($d->getData())
				{
					Mage::getSingleton('adminhtml/session')->addError("Attribute code is already exists try again");
					$this->_redirect('*/*/new');
					return;
				}
			}
			
			//customer attribute ---------------------------------------------
			
			if($data['frontend_input']=='yesno')
			{
				$data['frontend_input']="select";
				$data['option']=array(
							array(
								'value' => 0,
								'label' => Mage::helper('catalog')->__('No')
							),
							array(
								'value' => 1,
								'label' => Mage::helper('catalog')->__('Yes')
							));
			}
			
			if($data['frontend_input']=='label')
			{
				$attr_type="static";
			}
			else
			{
				$attr_type="text";
			}
			
			Mage::app();
			$installer = new Mage_Customer_Model_Entity_Setup('core_setup');

			$installer->startSetup();

			$vCustomerEntityType = $installer->getEntityTypeId('customer');
			$vCustAttributeSetId = $installer->getDefaultAttributeSetId($vCustomerEntityType);
			$vCustAttributeGroupId = $installer->getDefaultAttributeGroupId($vCustomerEntityType, $vCustAttributeSetId);

			$installer->addAttribute('customer', $data['attribute_code'], array(
				"type"=>$attr_type,
				"backend_model"=>"eav/entity_attribute_backend_array",
				"input"=>$data['frontend_input'],
				"label"=>$data['frontend_label'][0],
				"global"=>Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
				"visible"=>1,
				"required"=>$data['is_required'],
				"user_defined"=>1,
				"is_unique"=>$data["is_unique"],
				"default"=>$data['default_value'],
				"visible_on_front"=>1,
				"source"=>'eav/entity_attribute_source_table',
				'forms' => array('customer_account_edit','customer_account_create','adminhtml_customer','checkout_register'),
				'option' =>$data['option'],
			));

			$installer->addAttributeToGroup($vCustomerEntityType, $vCustAttributeSetId, $vCustAttributeGroupId, $data['attribute_code'], 0);

			$sore_view=implode(",",$data["store_view"]);
			
			$oAttribute = Mage::getSingleton('eav/config')->getAttribute('customer', $data['attribute_code']);
			$oAttribute->setData('used_in_forms', array('customer_account_edit','customer_account_create','adminhtml_customer','checkout_register'));
			$oAttribute->setData('store_view', $sore_view);
			$oAttribute->setData('show_on_customer_grid_new', $data["show_on_customer_grid"]);
			$oAttribute->setData('show_on_order_grid', $data["show_on_order_grid"]);
			$oAttribute->setData('show_on_order_view_page', $data["show_on_order_view_page"]);
			$oAttribute->setData('show_on_acc_info_page', $data["show_on_acc_info_page"]);
			$oAttribute->setData('show_on_billing_page', $data["show_on_billing_page"]);
			$oAttribute->setData('default_value', $data['default_value']);
			$oAttribute->setData('show_on_reg_page', $data["show_on_reg_page"]);
			$oAttribute->setData('sort_order', $data["sort_order"]);
			$oAttribute->setStoreLabels($data["frontend_label"]);
			$oAttribute->save();

			$installer->endSetup();
			//customer attribute -----------------------------------------------------
			
			if($data["show_on_order_view_page"]==1 || $data["show_on_order_grid"]==1 || $data["show_on_billing_page"]==1)
			{
				$installer1 = new Mage_Eav_Model_Entity_Setup('core_setup');

				$installer1->startSetup();
				$installer1->getConnection()->addColumn($installer1->getTable('sales_flat_order'), $data['attribute_code'], Varien_Db_Ddl_Table::TYPE_TEXT);
				$installer1->endSetup();
			}
			
            $model = Mage::getModel('attribute/attribute');
            $id = $this->getRequest()->getParam('id');
			
            foreach ($data as $key => $value)
            {
                if (is_array($value))
                {
                        $data[$key] = implode(',',$this->getRequest()->getParam($key));
                }
            }
			
            $model->setData($data);
 
            Mage::getSingleton('adminhtml/session')->setFormData($data);
			
            try {
                if ($id) {
                    $model->setAttributeId($id);
                }
				
				if(!$model->getAttributeId())
				{
					$attr = Mage::getModel('eav/entity_attribute')->getCollection()->addFieldToFilter('attribute_code', $model->getAttributeCode());
					$attr_id=$attr->getData();
					
					$model->setAttributeId($attr_id[0]['attribute_id']);
				}
				
                if (!$model->getAttributeId()) {
                    Mage::throwException(Mage::helper('attribute')->__('Error saving customer attribute details'));
                }
 
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('attribute')->__('Customer attribute was successfully saved.'));
 
                Mage::getSingleton('adminhtml/session')->setFormData(false);
				
                // The following line decides if it is a "save" or "save and continue"
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getAttributeId()));
                } else {
                    $this->_redirect('*/*/');
                }
 
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                if ($model && $model->getAttributeId()) {
                    $this->_redirect('*/*/edit', array('id' => $model->getAttributeId()));
                } else {
                    $this->_redirect('*/*/');
                }
            }
 
          return;
		}
	}
	
	public function deleteAction() {
        if ($this->getRequest()->getParam('id') > 0) {
            
			$data=Mage::getModel('attribute/attribute')->load($this->getRequest()->getParam('id'));
			$data=$data->getData();
			
			//customer attribute------------------------------------
			Mage::app();
			$installer = new Mage_Customer_Model_Entity_Setup('core_setup');

			$installer->startSetup();
			
			$installer->removeAttribute('customer', $data['attribute_code']);
			$installer->endSetup();
			
			$installer->endSetup();
			//customer attribute------------------------------------
			
			$installer1 = new Mage_Eav_Model_Entity_Setup('core_setup');

			$installer1->startSetup();
			$installer1->getConnection()->dropColumn($installer1->getTable('sales_flat_order'), $data['attribute_code']);
			$installer1->endSetup();
			
			try {
                $model = Mage::getModel('attribute/attribute');
 
                $model->setId($this->getRequest()->getParam('id'))
                        ->delete();
 
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Customer attribute was successfully deleted'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }
}
?>