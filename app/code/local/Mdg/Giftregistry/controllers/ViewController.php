<?php
/**
 * Created by PhpStorm.
 * User: astayart
 * Date: 4/7/14
 * Time: 4:24 PM
 */
class Mdg_Giftregistry_ViewController extends Mage_Core_Controller_Front_Action {
    public function viewAction() {
        $regid = $this->getRequest()->getParam('registry_id');
        if($regid) {
            $entity = Mage::getModel('mdg_giftregistry/entity');
            if($entity->load($regid)) {
                Mage::register('loaded_registry', $entity);
                $this->loadLayout();
                $this->_initLayoutMessages('customer/session');
                $this->renderLayout();
            } else {
                $this->_forward('noroute');
            }
            return $this;
        }
    }
}