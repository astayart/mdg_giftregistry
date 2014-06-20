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
			$data = Mage::registry('registry_data')->getData();
		}

		$fieldset = $form->addFieldset('registry_form', array(
			'legend' => Mage::helper('mdg_giftregistry')->__('Gift Registry Information')
		));
		$fieldset->addField('event_name', 'text', array(
			'label' => 'Event Name',
			'class' => 'required-entry',
			'required' => true,
			'name' => 'event_name'
		));
		$fieldset->addField('type_id', 'text', array(
			'label' => 'Event Type Id',
			'class' => 'required-entry',
			'required' => true,
			'name' => 'type_id'
		));
		$fieldset->addField('event_location', 'text', array(
			'label' => 'Event Location',
			'class' => 'required-entry',
			'required' => true,
			'name' => 'event_location'
		));
		$fieldset->addField('event_date', 'date', array(
			'label' => 'Event Date',
			'class' => 'required-entry',
			'required' => true,
			'after_element_html' => '<small>click to set</small>',
			'name' => 'event_date',
			'image' => $this->getSkinUrl('images/grid-cal.gif'), // See more at: http://excellencemagentoblog.com/magento-admin-form-field#sthash.YFqGJWrt.dpuf
			'format' => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT)
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
