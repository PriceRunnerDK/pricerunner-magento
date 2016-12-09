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
 * Validate email from the module admin config
 */
class Pricerunner_ProductFetcher_Model_Validation_Email
	extends Mage_Core_Model_Config_Data
{
	protected function _beforeSave()
    {
        if ($this->getGroups()["fetcher_group"]["fields"]["enabled_feed"]["value"] == 1) 
        {
            $value = $this->getValue();
            if (!Zend_Validate::is($value, 'EmailAddress')) 
            {
                Mage::throwException(Mage::helper('adminhtml')->__('Invalid email address "%s".', $value));
            }
            return $this;
        }
    }
}