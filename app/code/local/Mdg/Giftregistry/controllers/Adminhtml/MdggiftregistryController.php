<?php
/**
 * Created by PhpStorm.
 * User: magentouser
 * Date: 5/1/14
 * Time: 3:55 PM
 */
class Mdg_Giftregistry_Adminhtml_MdgGiftregistryController extends Mage_Adminhtml_Controller_Action {
    public function indexAction() {
        $this->loadLayout();
        $this->renderLayout();
        return $this;
    }
	public function editAction() {
		$id = $this->getRequest()->getParam('id', null);
		$registry = Mage::getModel('mdg_giftregistry/entity');

		/** var Mage_Adminhtml_Model_Session $session */
		$session = Mage::getSingleton('adminhtml/session');

		if($id){
			$registry->load($id);
			if($registry->getId()){
				$data = $session->getFormData(true);
				if($data){
					$registry->setData($data)->setId($id);
				}
			} else {
				$session->addError('The gift registry does not exist');
				$this->_redirect('*/*/');
			}

		}
		Mage::register('registry_data', $registry);

		$this->loadLayout();
		$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
		$this->renderLayout();
		return $this;
	}
	public function saveAction() {
		$data = $this->getRequest()->getPost();
		$id = $this->getRequest()->getParam('id');
		try {
			/** @var Mdg_Giftregistry_Model_Entity $registry */
			$registry = Mage::getModel('mdg_giftregistry/entity')->load($id);
			/* Just calling setData will not work because $data does not
				contain things like the customer_id. since this $data will
				replace the existing data, you will end up trying to save
				a registry without a customer_id, which is a violation of
				a foreign key constraint. */
			//$registry->setData($data);
			$registry->setEventName($data['event_name']);
			$registry->setTypeId($data['type_id']);
			$registry->setEventLocation($data['event_location']);
			$registry->setEventDate($data['event_date']);
			$registry->setEventCountry($data['event_country']);

			$registry->save();
			$this->_redirect('*/*/edit', array('id' => $id));
		} catch (Exception $e) {
			$this->_getSession()->addError('error! ' . $e->getMessage());
			Mage::logException($e);
			$this->_redirect('*/*/edit', array('id' => $id));
		}
	}
	public function newAction() {
		$this->loadLayout();
		$this->renderLayout();
		return $this;
	}
	public function massDeleteAction() {
		$registryIds = $this->getRequest()->getParam('registries');
		/** @var $session Mage_Adminhtml_Model_Session */
		$session = Mage::getSingleton('adminhtml/session');
		if(!is_array($registryIds)) {
			$session->addError('Please Select one or more registries');
		} else {
			try {
				/** @var  $reg Mdg_Giftregistry_Model_Entity */
				$reg = Mage::getModel('mdg_giftregistry/entity');
				foreach($registryIds as $rid) {
					$reg->reset()->load($rid)->delete();
				}
				$trans = Mage::helper('adminhtml');
				$translated = $trans->__('Total of %d registries deleted.', count($registryIds));
				$session->addSuccess($translated);
			} catch (Exception $e){
				$session->addError("no reset? " . $e->getMessage());
			}
		}
		$this->_redirect('*/*/');
	}
}

