<?php
/**
 * Created by PhpStorm.
 * User: magentouser
 * Date: 5/28/14
 * Time: 1:16 PM
 */
class Mdg_Giftregistry_Block_Adminhtml_Registries_Edit
extends Mage_Adminhtml_Block_Widget_Form_Container {
	private $helper;
	public function __construct() {
		parent::__construct();
		$this->_objectId = 'id';
		$this->_blockGroup = 'mdg_giftregistry';
		$this->_controller  = 'adminhtml_registries';
		$this->_mode = 'edit';
		$this->helper = Mage::helper('mdg_giftregistry');

		$this->_updateButton('save', 'label', $this->helper->__('Save Registry'));
		$this->_updateButton('delete', 'label', $this->helper->__('Delete Registry'));
	}
	public function getHeaderText(){
		$rd = Mage::registry('registry_data');

		if($rd && $rd->getId()){
			return $this->helper->__("Edit Registry '%s'", $this->escapeHtml($rd->getTitle()));
		}
		return $this->helper->__('Add Registry');
	}
}