<?php
/**
 * Created by PhpStorm.
 * User: astayart
 * Date: 4/2/14
 * Time: 1:52 PM
 */
class Mdg_Giftregistry_Model_Mysql4_Entity extends Mage_Core_Model_Mysql4_Abstract {
    public function _construct() {
        $this->_init('mdg_giftregistry/entity', 'entity_id');
    }
}