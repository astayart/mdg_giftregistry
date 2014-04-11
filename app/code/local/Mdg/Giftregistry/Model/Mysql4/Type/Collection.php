<?php
/**
 * Created by PhpStorm.
 * User: astayart
 * Date: 3/24/14
 * Time: 4:42 PM
 */
class Mdg_Giftregistry_Model_Mysql4_Type_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {
    public function _construct() {
        $this->_init('mdg_giftregistry/type');
        parent::_construct();
    }
}