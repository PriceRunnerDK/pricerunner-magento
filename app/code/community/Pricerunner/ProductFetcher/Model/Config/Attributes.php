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
 * This class return array of store attributes
 */
class Pricerunner_ProductFetcher_Model_Config_Attributes {

	public function toOptionArray() {

		$attribute_codes = array();

        $config = Mage::getModel('eav/config');

        $attributes_codes = $config->getEntityAttributeCodes('catalog_product', null);

        $idx = 0;
        foreach ($attributes_codes as $attribute_code) 
        {
            // Add default value as pre-selected
            if ($idx == 0) 
            {
                $attribute_codes["default"] = "";
            }

            $attribute = $config->getAttribute('catalog_product', $attribute_code);
            if ($attribute !== false && $attribute->getAttributeId() > 0) 
            {
                // To display frontend name use addslashes($attribute->getFrontend()->getLabel() . ' (' . $attribute->getAttributeCode() . ')');
                $attribute_codes[$attribute->getAttributeCode()] = addslashes($attribute->getAttributeCode());
            }

            $idx++;
        }

        asort($attribute_codes);       

        return $attribute_codes;
	}
}