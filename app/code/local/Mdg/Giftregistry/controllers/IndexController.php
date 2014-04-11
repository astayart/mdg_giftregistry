<?php

/**
 * Created by PhpStorm.
 * User: astayart
 * Date: 4/4/14
 * Time: 2:38 PM
 */
class Mdg_Giftregistry_IndexController extends Mage_Core_Controller_Front_Action
{
    public function preDispatch()
    {
        parent::preDispatch();
        if (!Mage::getSingleton('customer/session')->authenticate($this)) {
            $this->getResponse()->setRedirect(Mage::helper('customer')->getLoginUrl());
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
        }
    }

    public function indexAction()
    {
        //echo 'this is our test controller';
        $this->loadLayout();
        $this->renderLayout();
        // may need to add "return $this;" but somehow i feel this is superfluous.

    }

    public function deleteAction()
    {
//        $this->loadLayout();
//        $this->renderLayout();
        try {
            $regid = $this->getRequest()->getParam('registry_id');
            if ($regid && $this->getRequest()->getPost()) {
                if ($registry = Mage::getModel('mdg_giftregistry/entity')->load($regid)) {
                    $registry->delete();
                    $mess = Mage::helper('mdg_giftregistry')->__('Gift registry deleted.');
                    Mage::getsingleton('core/session')->addSuccess($mess);
                } else {
                    throw new Exception('There was a problem deleting the registry');
                }
            }

        } catch (Exception $e) {
            Mage::getSingleton('core/session')->addError($e->getMessage());
        }
        $this->_redirect('*/*/');
    }

    public function newAction()
    {
        $this->loadLayout();
        $this->renderLayout();

    }

    public function editAction()
    {
//        $this->loadLayout();
//        $this->renderLayout();
        try {
            $data = $this->getRequest()->getParams();
            $registry = Mage::getModel('mdg_giftregistry/entity');
            $customer = Mage::getSingleton('customer/session')->getCustomer();

            if ($this->getRequest()->getPost() && !empty($data)) {
                $registry->load($data['registry_id']);
                if ($registry) {
                    $registry->updateRegistryData($customer, $data);
                    $registry->save();
                    $successMessage = Mage::helper('mdg_giftregistry')->__('Registry Successfully Saved');
                    Mage::getSingleton('core/session')->addSuccess($successMessage);
                } else {
                    throw new Exception('Invalid registry specified');
                }

            } else {
                throw new Exception('Insufficient data provided');
            }

        } catch (Mage_Core_Exception $e) {
            Mage::getSingleton('core/session')->addError($e->getMessages());
            $this->_redirect('*/*/');
        }
        $this->_redirect('*/*/');
    }

    public function newPostAction()
    {
//        $this->loadLayout();
//        $this->renderLayout();
        try {
            $data = $this->getRequest()->getParams();
            $registry = Mage::getModel('mdg_giftregistry/entity');
            $customer = Mage::getSingleton('customer/session')->getCustomer();

            if ($this->getRequest()->getPost() && !empty($data)) {
                $registry->updateRegistryData($customer, $data);
                $registry->save();
                $successMessage = Mage::helper('mdg_giftregistry')->__('Registry Successfully Created');
                Mage::getSingleton('core/session')->addSuccess($successMessage);

            } else {
                throw new Exception('Insufficient data provided');
            }

        } catch (Mage_Core_Exception $e) {
            Mage::getSingleton('core/session')->addError($e->getMessages());
            $this->_redirect('*/*/');
        }
        $this->_redirect('*/*/');
    }

    public function editPostAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }
}



