<?php
/**
 * Created by PhpStorm.
 * User: magentouser
 * Date: 5/20/14
 * Time: 4:04 PM
 */

class Mdg_Giftregistry_Block_Adminhtml_Registries_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {
	public function __construct(){
		parent::__construct();
		$this->_objectId = 'id';
		$this->_blockGroup = 'registries';
		$this->_controller = 'adminhtml_mdggiftregistry';
		$this->_mode = 'edit';

		$this->_updateButton('save', 'label', Mage::helper('adminhtml')->__('Save Registry'));
		$this->_updateButton('delete', 'label', Mage::helper('adminhtml')->__('Delete Registry'));
	}

	public function getHeaderText() {
		if(Mage::registry('registries_data') && Mage::registry('registries_data')->getId()) {
			return Mage::helper('adminhtml')->__('Edit Registry \'%s\'', $this->escapeHtml(Mage::registry('registries_data')->getTitle()));
		}
		return Mage::helper('adminhtml')->__('Add Registry');
	}
}