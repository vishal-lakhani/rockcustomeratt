<?php
/**
 * @category   Manage Customer Attribute
 * @package    Rockatt_Attribute
 * @copyright  Copyright (c) 2016 Rocktechnolabs (http://www.rocktechnolabs.com)
 * @author     Vishal Lakhani <vishal.lakhani@yahoo.co.in>
 */
?>
<?php
require_once 'Mage/Customer/controllers/AccountController.php';

class Rockatt_Attribute_AccountController extends Mage_Customer_AccountController
{
	public function createPostAction()
    {
		if(Mage::getVersion()=="1.7.0.0" || Mage::getVersion()=="1.7.0.1")
		{
			$session = $this->_getSession();
			if ($session->isLoggedIn()) {
				$this->_redirect('*/*/');
				return;
			}
			$session->setEscapeMessages(true); // prevent XSS injection in user input
			if ($this->getRequest()->isPost()) {
				$errors = array();

				if (!$customer = Mage::registry('current_customer')) {
					$customer = Mage::getModel('customer/customer')->setId(null);
				}

				/* @var $customerForm Mage_Customer_Model_Form */
				$customerForm = Mage::getModel('customer/form');
				$customerForm->setFormCode('customer_account_create')
					->setEntity($customer);

				$customerData = $customerForm->extractData($this->getRequest());

				if ($this->getRequest()->getParam('is_subscribed', false)) {
					$customer->setIsSubscribed(1);
				}

				/**
				 * Initialize customer group id
				 */
				$customer->getGroupId();

				if ($this->getRequest()->getPost('create_address')) {
					/* @var $address Mage_Customer_Model_Address */
					$address = Mage::getModel('customer/address');
					/* @var $addressForm Mage_Customer_Model_Form */
					$addressForm = Mage::getModel('customer/form');
					$addressForm->setFormCode('customer_register_address')
						->setEntity($address);

					$addressData    = $addressForm->extractData($this->getRequest(), 'address', false);
					$addressErrors  = $addressForm->validateData($addressData);
					if ($addressErrors === true) {
						$address->setId(null)
							->setIsDefaultBilling($this->getRequest()->getParam('default_billing', false))
							->setIsDefaultShipping($this->getRequest()->getParam('default_shipping', false));
						$addressForm->compactData($addressData);
						$customer->addAddress($address);

						$addressErrors = $address->validate();
						if (is_array($addressErrors)) {
							$errors = array_merge($errors, $addressErrors);
						}
					} else {
						$errors = array_merge($errors, $addressErrors);
					}
				}

				try {
					$customerErrors = $customerForm->validateData($customerData);
					if ($customerErrors !== true) {
						$errors = array_merge($customerErrors, $errors);
					} else {
						$customerForm->compactData($customerData);
						$customer->setPassword($this->getRequest()->getPost('password'));
						$customer->setConfirmation($this->getRequest()->getPost('confirmation'));
						$customerErrors = $customer->validate();
						if (is_array($customerErrors)) {
							$errors = array_merge($customerErrors, $errors);
						}
					}
					
					$blockobj=new Rockatt_Attribute_Block_Attribute();
			$attributes=$blockobj->getAttributeForAccInfo();
			
			$attr_values=array();
			
			foreach($attributes as $attribute)
			{
				if($attribute["frontend_input"]=="image"||$attribute["frontend_input"]=="file")
				{
					$contrl_name=$attribute["attribute_code"]."1";
					$attr_code=$attribute["attribute_code"];
					
					if($attribute["frontend_input"]=="image")
					{
						
						if(isset($_FILES[$contrl_name]['name']) && $_FILES[$contrl_name]['name'] != '')
						{
							try
							{
								$path = Mage::getBaseDir('media'). DS .'customer'. DS .'images'. DS;  //desitnation directory    
								$fname = $_FILES[$contrl_name]['name']; //file name                       
								$uploader = new Varien_File_Uploader($contrl_name); //load class
								$uploader->setAllowedExtensions(array('jpg','png','jpeg')); //Allowed extension for file
								$uploader->setAllowCreateFolders(true); //for creating the directory if not exists
								$uploader->setAllowRenameFiles(true); //if true, uploaded file's name will be changed, if file with the same name already exists directory.
								$uploader->setFilesDispersion(false);
								$value=$uploader->save($path,$fname); //save the file on the specified path
								
								$attr_values[$attr_code]=DS . "images". DS .$value["file"];
							}
							catch (Exception $e)
							{
								$this->_getSession()->setCustomerFormData($this->getRequest()->getPost())
									->addException($e, $this->__($attribute["frontend_label"]." Invalid File. Upload Image Only"));
									
								$this->_redirect('*/*/create');
								return;
							}
						}
					}
					else
					{
						if(isset($_FILES[$contrl_name]['name']) && $_FILES[$contrl_name]['name'] != '')
						{
							try
							{
								$path = Mage::getBaseDir('media'). DS .'customer'. DS .'documents'. DS;  //desitnation directory    
								$fname = $_FILES[$contrl_name]['name']; //file name                       
								$uploader = new Varien_File_Uploader($contrl_name); //load class
								$uploader->setAllowedExtensions(array('doc', 'docx','pdf', 'jpg', 'png', 'zip')); //Allowed extension for file
								$uploader->setAllowCreateFolders(true); //for creating the directory if not exists
								$uploader->setAllowRenameFiles(true); //if true, uploaded file's name will be changed, if file with the same name already exists directory.
								$uploader->setFilesDispersion(false);
								$value=$uploader->save($path,$fname); //save the file on the specified path
								
								$attr_values[$attr_code]=DS . "documents". DS .$value["file"];
							}
							catch (Exception $e)
							{
								$this->_getSession()->setCustomerFormData($this->getRequest()->getPost())
									->addException($e, $this->__($attribute["frontend_label"]." Invalid File Extenstion"));
									
								$this->_redirect('*/*/create');
								return;
							}
						}
					}
				}
				
			}
			
			foreach($attr_values as $key=>$value)
			{
				unlink(Mage::getBaseDir('media'). DS .'customer'.$customer->getData($key));
				$customer->setData($key,$value);
			}

					$validationResult = count($errors) == 0;

					if (true === $validationResult) {
						$customer->save();

						Mage::dispatchEvent('customer_register_success',
							array('account_controller' => $this, 'customer' => $customer)
						);

						if ($customer->isConfirmationRequired()) {
							$customer->sendNewAccountEmail(
								'confirmation',
								$session->getBeforeAuthUrl(),
								Mage::app()->getStore()->getId()
							);
							$session->addSuccess($this->__('Account confirmation is required. Please, check your email for the confirmation link. To resend the confirmation email please <a href="%s">click here</a>.', Mage::helper('customer')->getEmailConfirmationUrl($customer->getEmail())));
							$this->_redirectSuccess(Mage::getUrl('*/*/index', array('_secure'=>true)));
							return;
						} else {
							$session->setCustomerAsLoggedIn($customer);
							$url = $this->_welcomeCustomer($customer);
							$this->_redirectSuccess($url);
							return;
						}
					} else {
						$session->setCustomerFormData($this->getRequest()->getPost());
						if (is_array($errors)) {
							foreach ($errors as $errorMessage) {
								$session->addError($errorMessage);
							}
						} else {
							$session->addError($this->__('Invalid customer data'));
						}
					}
				} catch (Mage_Core_Exception $e) {
					$session->setCustomerFormData($this->getRequest()->getPost());
					if ($e->getCode() === Mage_Customer_Model_Customer::EXCEPTION_EMAIL_EXISTS) {
						$url = Mage::getUrl('customer/account/forgotpassword');
						$message = $this->__('There is already an account with this email address. If you are sure that it is your email address, <a href="%s">click here</a> to get your password and access your account.', $url);
						$session->setEscapeMessages(false);
					} else {
						$message = $e->getMessage();
					}
					$session->addError($message);
				} catch (Exception $e) {
					$session->setCustomerFormData($this->getRequest()->getPost())
						->addException($e, $this->__('Cannot save the customer.'));
				}
			}

			$this->_redirectError(Mage::getUrl('*/*/create', array('_secure' => true)));
		}
		else
		{		
			/** @var $session Mage_Customer_Model_Session */
			$session = $this->_getSession();
			if ($session->isLoggedIn()) {
				$this->_redirect('*/*/');
				return;
			}
			$session->setEscapeMessages(true); // prevent XSS injection in user input
			if (!$this->getRequest()->isPost()) {
				$errUrl = $this->_getUrl('*/*/create', array('_secure' => true));
				$this->_redirectError($errUrl);
				return;
			}
			
			$blockobj=new Rockatt_Attribute_Block_Attribute();
			$attributes=$blockobj->getAttributeForAccInfo();
			
			$attr_values=array();
			
			foreach($attributes as $attribute)
			{
				if($attribute["frontend_input"]=="image"||$attribute["frontend_input"]=="file")
				{
					$contrl_name=$attribute["attribute_code"]."1";
					$attr_code=$attribute["attribute_code"];
					
					if($attribute["frontend_input"]=="image")
					{
						
						if(isset($_FILES[$contrl_name]['name']) && $_FILES[$contrl_name]['name'] != '')
						{
							try
							{
								$path = Mage::getBaseDir('media'). DS .'customer'. DS .'images'. DS;  //desitnation directory    
								$fname = $_FILES[$contrl_name]['name']; //file name                       
								$uploader = new Varien_File_Uploader($contrl_name); //load class
								$uploader->setAllowedExtensions(array('jpg','png','jpeg')); //Allowed extension for file
								$uploader->setAllowCreateFolders(true); //for creating the directory if not exists
								$uploader->setAllowRenameFiles(true); //if true, uploaded file's name will be changed, if file with the same name already exists directory.
								$uploader->setFilesDispersion(false);
								$value=$uploader->save($path,$fname); //save the file on the specified path
								
								$attr_values[$attr_code]=DS . "images". DS .$value["file"];
							}
							catch (Exception $e)
							{
								$this->_getSession()->setCustomerFormData($this->getRequest()->getPost())
									->addException($e, $this->__($attribute["frontend_label"]." Invalid File. Upload Image Only"));
									
								$this->_redirect('*/*/create');
								return;
							}
						}
					}
					else
					{
						if(isset($_FILES[$contrl_name]['name']) && $_FILES[$contrl_name]['name'] != '')
						{
							try
							{
								$path = Mage::getBaseDir('media'). DS .'customer'. DS .'documents'. DS;  //desitnation directory    
								$fname = $_FILES[$contrl_name]['name']; //file name                       
								$uploader = new Varien_File_Uploader($contrl_name); //load class
								$uploader->setAllowedExtensions(array('doc', 'docx','pdf', 'jpg', 'png', 'zip')); //Allowed extension for file
								$uploader->setAllowCreateFolders(true); //for creating the directory if not exists
								$uploader->setAllowRenameFiles(true); //if true, uploaded file's name will be changed, if file with the same name already exists directory.
								$uploader->setFilesDispersion(false);
								$value=$uploader->save($path,$fname); //save the file on the specified path
								
								$attr_values[$attr_code]=DS . "documents". DS .$value["file"];
							}
							catch (Exception $e)
							{
								$this->_getSession()->setCustomerFormData($this->getRequest()->getPost())
									->addException($e, $this->__($attribute["frontend_label"]." Invalid File Extenstion"));
									
								$this->_redirect('*/*/create');
								return;
							}
						}
					}
				}
				
			}

			$customer = $this->_getCustomer();
			
			foreach($attr_values as $key=>$value)
			{
				unlink(Mage::getBaseDir('media'). DS .'customer'.$customer->getData($key));
				$customer->setData($key,$value);
			}

			try {
				$errors = $this->_getCustomerErrors($customer);

				if (empty($errors)) {
					if (version_compare(Mage::getVersion(),"1.9.1.0",">="))
					{
						// Only from 1.9.1.0
						$customer->cleanPasswordsValidationData();
					}
					//$customer->cleanPasswordsValidationData();
					$customer->save();
					$this->_dispatchRegisterSuccess($customer);
					$this->_successProcessRegistration($customer);
					return;
				} else {
					$this->_addSessionError($errors);
				}
			} catch (Mage_Core_Exception $e) {
				$session->setCustomerFormData($this->getRequest()->getPost());
				if ($e->getCode() === Mage_Customer_Model_Customer::EXCEPTION_EMAIL_EXISTS) {
					$url = $this->_getUrl('customer/account/forgotpassword');
					$message = $this->__('There is already an account with this email address. If you are sure that it is your email address, <a href="%s">click here</a> to get your password and access your account.', $url);
					$session->setEscapeMessages(false);
				} else {
					$message = $e->getMessage();
				}
				$session->addError($message);
			} catch (Exception $e) {
				$session->setCustomerFormData($this->getRequest()->getPost())
					->addException($e, $this->__('Cannot save the customer.'));
			}
			$errUrl = $this->_getUrl('*/*/create', array('_secure' => true));
			$this->_redirectError($errUrl);
		}
    }
	
	public function editPostAction()
    {
		if(Mage::getVersion()=="1.7.0.0" || Mage::getVersion()=="1.7.0.1")
		{
			if (!$this->_validateFormKey()) {
				return $this->_redirect('*/*/edit');
			}

			if ($this->getRequest()->isPost()) {
				$blockobj=new Rockatt_Attribute_Block_Attribute();
				$attributes=$blockobj->getAttributeForAccInfo();
				
				$attr_values=array();
				
				foreach($attributes as $attribute)
				{
					if($attribute["frontend_input"]=="image"||$attribute["frontend_input"]=="file")
					{
						$contrl_name=$attribute["attribute_code"]."1";
						$attr_code=$attribute["attribute_code"];
						
						if($attribute["frontend_input"]=="image")
						{	
							if(isset($_FILES[$contrl_name]['name']) && $_FILES[$contrl_name]['name'] != '')
							{
								try
								{
									$path = Mage::getBaseDir('media'). DS .'customer'. DS .'images'. DS;  //desitnation directory    
									$fname = $_FILES[$contrl_name]['name']; //file name                       
									$uploader = new Varien_File_Uploader($contrl_name); //load class
									$uploader->setAllowedExtensions(array('jpg','png','jpeg')); //Allowed extension for file
									$uploader->setAllowCreateFolders(true); //for creating the directory if not exists
									$uploader->setAllowRenameFiles(true); //if true, uploaded file's name will be changed, if file with the same name already exists directory.
									$uploader->setFilesDispersion(false);
									$value=$uploader->save($path,$fname); //save the file on the specified path
									
									$attr_values[$attr_code]=DS . "images". DS .$value["file"];
								}
								catch (Exception $e)
								{
									$this->_getSession()->setCustomerFormData($this->getRequest()->getPost())
										->addException($e, $this->__($attribute["frontend_label"]." Invalid File. Upload Image Only"));
										
									$this->_redirect('*/*/edit');
									return;
								}
							}
						}
						else
						{
							if(isset($_FILES[$contrl_name]['name']) && $_FILES[$contrl_name]['name'] != '')
							{
								try
								{
									$path = Mage::getBaseDir('media'). DS .'customer'. DS .'documents'. DS;  //desitnation directory    
									$fname = $_FILES[$contrl_name]['name']; //file name                       
									$uploader = new Varien_File_Uploader($contrl_name); //load class
									$uploader->setAllowedExtensions(array('doc', 'docx','pdf', 'jpg', 'png', 'zip')); //Allowed extension for file
									$uploader->setAllowCreateFolders(true); //for creating the directory if not exists
									$uploader->setAllowRenameFiles(true); //if true, uploaded file's name will be changed, if file with the same name already exists directory.
									$uploader->setFilesDispersion(false);
									$value=$uploader->save($path,$fname); //save the file on the specified path
									
									$attr_values[$attr_code]=DS . "documents". DS .$value["file"];
								}
								catch (Exception $e)
								{
									$this->_getSession()->setCustomerFormData($this->getRequest()->getPost())
										->addException($e, $this->__($attribute["frontend_label"]." Invalid File Extenstion"));
									$this->_redirect('*/*/edit');
									return;
								}
							}
						}
					}
					
				}
				
				/** @var $customer Mage_Customer_Model_Customer */
				$customer = $this->_getSession()->getCustomer();
				
				foreach($attr_values as $key=>$value)
				{
					unlink(Mage::getBaseDir('media'). DS .'customer'.$customer->getData($key));
					$customer->setData($key,$value);
				}
				
				/** @var $customer Mage_Customer_Model_Customer */
				$customer = $this->_getSession()->getCustomer();

				/** @var $customerForm Mage_Customer_Model_Form */
				$customerForm = Mage::getModel('customer/form');
				$customerForm->setFormCode('customer_account_edit')
					->setEntity($customer);

				$customerData = $customerForm->extractData($this->getRequest());

				$errors = array();
				$customerErrors = $customerForm->validateData($customerData);
				if ($customerErrors !== true) {
					$errors = array_merge($customerErrors, $errors);
				} else {
					$customerForm->compactData($customerData);
					$errors = array();

					// If password change was requested then add it to common validation scheme
					if ($this->getRequest()->getParam('change_password')) {
						$currPass   = $this->getRequest()->getPost('current_password');
						$newPass    = $this->getRequest()->getPost('password');
						$confPass   = $this->getRequest()->getPost('confirmation');

						$oldPass = $this->_getSession()->getCustomer()->getPasswordHash();
						if (Mage::helper('core/string')->strpos($oldPass, ':')) {
							list($_salt, $salt) = explode(':', $oldPass);
						} else {
							$salt = false;
						}

						if ($customer->hashPassword($currPass, $salt) == $oldPass) {
							if (strlen($newPass)) {
								/**
								 * Set entered password and its confirmation - they
								 * will be validated later to match each other and be of right length
								 */
								$customer->setPassword($newPass);
								$customer->setConfirmation($confPass);
							} else {
								$errors[] = $this->__('New password field cannot be empty.');
							}
						} else {
							$errors[] = $this->__('Invalid current password');
						}
					}

					// Validate account and compose list of errors if any
					$customerErrors = $customer->validate();
					if (is_array($customerErrors)) {
						$errors = array_merge($errors, $customerErrors);
					}
				}

				if (!empty($errors)) {
					$this->_getSession()->setCustomerFormData($this->getRequest()->getPost());
					foreach ($errors as $message) {
						$this->_getSession()->addError($message);
					}
					$this->_redirect('*/*/edit');
					return $this;
				}

				try {
					$customer->setConfirmation(null);
					$customer->save();
					$this->_getSession()->setCustomer($customer)
						->addSuccess($this->__('The account information has been saved.'));

					$this->_redirect('customer/account');
					return;
				} catch (Mage_Core_Exception $e) {
					$this->_getSession()->setCustomerFormData($this->getRequest()->getPost())
						->addError($e->getMessage());
				} catch (Exception $e) {
					$this->_getSession()->setCustomerFormData($this->getRequest()->getPost())
						->addException($e, $this->__('Cannot save the customer.'));
				}
			}

			$this->_redirect('*/*/edit');
		}
		else
		{
			if (!$this->_validateFormKey()) {
				return $this->_redirect('*/*/edit');
			}

			if ($this->getRequest()->isPost())
			{	
				$blockobj=new Rockatt_Attribute_Block_Attribute();
				$attributes=$blockobj->getAttributeForAccInfo();
				
				$attr_values=array();
				
				foreach($attributes as $attribute)
				{
					if($attribute["frontend_input"]=="image"||$attribute["frontend_input"]=="file")
					{
						$contrl_name=$attribute["attribute_code"]."1";
						$attr_code=$attribute["attribute_code"];
						
						if($attribute["frontend_input"]=="image")
						{	
							if(isset($_FILES[$contrl_name]['name']) && $_FILES[$contrl_name]['name'] != '')
							{
								try
								{
									$path = Mage::getBaseDir('media'). DS .'customer'. DS .'images'. DS;  //desitnation directory    
									$fname = $_FILES[$contrl_name]['name']; //file name                       
									$uploader = new Varien_File_Uploader($contrl_name); //load class
									$uploader->setAllowedExtensions(array('jpg','png','jpeg')); //Allowed extension for file
									$uploader->setAllowCreateFolders(true); //for creating the directory if not exists
									$uploader->setAllowRenameFiles(true); //if true, uploaded file's name will be changed, if file with the same name already exists directory.
									$uploader->setFilesDispersion(false);
									$value=$uploader->save($path,$fname); //save the file on the specified path
									
									$attr_values[$attr_code]=DS . "images". DS .$value["file"];
								}
								catch (Exception $e)
								{
									$this->_getSession()->setCustomerFormData($this->getRequest()->getPost())
										->addException($e, $this->__($attribute["frontend_label"]." Invalid File. Upload Image Only"));
										
									$this->_redirect('*/*/edit');
									return;
								}
							}
						}
						else
						{
							if(isset($_FILES[$contrl_name]['name']) && $_FILES[$contrl_name]['name'] != '')
							{
								try
								{
									$path = Mage::getBaseDir('media'). DS .'customer'. DS .'documents'. DS;  //desitnation directory    
									$fname = $_FILES[$contrl_name]['name']; //file name                       
									$uploader = new Varien_File_Uploader($contrl_name); //load class
									$uploader->setAllowedExtensions(array('doc', 'docx','pdf', 'jpg', 'png', 'zip')); //Allowed extension for file
									$uploader->setAllowCreateFolders(true); //for creating the directory if not exists
									$uploader->setAllowRenameFiles(true); //if true, uploaded file's name will be changed, if file with the same name already exists directory.
									$uploader->setFilesDispersion(false);
									$value=$uploader->save($path,$fname); //save the file on the specified path
									
									$attr_values[$attr_code]=DS . "documents". DS .$value["file"];
								}
								catch (Exception $e)
								{
									$this->_getSession()->setCustomerFormData($this->getRequest()->getPost())
										->addException($e, $this->__($attribute["frontend_label"]." Invalid File Extenstion"));
									$this->_redirect('*/*/edit');
									return;
								}
							}
						}
					}
					
				}
				
				/** @var $customer Mage_Customer_Model_Customer */
				$customer = $this->_getSession()->getCustomer();
				
				foreach($attr_values as $key=>$value)
				{
					unlink(Mage::getBaseDir('media'). DS .'customer'.$customer->getData($key));
					$customer->setData($key,$value);
				}
				
				/** @var $customerForm Mage_Customer_Model_Form */
				$customerForm = $this->_getModel('customer/form');
				$customerForm->setFormCode('customer_account_edit')
					->setEntity($customer);

				$customerData = $customerForm->extractData($this->getRequest());
				
				$errors = array();
				$customerErrors = $customerForm->validateData($customerData);
				
				if ($customerErrors !== true) {
					$errors = array_merge($customerErrors, $errors);
				} else {
					$customerForm->compactData($customerData);
					$errors = array();

					// If password change was requested then add it to common validation scheme
					if ($this->getRequest()->getParam('change_password')) {
						$currPass   = $this->getRequest()->getPost('current_password');
						$newPass    = $this->getRequest()->getPost('password');
						$confPass   = $this->getRequest()->getPost('confirmation');

						$oldPass = $this->_getSession()->getCustomer()->getPasswordHash();
						if ( $this->_getHelper('core/string')->strpos($oldPass, ':')) {
							list($_salt, $salt) = explode(':', $oldPass);
						} else {
							$salt = false;
						}

						if ($customer->hashPassword($currPass, $salt) == $oldPass) {
							if (strlen($newPass)) {
								/**
								 * Set entered password and its confirmation - they
								 * will be validated later to match each other and be of right length
								 */
								$customer->setPassword($newPass);
								$customer->setPasswordConfirmation($confPass);
							} else {
								$errors[] = $this->__('New password field cannot be empty.');
							}
						} else {
							$errors[] = $this->__('Invalid current password');
						}
					}

					// Validate account and compose list of errors if any
					$customerErrors = $customer->validate();
					if (is_array($customerErrors)) {
						$errors = array_merge($errors, $customerErrors);
					}
				}

				if (!empty($errors)) {
					$this->_getSession()->setCustomerFormData($this->getRequest()->getPost());
					foreach ($errors as $message) {
						$this->_getSession()->addError($message);
					}
					$this->_redirect('*/*/edit');
					return $this;
				}

				try {
					if (version_compare(Mage::getVersion(),"1.9.1.0",">="))
					{
						// Only from 1.9.1.0
						$customer->cleanPasswordsValidationData();
					}
					//$customer->cleanPasswordsValidationData();
					$customer->save();
					$this->_getSession()->setCustomer($customer)
						->addSuccess($this->__('The account information has been saved.'));

					$this->_redirect('customer/account');
					return;
				} catch (Mage_Core_Exception $e) {
					$this->_getSession()->setCustomerFormData($this->getRequest()->getPost())
						->addError($e->getMessage());
				} catch (Exception $e) {
					$this->_getSession()->setCustomerFormData($this->getRequest()->getPost())
						->addException($e, $this->__('Cannot save the customer.'));
				}
			}

			$this->_redirect('*/*/edit');
		}
	}
}
?>