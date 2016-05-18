<?php
/**
 * @category   Manage Customer Attribute
 * @package    Rockatt_Attribute
 * @copyright  Copyright (c) 2016 Rocktechnolabs (http://www.rocktechnolabs.com)
 * @author     Vishal Lakhani <vishal.lakhani@yahoo.co.in>
 */
?>
<?php
require_once 'Mage/Checkout/controllers/OnepageController.php';

class Rockatt_Attribute_OnepageController extends Mage_Checkout_OnepageController
{
	public function saveBillingAction()
    {
        if ($this->_expireAjax()) {
            return;
        }
        if ($this->getRequest()->isPost()) {
			
			$blockobj=new Rockatt_Attribute_Block_Attribute();
			$attributes=$blockobj->getAttributeForBilling();
			$post_arr=$this->getRequest()->getPost();
			$attr_arr=array();
			
			foreach($attributes as $attribute)
			{
				if(is_array($post_arr[$attribute["attribute_code"]]))
				{
					$attr_arr[$attribute["attribute_code"]]=implode(",",$post_arr[$attribute["attribute_code"]]);
				}
				else
				{
					$attr_arr[$attribute["attribute_code"]]=$post_arr[$attribute["attribute_code"]];
				}
			}
			
			Mage::getSingleton('core/session')->setOrderColumnData($attr_arr);
			
            $data = $this->getRequest()->getPost('billing', array());
            $customerAddressId = $this->getRequest()->getPost('billing_address_id', false);

            if (isset($data['email'])) {
                $data['email'] = trim($data['email']);
            }
            $result = $this->getOnepage()->saveBilling($data, $customerAddressId);

            if (!isset($result['error'])) {
                if ($this->getOnepage()->getQuote()->isVirtual()) {
                    $result['goto_section'] = 'payment';
                    $result['update_section'] = array(
                        'name' => 'payment-method',
                        'html' => $this->_getPaymentMethodsHtml()
                    );
                } elseif (isset($data['use_for_shipping']) && $data['use_for_shipping'] == 1) {
                    $result['goto_section'] = 'shipping_method';
                    $result['update_section'] = array(
                        'name' => 'shipping-method',
                        'html' => $this->_getShippingMethodsHtml()
                    );

                    $result['allow_sections'] = array('shipping');
                    $result['duplicateBillingInfo'] = 'true';
                } else {
                    $result['goto_section'] = 'shipping';
                }
            }

            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
    }
	
	public function successAction()
    {
		$data=Mage::getSingleton('core/session')->getOrderColumnData();
		$orderid=Mage::getSingleton('checkout/session')->getLastOrderId();
		
		try
		{
			$quoteItem=Mage::getModel('sales/order')->load($orderid);
			foreach($data as $key=>$value)
			{
				$quoteItem->setData($key,$value);
			}
			
			$quoteItem->save();
			
			Mage::getSingleton('core/session')->unsOrderColumnData();
		}
		catch(Exception $ex)
		{
			
		}
		
        $session = $this->getOnepage()->getCheckout();
        if (!$session->getLastSuccessQuoteId()) {
            $this->_redirect('checkout/cart');
            return;
        }

        $lastQuoteId = $session->getLastQuoteId();
        $lastOrderId = $session->getLastOrderId();
        $lastRecurringProfiles = $session->getLastRecurringProfileIds();
        if (!$lastQuoteId || (!$lastOrderId && empty($lastRecurringProfiles))) {
            $this->_redirect('checkout/cart');
            return;
        }

        $session->clear();
        $this->loadLayout();
        $this->_initLayoutMessages('checkout/session');
        Mage::dispatchEvent('checkout_onepage_controller_success_action', array('order_ids' => array($lastOrderId)));
        $this->renderLayout();
    }
}
?>