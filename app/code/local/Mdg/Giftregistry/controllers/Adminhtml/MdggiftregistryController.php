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

		if($id){
			$registry->load($id);
			if($registry->getId()){
				$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
				if($data){
					$registry->setData($data)->setId($id);
				}
			} else {
				Mage::getSingleton('adminhtml/session')->addError('The gift registry does not exist');
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
		$this->loadLayout();
		$this->renderLayout();
		return $this;
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

