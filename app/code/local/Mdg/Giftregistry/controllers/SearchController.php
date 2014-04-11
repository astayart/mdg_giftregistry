<?php
/**
 * Created by PhpStorm.
 * User: astayart
 * Date: 4/7/14
 * Time: 4:06 PM
 */
class Mdg_Giftregistry_SearchController extends Mage_Core_Controller_Front_Action {
    public function indexAction() {
        $this->loadLayout();
        $this->renderLayout();
        return $this;
    }
    public function resultsAction() {
        $this->loadLayout();
        if($sp = $this->getRequest()->getParam('search_params')) {
            $results = Mage::getModel('mdg_giftregistry/entity')->getCollection();
            if($sp['type']){
                $results->addFieldToFilter('type_id', $sp['type']);
            }
            if($sp['date']){
                $results->addFieldToFilter('event_date', $sp['date']);
            }
            if($sp['location']){
                $results->addFieldToFilter('event_location', $sp['location']);
            }
            $this->getLayout()->getBlock('mdg_giftregistry.search.results')->setResults($results);

        }

        $this->renderLayout();
        return $this;
    }
}