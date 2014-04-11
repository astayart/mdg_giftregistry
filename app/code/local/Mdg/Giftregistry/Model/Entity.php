<?php
/**
 * Created by PhpStorm.
 * User: astayart
 * Date: 3/24/14
 * Time: 4:51 PM
 */
class Mdg_Giftregistry_Model_Entity extends Mage_Core_Model_Abstract {
    public function __construct() {
        $this->_init('mdg_giftregistry/entity');
        parent::_construct();
    }
    public function updateRegistryData(Mage_Customer_Model_Customer $customer, $data) {
        try {
            if(! empty($data)) {
                $this->setCustomerId($customer->getId());
                $this->setWebsiteId($customer->getWebsiteId());
                $this->setTypeId($data['type_id']);
                $this->setEventName($data['event_name']);
                $this->setEventDate($data['event_date']);
                $this->setEventCountry($data['event_country']);
                $this->setEventLocation($data['event_location']);
            } else {
                throw new Exception('Error processing request: Insufficient data provided');
            }
        } catch (Exception $e) {
            Mage::logException($e);
        }
        return $this;
    }
}