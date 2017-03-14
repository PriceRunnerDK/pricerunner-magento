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
require_once(dirname(__FILE__) . '/../Helper/CustomValidator/MagentoProductValidator.php');
require_once(dirname(__FILE__) . '/../Helper/CustomValidator/MagentoProductCollectionValidator.php');

use PricerunnerSDK\PricerunnerSDK;
use PricerunnerSDK\Errors\ProductErrorRenderer;
use PricerunnerSDK\Models\Product;
use CustomValidator\MagentoProductCollectionValidator;

/**
 * Index Controller
 * This is main controller to access the feed
 */
class Pricerunner_ProductFetcher_IndexController
    extends Mage_Core_Controller_Front_Action
{
    /**
     * Index action
     * To access the feed from url
     * @return string xml
     */
    public function indexAction()
    {       
        if(empty($_GET['hash'])) exit;

        if ($this->isModuleEnabled($_GET['hash'])) exit;

        if (isset($_GET['test'])) {
            return $this->testFeed();
        }

        $products = $this->getStoreProducts();
        $pricerunnerDataContainer = PricerunnerSDK::generateDataContainer($products, true, new MagentoProductCollectionValidator());
        $xmlString = $pricerunnerDataContainer->getXmlString();

        header("Content-Type:text/xml; charset=utf-8");
        echo $xmlString;

        return;
    }

    /**
     * Test action
     * To access the feed from url
     * @return string html
     */
    private function testFeed()
    {
        $products = $this->getStoreProducts();
        $pricerunnerDataContainer = PricerunnerSDK::generateDataContainer($products, true, new MagentoProductCollectionValidator());
        $errors = $pricerunnerDataContainer->getErrors();

        $productErrorRenderer = new ProductErrorRenderer($errors);
        echo $productErrorRenderer->render();

        return;
    }

    /**
     * Check if the module is enabled
     * @param string $feedhash 
     * @return bool
     */
    private function isModuleEnabled($feedhash)
    {
        // Get enabled and hash config from the module
        $moduleEnabled = Mage::getStoreConfig('pricerunner_productfetcher/fetcher_group/enabled_feed');
        $hash = Mage::getStoreConfig('pricerunner_productfetcher/fetcher_group/feedhash');

        // Check if module is enabled and is correct hash
        return ($moduleEnabled == 0 || $feedhash !== $hash);
    }

    /**
     * Get the products belongs to the stores.
     * Except the configurable and grouped products when its contains simple or virtual products
     * @param array enabledStores
     * @return array products
     */
    private function getStoreProducts() 
    {
        // Get default store id
        $storeId = Mage::app()
            ->getWebsite(true)
            ->getDefaultGroup()
            ->getDefaultStoreId();

        $collection = Mage::getModel('catalog/product')->getCollection()
            ->setStore($storeId) //Set Store scope for collection
            ->addStoreFilter($storeId);

        // Get only product type in this array, when configurable and grouped products contains simple or virtual products
        $collection->addAttributeToFilter('type_id', array('nin' => [Mage_Catalog_Model_Product_Type::TYPE_BUNDLE, Mage_Catalog_Model_Product_Type::TYPE_GROUPED]));

        // Filter visible / enabled products
        $collection->addAttributeToFilter('status', array('neq' => Mage_Catalog_Model_Product_Status::STATUS_DISABLED));
        $collection->addFieldToFilter('visibility', array('neq' => Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE));

        $products = array();

        // Iterate through the list of products to get attribute values
        // Assign the products to Pricerunner product
        foreach ($collection as $item) 
        { 
            $product = Mage::getModel('catalog/product')->setStoreId($storeId)->load($item->getEntityId());

            $product = $this->createPricerunnerProduct($product);

            $products[] = $product;
        }

        return $products;
    }


    /**
     * Assign the product attributes from store to pricerunner object
     * @param  Mage_Catalog_Model_Product $product
     * @return Product $pricerunnerProduct
     */
    private function createPricerunnerProduct(Mage_Catalog_Model_Product $product)
    {
        // Set product sku
        $productSku = $product->getSku();

        $store = Mage::app()->getStore($product->getStoreId());

        // Set category name
        $categoryName = $this->getProductCategory($product);
        
        // Set product link
        $productUrl = $product->getUrlPath();
        if (!empty($productUrl))
        {            
            $baseUrl = $store->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
            $productLink = $baseUrl . $product->getUrlPath();
        }
        else 
        {
            $productLink = $product->getProductUrl();
        }
        
        // Set image url 
        $imageUrl = "";
        $image = $product->getImage();
        if ($image != 'no_selection' && $image != "") 
        {
            $imageUrl = $store->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA, false) . 'catalog/product' . '/' . ltrim($image, '/');
        }  

        // Get & Set stock 
        $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
        $stockStatus = $stock->getIsInStock() == 1 ? 'In Stock' : 'Out of Stock';

        // Set price
        $price = $this->getDisplayPrice($product);

        $ean = Mage::getStoreConfig('pricerunner_productfetcher/map_group/ean');
        $ean = isset($ean) && $ean != "default" ? $product[$ean] : ""; 

        // Set manufaturer sku
        $manufacturerSku = Mage::getStoreConfig('pricerunner_productfetcher/map_group/manufacturer_sku');
        $manufacturerSku = isset($manufacturerSku) && $manufacturerSku != "default" ? $product[$manufacturerSku] : ""; 

        // Set manufacturer
        $manufacturer = Mage::getStoreConfig('pricerunner_productfetcher/map_group/manufacturer');
        $manufacturerName = isset($manufacturer) && $manufacturer != "default" ? $product->getAttributeText($manufacturer) : "";

        $deliveryTime = Mage::getStoreConfig('pricerunner_productfetcher/map_group/delivery_time');
        $deliveryTime = isset($deliveryTime) && $deliveryTime != "default" ? $product[$deliveryTime]  : "";

        $pricerunnerProduct = new Product();

        $description = html_entity_decode($product->getDescription());

        $pricerunnerProduct->setProductName(PricerunnerSDK::getXmlReadyString($product->getName()));
        $pricerunnerProduct->setCategoryName(PricerunnerSDK::getXmlReadyString($categoryName));
        $pricerunnerProduct->setSku($productSku);
        $pricerunnerProduct->setPrice($price);
        $pricerunnerProduct->setShippingCost(Mage::getStoreConfig('carriers/flatrate/price', $product->getStoreId())); // Flat rate
        $pricerunnerProduct->setProductUrl($productLink);
        $pricerunnerProduct->setManufacturerSku($manufacturerSku);
        $pricerunnerProduct->setManufacturer($manufacturerName);
        $pricerunnerProduct->setEan($ean);
        $pricerunnerProduct->setDescription(PricerunnerSDK::getXmlReadyString($description));
        $pricerunnerProduct->setImageUrl($imageUrl);
        $pricerunnerProduct->setStockStatus($stockStatus);
        $pricerunnerProduct->setDeliveryTime($deliveryTime);
        // $pricerunnerProduct->setRetailerMessage('');
        $pricerunnerProduct->setProductState('New');

        return $pricerunnerProduct;
    }

    /**
     * Get parent id of a product
     * @param int $productId 
     * @return int parentId
     */
    private function getParentId($productId) 
    {
        $parentIds = Mage::getModel('catalog/product_type_grouped')->getParentIdsByChild($productId);

        if(!$parentIds)
        {
            $parentIds = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($productId);
        }

        return $parentIds[0];
    }

    /**
     * Get sale price that display in the webshop
     * For the bundle product, get the minimum price
     * @param Mage_Catalog_Model_Product $product
     * @return string
     */
    private function getDisplayPrice($product)
    {
        $priceIncludingTax = Mage::helper('tax')->getPrice($product, $product->getFinalPrice(), true);
        return sprintf("%.2F", $priceIncludingTax);
    }

    /**
     * Get product category
     * Excludes default and root category and get only one category group if the product is in many categories
     * @param Mage_Catalog_Model_Product $product
     * @return string
     */
    private function getProductCategory($product)
    {

        $collection = $product->getCategoryCollection()->addFieldToFilter('is_active', 1);
        $categories = $collection->exportToArray();

        $output = array();
        $cacheCategories = array();

        foreach ($categories as $cat) 
        {
            $categoriesOutput = array();

            $pathItems = explode('/', $cat['path']);

            foreach ($pathItems AS $id) 
            {
                // Avoid duplicate category
                if (!array_key_exists($id, $cacheCategories)) 
                {
                    $category = Mage::getModel('catalog/category')->setStoreId($product->getStoreId())->load($id);
                    $cacheCategories[$id] = trim($category->getName());
                }

                $category_name = $cacheCategories[$id];

                if (empty($category_name)) 
                {
                    continue;
                }

                // Excludes default and root category
                if (!(strpos(strtolower($category_name), "default") !== false || strpos(strtolower($category_name), "root") !== false)) 
                {
                    array_push($categoriesOutput, $category_name);
                }
            }

            $output[implode(' > ', $categoriesOutput)] = count($categoriesOutput);
        }

        // Limit the output
        arsort($output);
        $output = array_keys($output);

        // Return only the first categories group
        return $output[0]; 
    }
}