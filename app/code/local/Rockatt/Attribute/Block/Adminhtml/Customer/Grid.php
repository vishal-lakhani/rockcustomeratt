<?php
/**
 * @category   Manage Customer Attribute
 * @package    Rockatt_Attribute
 * @copyright  Copyright (c) 2016 Rocktechnolabs (http://www.rocktechnolabs.com)
 * @author     Vishal Lakhani <vishal.lakhani@yahoo.co.in>
 */
?>
<?php
class Rockatt_Attribute_Block_Adminhtml_Customer_Grid extends Mage_Adminhtml_Block_Customer_Grid
{
	public function __construct()
    {
        parent::__construct();
        $this->setId('customerGrid');
        $this->setUseAjax(true);
        $this->setDefaultSort('entity_id');
        $this->setSaveParametersInSession(true);
    }
	
	protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('customer/customer_collection')
            ->addNameToSelect()
            ->addAttributeToSelect('email')
			->addAttributeToSelect('new1')
            ->addAttributeToSelect('created_at')
            ->addAttributeToSelect('group_id')
            ->joinAttribute('billing_postcode', 'customer_address/postcode', 'default_billing', null, 'left')
            ->joinAttribute('billing_city', 'customer_address/city', 'default_billing', null, 'left')
            ->joinAttribute('billing_telephone', 'customer_address/telephone', 'default_billing', null, 'left')
            ->joinAttribute('billing_region', 'customer_address/region', 'default_billing', null, 'left')
            ->joinAttribute('billing_country_id', 'customer_address/country_id', 'default_billing', null, 'left');
			
		$attr_col=Mage::getModel('attribute/attribute')->getCollection();
		$attr_data=$attr_col->getData();
		$entityType = Mage::getModel('eav/entity_type')->loadByCode(1);
		$entityTable = $collection->getTable($entityType->getEntityTable());
		
		$index=1;
		
		foreach($attr_data as $value)
		{
			$alias='table'.$index;
			
			if($value["show_on_customer_grid_new"]==1)
			{
				$customerAttrtbl = Mage::getSingleton('customer/customer')->getResource()->getAttribute($value["attribute_code"]);
				$attr_table=$customerAttrtbl->getBackend()->getTable();
				$table=$entityTable."".$attr_table;
				$field=$alias.".value";
				
				
				$collection->getSelect()
					->joinLeft(
						array($alias => $table),
						'e.entity_id ='.$alias.'.entity_id AND '.$alias.'.attribute_id = '.$customerAttrtbl->getId(). ' AND '.$alias.'.entity_type_id = '.Mage::getSingleton('customer/customer')->getResource()->getTypeId(),
						array($value["attribute_code"]=>$field)
					);
			}
			
			$index++;
		}

        $this->setCollection($collection);
		
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('entity_id', array(
            'header'    => Mage::helper('customer')->__('ID'),
            'width'     => '50px',
            'index'     => 'entity_id',
            'type'  => 'number',
        ));
		
        $this->addColumn('name', array(
            'header'    => Mage::helper('customer')->__('Name'),
            'index'     => 'name'
        ));
        $this->addColumn('email', array(
            'header'    => Mage::helper('customer')->__('Email'),
            'width'     => '150',
            'index'     => 'email'
        ));
		
		$attr_col=Mage::getModel('attribute/attribute')->getCollection();
		$attr_data=$attr_col->getData();
		
		foreach($attr_data as $value)
		{
			if($value["show_on_customer_grid_new"]==1 && $value["frontend_input"]!="image")
			{
				$this->addColumn($value['attribute_code'], array(
					'header'    => Mage::helper('attribute')->__($value['attribute_code']),
					'width'     => '',
					'index'     => $value['attribute_code'],
					'renderer'=>'Rockatt_Attribute_Block_Adminhtml_Attribute_Renderer_Customerfield',
				));
			}
		}

        $groups = Mage::getResourceModel('customer/group_collection')
            ->addFieldToFilter('customer_group_id', array('gt'=> 0))
            ->load()
            ->toOptionHash();

        $this->addColumn('group', array(
            'header'    =>  Mage::helper('customer')->__('Group'),
            'width'     =>  '100',
            'index'     =>  'group_id',
            'type'      =>  'options',
            'options'   =>  $groups,
        ));

        $this->addColumn('Telephone', array(
            'header'    => Mage::helper('customer')->__('Telephone'),
            'width'     => '100',
            'index'     => 'billing_telephone'
        ));

        $this->addColumn('billing_postcode', array(
            'header'    => Mage::helper('customer')->__('ZIP'),
            'width'     => '90',
            'index'     => 'billing_postcode',
        ));

        $this->addColumn('billing_country_id', array(
            'header'    => Mage::helper('customer')->__('Country'),
            'width'     => '100',
            'type'      => 'country',
            'index'     => 'billing_country_id',
        ));

        $this->addColumn('billing_region', array(
            'header'    => Mage::helper('customer')->__('State/Province'),
            'width'     => '100',
            'index'     => 'billing_region',
        ));

        $this->addColumn('customer_since', array(
            'header'    => Mage::helper('customer')->__('Customer Since'),
            'type'      => 'datetime',
            'align'     => 'center',
            'index'     => 'created_at',
            'gmtoffset' => true
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('website_id', array(
                'header'    => Mage::helper('customer')->__('Website'),
                'align'     => 'center',
                'width'     => '80px',
                'type'      => 'options',
                'options'   => Mage::getSingleton('adminhtml/system_store')->getWebsiteOptionHash(true),
                'index'     => 'website_id',
            ));
        }

        $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('customer')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('customer')->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));

        $this->addExportType('*/*/exportCsv', Mage::helper('customer')->__('CSV'));
        $this->addExportType('*/*/exportXml', Mage::helper('customer')->__('Excel XML'));
    }
}
?>