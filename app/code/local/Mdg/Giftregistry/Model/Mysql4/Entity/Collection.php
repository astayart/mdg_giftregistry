<?php
/**
 * Created by PhpStorm.
 * User: astayart
 * Date: 4/2/14
 * Time: 1:57 PM
 */
class Mdg_Giftregistry_Model_Mysql4_Entity_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {
    public function _construct() {
        $this->_init('mdg_giftregistry/entity');
        parent::_construct();
    }
}