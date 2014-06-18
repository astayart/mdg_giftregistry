<?php
/**
 * Created by PhpStorm.
 * User: magentouser
 * Date: 5/28/14
 * Time: 2:14 PM
 */
class Mdg_Giftregistry_Block_Adminhtml_Registries_Edit_Form extends Mage_Adminhtml_Block_Widget_Form {
	protected function _prepareForm() {
		/** @var Varien_Data_Form $form */
		$form = new Varien_Data_Form(array(
			'id' => 'edit_form',
			'action' => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
			'method' => 'post',
			'enctype' => 'multipart/form-data'
		));
		$form->setUseContainer(true);
		$this->setForm($form);

		/** @var Mage_Adminhtml_Model_Session $session */
		$session = Mage::getSingleton('adminhtml/session');

		$data = $session->getFormData();
		if($data) {
			$session->setFormData(null);
		} elseif (Mage::registry('registry_data')) {
			$data = Mage::registry('registry_data')->getFormData();
		}

		$fieldset = $form->addFieldset('registry_form', array(
			'legend' => Mage::helper('mdg_giftregistry')->__('Gift Registry Information')
		));
		$fieldset->addField('type_id', 'text', array(
			'label' => 'Registry Id',
			'class' => 'required-entry',
			'required' => true,
			'name' => 'type_id'
		));
		$fieldset->addField('website_id', 'text', array(
			'label' => 'Website Id',
			'class' => 'required-entry',
			'required' => true,
			'name' => 'website_id'
		));
		$fieldset->addField('event_location', 'text', array(
			'label' => 'Event Location',
			'class' => 'required-entry',
			'required' => true,
			'name' => 'event_location'
		));
		$fieldset->addField('event_date', 'text', array(
			'label' => 'Event Date',
			'class' => 'required-entry',
			'required' => true,
			'name' => 'event_date'
		));
		$fieldset->addField('event_country', 'text', array(
			'label' => 'Event Country',
			'class' => 'required-entry',
			'required' => true,
			'name' => 'event_country'
		));
		$form->setValues($data);
		return parent::_prepareForm();
	}
}
