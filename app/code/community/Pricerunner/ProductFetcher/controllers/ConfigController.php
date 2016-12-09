<?php
/**
 * 2016 Modified Solutions ApS www.modified.dk hej@modified.dk
 *
 * NOTICE OF LICENSE
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 *
 * @author    Modified Solutions ApS
 * @category  Pricerunner
 * @package   Pricerunner_ProductFetcher
 * @copyright Copyright (c) 2016 Modified Solutions ApS (https://www.modified.dk)
 * @license   Mozilla Public License Version 2.0
 */

/**
 * Configuration Controller
 */
class Pricerunner_ProductFetcher_ConfigController
    extends Mage_Core_Controller_Front_Action
{
	/**
	 * Get store configuration
	 * @return json
	 */
	public function storeAction()
	{
		$result = array();
		$result["email"] = Mage::getStoreConfig('trans_email/ident_general/email');
		$result["enabled"] = Mage::getStoreConfig('pricerunner_productfetcher/fetcher_group/enabled_feed');
		$result["ean"] = Mage::getStoreConfig('pricerunner_productfetcher/map_group/ean');

		$this->getResponse()->clearHeaders()->setHeader('Content-type','application/json', true);
        $this->getResponse()->setBody(json_encode($result));
	}

}