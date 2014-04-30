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

    /** @var $item Mdg_Giftregistry_Model_item */
    public function deleteAction()
    {
        try {
            $data = $this->getRequest()->getParams();
            $item = Mage::getModel('mdg_giftregistry/item');
            if( !empty($data)) {
                /** @var  $collection Mdg_Giftregistry_Model_Mysql4_Item_Collection */
                $collection = $item->getCollection();
                $collection->addFieldToFilter('registry_id', $data['registry_id'])
                    ->addFieldToFilter('product_id', $data['product_id']);
                foreach($collection as $del) {
                    $del->delete();
                }

                $successMessage =  Mage::helper('mdg_giftregistry')->__('Product Successfully Removed from the Registry');
                Mage::getSingleton('core/session')->addSuccess($successMessage);
            }
        } catch(Mage_Core_Exception $e) {
            Mage::getSingleton('core/session')->addError($e->getMessage());
        }
        $this->_redirectUrl($this->_getRefererUrl());
    }
}
