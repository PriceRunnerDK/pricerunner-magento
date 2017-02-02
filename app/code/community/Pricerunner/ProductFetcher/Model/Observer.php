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

require_once(dirname(__FILE__) . '/../Helper/pricerunner-php-sdk/src/files.php');

use \PricerunnerSDK\PricerunnerSDK;

/**
 * This class handler the event when the Save Config button is clicked
 */
class Pricerunner_ProductFetcher_Model_Observer
{

    public function handle_adminSystemConfigChangedSection($observer)
    {
        // Get hash value from config
        $hash = Mage::getStoreConfig('pricerunner_productfetcher/fetcher_group/feedhash');

        // Check if this module config is enabled
        if (Mage::getStoreConfig('pricerunner_productfetcher/fetcher_group/enabled_feed' ) == 1 && empty($hash)) {
        	$this->pricerunnerRegistration();
        } elseif (Mage::getStoreConfig('pricerunner_productfetcher/fetcher_group/enabled_feed' ) == 0) {
           $this->deactivate();
        }
    }

    /**
     * Send mail to registration
     * @return Exception|void
     */
    private function pricerunnerRegistration()
    {
        // Generate hash string
        $randomString = PricerunnerSDK::getRandomString();

        $domain  = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK);
        $feedUrl = $domain . "pricerunnerfeed?hash=" . $randomString;

        try {
            // Post registration data to pricerunner.
            PricerunnerSDK::postRegistration(
                Mage::app()->getDefaultStoreView()->getFrontendName(),
                Mage::getStoreConfig('pricerunner_productfetcher/fetcher_group/phone'),
                Mage::getStoreConfig('pricerunner_productfetcher/fetcher_group/email'),
                $domain,
                $feedUrl
            );

            // Success registration :)
            Mage::getSingleton('core/session')->addSuccess('Your feed information has been sent to Pricerunner. The XML Feed can be accessed on ' . $feedUrl);

            // Save hash string to config to use for checking when the feed url got access
            Mage::getModel('core/config')->saveConfig('pricerunner_productfetcher/fetcher_group/feedhash', $randomString);
        } 
        catch (Exception $e) 
        {
            // Registration error...
            Mage::getModel('core/config')->saveConfig('pricerunner_productfetcher/fetcher_group/enabled_feed', 0);
            Mage::getSingleton('core/session')->addError('Unable to send feed information to Pricerunner.');
        }


        $this->cleanConfigCache();
    }

    private function deactivate()
    {
        Mage::getModel('core/config')->deleteConfig('pricerunner_productfetcher/fetcher_group/feedhash');
        Mage::getModel('core/config')->deleteConfig('pricerunner_productfetcher/fetcher_group/email');
        Mage::getModel('core/config')->deleteConfig('pricerunner_productfetcher/fetcher_group/phone');
        Mage::getModel('core/config')->deleteConfig('pricerunner_productfetcher/fetcher_group/testfeed');
        Mage::getModel('core/config')->deleteConfig('pricerunner_productfetcher/map_group/ean');
    }

    private function cleanConfigCache()
    {
        $cacheType = 'config';

        Mage::app()->getCacheInstance()->cleanType($cacheType);
        Mage::dispatchEvent('adminhtml_cache_refresh_type', array('type' => $cacheType));
    }
}
