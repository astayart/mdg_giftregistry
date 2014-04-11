<?php
/**
 * Created by PhpStorm.
 * User: astayart
 * Date: 3/24/14
 * Time: 4:44 PM
 */
class Mdg_Giftregistry_Model_Item extends Mage_Core_Model_Abstract {
    public function __construct() {
        $this->_init('mdg_giftregistry/item');
        parent::_construct();
    }
}