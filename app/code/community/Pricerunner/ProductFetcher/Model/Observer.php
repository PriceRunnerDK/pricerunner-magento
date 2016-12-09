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

require_once(dirname(__FILE__) . '/../Helper/PricerunnerSDK/files.php');

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
        if (Mage::getStoreConfig('pricerunner_productfetcher/fetcher_group/enabled_feed' ) == 1 && empty($hash)) 
        {
        	$this->pricerunnerRegistration();
        } 
        elseif (Mage::getStoreConfig('pricerunner_productfetcher/fetcher_group/enabled_feed' ) == 0) 
        {
           Mage::getModel('core/config')->deleteConfig('pricerunner_productfetcher/fetcher_group/feedhash');
           Mage::getModel('core/config')->deleteConfig('pricerunner_productfetcher/fetcher_group/email');
           Mage::getModel('core/config')->deleteConfig('pricerunner_productfetcher/fetcher_group/phone');
           Mage::getModel('core/config')->deleteConfig('pricerunner_productfetcher/fetcher_group/testfeed');

           Mage::getModel('core/config')->deleteConfig('pricerunner_productfetcher/map_group/ean');
        } 
    }

    /**
     * Send mail to registration
     * 
     * Description
     * @return Exception|void
     */
    private function pricerunnerRegistration()
    {
        // Generate hash string
        $randomString = PricerunnerSDK::getRandomString();

        $domain = Mage::getBaseUrl (Mage_Core_Model_Store::URL_TYPE_WEB);
        $feedUrl = $domain . "pricerunnerfeed?hash=" . $randomString;

        try {
            PricerunnerSDK::postRegistration($this->getDomainName()
                , Mage::getStoreConfig('pricerunner_productfetcher/fetcher_group/phone')
                , Mage::getStoreConfig('pricerunner_productfetcher/fetcher_group/email')
                , $domain
                , $feedUrl);

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
    }

    /**
     * Get only domain name from the url
     * @return string|false
     */
    private function getDomainName()
    {
        $url = Mage::getBaseUrl (Mage_Core_Model_Store::URL_TYPE_WEB);
        $urlobj = parse_url($url);
        $domain = $urlobj['host'];

        if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs)) 
        {
            return $regs['domain'];
        }

        return false;
    }
}