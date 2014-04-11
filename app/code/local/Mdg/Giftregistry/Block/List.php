<?php
/**
 * Created by PhpStorm.
 * User: astayart
 * Date: 4/8/14
 * Time: 3:22 PM
 */
class Mdg_Giftregistry_Block_List extends Mage_Core_Block_Template {
    public function getCustomerRegistries() {
        $collection = null;
        $currentCustomer = Mage::getSingleton('customer/session')->getCustomer();
        if($currentCustomer) {
            $collection = Mage::getModel('mdg_giftregistry/entity')
                ->getCollection()
                ->addFieldToFilter('customer_id', $currentCustomer->getId());
        }
        return $collection;
    }
}