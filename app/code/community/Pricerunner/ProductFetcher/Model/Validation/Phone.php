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
 * XML validation
 * 
 * Validate phone from the module admin config
 */
class Pricerunner_ProductFetcher_Model_Validation_Phone
	extends Mage_Core_Model_Config_Data
{
	protected function _beforeSave()
    {
        if ($this->getGroups()["fetcher_group"]["fields"]["enabled_feed"]["value"] == 1) 
        {
            $phone = $this->getValue(); //get the value from our config
            $phone = preg_replace('#[^0-9]#','',$phone); //strip non numeric
            if(strlen($phone) < 8) 
            {
                Mage::throwException(Mage::helper('adminhtml')->__('Invalid phone number "%s". It must be minimum 8 digits.', $phone));
            }
     
            return $this;
        }
    }

}