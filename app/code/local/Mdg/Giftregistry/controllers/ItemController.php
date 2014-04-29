<?php
/**
 * Created by PhpStorm.
 * User: astayart
 * Date: 4/20/14
 * Time: 10:00 AM
 */
class Mdg_Giftregistry_ItemController extends Mage_Core_Controller_Front_Action
{
    public function addAction()
    {
        try {
            $data = $this->getRequest()->getParams();
            $item = Mage::getModel('mdg_giftregistry/item');
            if( !empty($data)) {
                $item->setProductId($data['product_id']);
                $item->setRegistryId($data['registry_id']);
                $item->save();
                $successMessage =  Mage::helper('mdg_giftregistry')->__('Product Successfully Added to the Registry');
                Mage::getSingleton('core/session')->addSuccess($successMessage);
            }
        } catch(Mage_Core_Exception $e) {
            Mage::getSingleton('core/session')->addError($e->getMessage());
        }
        $this->_redirectUrl($this->_getRefererUrl());
    }
    public function editAction()
    {
        return $this;
    }
    public function deleteAction()
    {
        return $this;
    }
}
