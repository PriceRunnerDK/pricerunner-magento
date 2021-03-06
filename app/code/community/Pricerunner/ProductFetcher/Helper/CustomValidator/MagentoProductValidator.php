<?php

namespace CustomValidator;

use PricerunnerSDK\Validators\ProductValidator;

class MagentoProductValidator extends ProductValidator
{
    public function validate()
    {
        $this->validateCategoryName();
        $this->validateProductName();
        $this->validateSku();
        $this->validatePrice();
        $this->validateProductUrl();
        $this->validateIsbn();
        $this->validateManufacturer();
        $this->validateManufacturerSku();
        $this->validateShippingCost();
        $this->validateEan();
        $this->validateUpc();
        $this->validateDescription();
        $this->validateImageUrl();
        $this->validateStockStatus();
        // $this->validateDeliveryTime();
        $this->validateRetailerMessage();
        $this->validateCatalogId();
        $this->validateWarranty();
    }
}