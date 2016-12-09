<?php
/**
 * Pricerunner
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @author    Modified Solutions ApS
 * @category  Pricerunner
 * @package   Pricerunner_ProductFetcher
 * @copyright Copyright (c) 2016 Modified Solutions ApS (https://www.modified.dk)
 * @license   
 */

/**
 * XML config value
 * 
 * This class return array of stores
 */
class Pricerunner_ProductFetcher_Model_Config_Store {

	public function toOptionArray() {
		
		// Set pre-selected
		$enabledStores = Mage::getStoreConfig('pricerunner_productfetcher/fetcher_group/stores');
		if (is_null($enabledStores))
		{
			Mage::getModel('core/config')->saveConfig('pricerunner_productfetcher/fetcher_group/stores', 0);
		}

	    return Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true);
	}
}