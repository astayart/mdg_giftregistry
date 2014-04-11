<?php
/**
 * Created by PhpStorm.
 * User: astayart
 * Date: 3/24/14
 * Time: 4:40 PM
 */
class Mdg_Giftregistry_Model_Mysql4_Type extends Mage_Core_Model_Mysql4_Abstract {
    public function _construct() {
        $this->_init('mdg_giftregistry/type', 'type_id');
    }
}