<?php
/**
 * Created by PhpStorm.
 * User: magentouser
 * Date: 5/2/14
 * Time: 4:17 PM
 */

class Mdg_Giftregistry_Block_Adminhtml_Registries_Grid
extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct() {
        parent::__construct();
        $this->setId('registriesGrid');
        $this->setDefaultSort('event_date');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection() {
        $collection = Mage::getModel('mdg_giftregistry/entity')
            ->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $this->addColumn('entity_id', array(
            'header' => Mage::helper('mdg_giftregistry')->__('Id'),
            'width' => 50,
            'index' => 'entity_id',
            'sortable' => false
        ));

        $this->addColumn('event_location', array(
            'header' => Mage::helper('mdg_giftregistry')->__('Location'),
            'index' => 'event_location',
            'sortable' => false
        ));
        $this->addColumn('event_date', array(
            'header' => Mage::helper('mdg_giftregistry')->__('Event Date'),
            'index' => 'event_date',
            'sortable' => false
        ));
        $this->addColumn('type_id', array(
            'header' => Mage::helper('mdg_giftregistry')->__('Event Type'),
            'index' => 'type_id',
            'sortable' => false
        ));
        return parent::_prepareColumns();
    }
    protected function _prepareMassaction(){
		$this->setMassactionIdField('entity_id');
		$this->getMassactionBlock()->setFormFieldName('registries');
		$this->getMassactionBlock()->addItem('delete', array(
			'label' => Mage::helper('mdg_giftregistry')->__('Delete'),
			'url' => $this->getUrl('*/*/massDelete'),
			'confirm' => Mage::helper('mdg_giftregistry')->__('Are you sure?')
		));
		return $this;
    }
	public function getRowUrl($row){
		return $this->getUrl('*/*/edit', array('id' => $row->getEntityId()));
	}
}