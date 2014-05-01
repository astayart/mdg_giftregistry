<?php
/**
 * Created by PhpStorm.
 * User: magentouser
 * Date: 5/1/14
 * Time: 3:55 PM
 */
class Mdg_Giftregistry_Adminhtml_GiftregistryController extends Mage_Adminhtml_Controller_Action {
    public function indexAction() {
        $this->loadLayout();
        $this->renderLayout();
        return $this;
    }
}