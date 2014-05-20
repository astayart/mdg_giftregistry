<?php

/**
 * Created by PhpStorm.
 * User: magentouser
 * Date: 5/1/14
 * Time: 4:22 PM
 */
class Mdg_Giftregistry_Block_Adminhtml_Customer_Edit_Tab_Giftregistry
    extends Mage_Adminhtml_Block_Template
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    public function __construct()
    {
        $this->setTemplate('mdg/giftregistry/customer/main.phtml');
        parent::_construct();
    }

    public function getCustomerId() {
        return Mage::registry('current_customer')->getId();
    }
    /**
     * Return Tab label
     *
     * @return string
     */
    public function getTabLabel()
    {
        return $this->__('MdgGiftregistry List');
    }

    /**
     * Return Tab title
     *
     * @return string
     */
    public function getTabTitle()
    {
        $this->__('Click to view the customer Gift Registries');
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }

}