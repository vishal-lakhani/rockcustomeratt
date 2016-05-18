<?php
/**
 * @category   Manage Customer Attribute
 * @package    Rockatt_Attribute
 * @copyright  Copyright (c) 2016 Rocktechnolabs (http://www.rocktechnolabs.com)
 * @author     Vishal Lakhani <vishal.lakhani@yahoo.co.in>
 */
?>
<?php
class Rockatt_Attribute_Block_Adminhtml_Attribute_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct()
	{
		parent::__construct();
		$this->setId("attribute_grid");
		$this->setDefaultSort("id");
		$this->setDefaultDir("ASC");
		$this->setSaveParametersInSession(true);
	}
	
	public function _prepareCollection()
	{
		$collection=Mage::getModel("attribute/test")->getCollection()
		->addFieldToFilter('show_on_order_grid', array('gt' => -1));
		
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}
	
	public function _prepareColumns()
	{
        $this->addColumn('attribute_code', array(
            'header' => 'attribute code',
            'align' => 'left',
            'width' => '',
			'index' => 'attribute_code'
        ));
		
		$this->addColumn('frontend_label', array(
            'header' => 'Label',
            'align' => 'left',
            'width' => '',
			'index' => 'frontend_label'
        ));
		
		$this->addColumn('frontend_input', array(
            'header' => 'Input Type',
            'align' => 'left',
            'width' => '',
			'type'=>'options',
			'options'=>array(
                'text'=> Mage::helper('attribute')->__('Text Field'),
                'textarea'=> Mage::helper('attribute')->__('Text Area'),
                'date'=> Mage::helper('attribute')->__('Date'),
                'yesno'=> Mage::helper('attribute')->__('Yes/No'),
                'multiselect'=> Mage::helper('attribute')->__('Multiple Select'),
                'select'=> Mage::helper('attribute')->__('Dropdown'),
				'static_text'=> Mage::helper('attribute')->__('Static Text'),
				'file'=> Mage::helper('attribute')->__('Sengle File Upload'),
				'image'=> Mage::helper('attribute')->__('Image'),
            ),
			'index' => 'frontend_input'
        ));
		
		$this->addColumn('sort_order', array(
            'header' => 'Sorting Order',
            'align' => 'left',
            'width' => '',
			'index' => 'sort_order'
        ));
		
		$this->addColumn('show_on_customer_grid_new', array(
            'header' => 'Show On Customer Grid',
            'align' => 'left',
            'width' => '',
			'index' => 'show_on_customer_grid_new',
			'type'=>'options',
			'options'=>array(0=>'no',1=>'yes'),
        ));
		
		$this->addColumn('show_on_order_grid', array(
            'header' => 'Show On Orders Grid',
            'align' => 'left',
            'width' => '',
			'type'=>'options',
			'options'=>array(0=>'no',1=>'yes'),
			'index' => 'show_on_order_grid',
        ));
		
		$this->addColumn('show_on_order_view_page', array(
            'header' => 'Show On Order View Page',
            'align' => 'left',
            'width' => '',
			'type'=>'options',
			'options'=>array(0=>'no',1=>'yes'),
			'index' => 'show_on_order_view_page',
        ));
		
		$this->addColumn('show_on_acc_info_page', array(
            'header' => 'Show On Account Information Page',
            'align' => 'left',
            'width' => '',
			'type'=>'options',
			'options'=>array(0=>'no',1=>'yes'),
			'index' => 'show_on_acc_info_page',
        ));
		
		$this->addColumn('show_on_reg_page', array(
            'header' => 'Show On Registration Page',
            'align' => 'left',
            'width' => '',
			'type'=>'options',
			'options'=>array(0=>'no',1=>'yes'),
			'index' => 'show_on_reg_page',
        ));
		
		$this->addColumn('show_on_billing_page', array(
            'header' => 'Show On Biiling Page',
            'align' => 'left',
            'width' => '',
			'type'=>'options',
			'options'=>array(0=>'no',1=>'yes'),
			'index' => 'show_on_billing_page',
        ));
 
        return parent::_prepareColumns();
	}
	
	public function getRowUrl($row)
	{
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }
}
?>