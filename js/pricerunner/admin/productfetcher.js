
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

Event.observe(window, 'load', function() {
   initProductFetcher();
});

ProductFetcher = Class.create();
ProductFetcher.prototype = {
    initialize: function(){

    	this.txtBtnStatus0 = 'Run a feed test';

        // Hide checkbox 
        var defaults = $$("#row_pricerunner_productfetcher_fetcher_group_testfeed .use-default"); // Add , after value to make array
        if (Object.isArray(defaults)) {
            for (var i=0; i<defaults.length; i++) {
                defaults[i].hide();
            }
        }

    	this.changeUi();
    },

    changeUi: function () {
        
        $("pricerunner_productfetcher_testbutton_label").update(this.txtBtnStatus0);

        // Set button text and set it to be visible
        $("pricerunner_productfetcher_testbutton").style.display = "block";

        // Get store info such email, etc..
        response = this.getJson(window.ADMIN_URL +"pricerunnerfeed/config/store");

        if (typeof response === typeof undefined) {
            alert('An unexpected error occurred.');
            return;
        }

        email = response.hasOwnProperty('email') ? response.email : '';
        enabled = response.hasOwnProperty('enabled') ? response.enabled : '';
        ean = response.hasOwnProperty('ean') ? response.ean : '';
        
        // Set store mail as default value of email, when it's empty
    	if ($("pricerunner_productfetcher_fetcher_group_email") !== null 
            && $("pricerunner_productfetcher_fetcher_group_email").value == "") 
        {
	    	$("pricerunner_productfetcher_fetcher_group_email").value = email;
	    }

        // Hide phone and email when the module is enabled
        this.onModuleEnabled(enabled);

        // Pre-select Ean value
        if (!ean)
        {   
            var nodes = $("pricerunner_productfetcher_map_group_ean").childNodes;
            for(var i=0; i < nodes.length; i++) {
                if (/ean/i.test(nodes[i].value)) {
                     nodes[i].setAttribute('selected', true);
                     break;
                }
            }
        }

        document.getElementById('pricerunner_productfetcher_fetcher_group_enabled_feed').addEventListener('change', function() {
            if (enabled == 1) 
            {
                // Hide phone and email when the module is enabled
                $("row_pricerunner_productfetcher_fetcher_group_phone").hide();
                $("row_pricerunner_productfetcher_fetcher_group_email").hide();
            }
        });
    },

    getJson: function(url) {
        var response;
        new Ajax.Request(
            url,
            {
                dataType: 'json',
                onComplete:   function(transport) {response = transport.responseText.evalJSON(true);},
                asynchronous: false
            });

        return response;
    },

    onModuleEnabled: function(enabled) {
        if (enabled == 1) 
        {
            // Hide phone and email when the module is enabled
            $("row_pricerunner_productfetcher_fetcher_group_phone").hide();
            $("row_pricerunner_productfetcher_fetcher_group_email").hide();
        }
    }
}