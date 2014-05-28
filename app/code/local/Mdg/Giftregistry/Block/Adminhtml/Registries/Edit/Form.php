<?php
/**
 * Created by PhpStorm.
 * User: magentouser
 * Date: 5/28/14
 * Time: 2:14 PM
 */
class Mdg_Giftregistry_Block_Adminhtml_Registries_Edit_Form extends Mage_Adminhtml_Block_Widget_Form {
	protected function _prepareForm() {
		/** @var $form Varien_Data_Form */
		$form = new Varien_Data_Form(array(
			'id' => 'edit_form',
			'action' => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
			'method' => 'post',
			'enctype' => 'multipart/form-data'
		));
		$form->setUseContainer(true);
		$this->setForm($form);

		$session = Mage::getSingleton('adminhtml/session');

		$data = $session->getData();
		if($data) {
			$session->setFormData(null);
		} elseif (Mage::registry('registry_data')) {
			$data = Mage::registry('registry_data')->getData();
		}

		$fieldset = $form->addFieldset('registry_form', array(
			'legend' => Mage::helper('mdg_giftregistry')->__('Gift Registry Information')
		));
		$fieldset->addFieldset('type_id', 'text', array(
			'label' => 'Registry Id',
			'class' => 'required-entry',
			'required' => true,
			'name' => 'type_id'
		));
		$fieldset->addColumn('website_id', 'text', array(
			'label' => 'Website Id',
			'class' => 'required-entry',
			'required' => true,
			'name' => 'website_id'
		));
		$fieldset->addColumn('event_location', 'text', array(
			'label' => 'Event Location',
			'class' => 'required-entry',
			'required' => true,
			'name' => 'event_location'
		));
		$fieldset->addColumn('event_date', 'text', array(
			'label' => 'Event Date',
			'class' => 'required-entry',
			'required' => true,
			'name' => 'event_date'
		));
		$fieldset->addColumn('event_country', 'text', array(
			'label' => 'Event Country',
			'class' => 'required-entry',
			'required' => true,
			'name' => 'event_country'
		));
		$form->setValues($data);
		return parent::_prepareForm();
	}
}