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
 * XML config value
 *
 * This class return a comment text to use for enabled config in system.xml
 */
class Pricerunner_ProductFetcher_Model_Config_Enabledfeedcomment
{
    /**
     * Create a comment text with the feed url includes hash code
     * @return string
     */
    public function getCommentText()
    {
    	$feedUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK) . "pricerunnerfeed?hash=";

    	$string = "When enabled, Pricerunner will get the feed link and be able to access the feed on " . $feedUrl . "somehashstring";

        $moduleEnabled = Mage::getStoreConfig('pricerunner_productfetcher/fetcher_group/enabled_feed');
        $hash = Mage::getStoreConfig('pricerunner_productfetcher/fetcher_group/feedhash');

    	// Display feed url when the module is enabled
    	if ($moduleEnabled == 1) 
    	{
    		$feedUrl .= $hash;
			$string = "You can access the XML feed here <a href='" . $feedUrl . "' id='pricerunner_feed_link' target='_blank'>" .$feedUrl. "</a>";
		}
      
        return $string;

    }
}