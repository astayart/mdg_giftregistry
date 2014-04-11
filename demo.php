<?php
/**
 * Created by PhpStorm.
 * User: astayart
 * Date: 4/2/14
 * Time: 2:17 PM
 */

header("Content-Type: text/plain");
ini_set('display_errors', true);

require 'app/Mage.php';
Mage::setIsDeveloperMode(true);
Mage::app();

$registry = Mage::getModel('mdg_giftregistry/entity');
echo get_class($registry);