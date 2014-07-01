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
        if($sp = $this->getRequest()->getParams()) {
            $results = Mage::getModel('mdg_giftregistry/entity')->getCollection();
            if(isset($sp['type_id'])){
                $results->addFieldToFilter('type_id', $sp['type_id']);
            }
            if(isset($sp['date']) && !empty($sp['date'])){
                $results->addFieldToFilter('event_date', $sp['date']);
            }
            if(isset($sp['location']) && !empty($sp['location'])){
                $results->addFieldToFilter('event_location', $sp['location']);
            }
            $layout = $this->getLayout();
            $block = $layout->getBlock('giftregistry.results');
            $block->setResults($results);

        }

        $this->renderLayout();
        return $this;
    }
}