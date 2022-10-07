<?php

namespace Elogic\Sale\Model;

use Elogic\Sale\Api\Data\SaleInterface;
use Magento\Framework\Model\AbstractModel;

class Sale extends AbstractModel implements SaleInterface
{
    protected function _construct()
    {
        $this->_init(\Elogic\Sale\Model\ResourceModel\Sale::class);
    }

    public function getTitle()
    {
        return $this->getData(self::TITLE);
    }

    public function setTitle($title)
    {
        $this->setData(self::TITLE, $title);
    }

    public function getSlug()
    {
        return $this->getData(self::SLUG);
    }

    public function setSlug(string $slug = '')
    {
        $divider = '-';
        if (empty($slug)) {
            $slug = $this->getTitle();
        }

        $slug = preg_replace('~[^\pL\d]+~u', $divider, $slug);
        $slug = transliterator_transliterate('Ukrainian-Latin/BGN', $slug);
        $slug = preg_replace('~[^-\w]+~', '', $slug);
        $slug = trim($slug, $divider);
        $slug = preg_replace('~-+~', $divider, $slug);
        $slug = strtolower($slug);

        $this->setData(self::SLUG, $slug);
    }

    public function getDescription()
    {
        return $this->getData(self::DESCRIPTION);
    }

    public function setDescription(string $description)
    {
        $this->setData(self::DESCRIPTION, $description);
    }

    public function getPercentDiscount()
    {
        return $this->getData(self::PERCENT_DISCOUNT);
    }

    public function setPercentDiscount(string $percent)
    {
        $this->setData(self::PERCENT_DISCOUNT, $percent);
    }

    public function getValidFrom()
    {
        return $this->getData(self::VALID_FROM);
    }

    public function setValidFrom(string $date)
    {
        $this->setData(self::VALID_FROM, $date);
    }

    public function getValidUntil()
    {
        return $this->getData(self::VALID_UNTIL);
    }

    public function setValidUntil($date)
    {
        $this->setData(self::VALID_UNTIL, $date);
    }

    public function getProducts()
    {
        return json_decode($this->getData(self::PRODUCTS));
    }

    public function setProducts(array $ids)
    {
        $this->setData(self::PRODUCTS, json_encode($ids));
    }

    public function getCatalogPriceRuleID()
    {
        return $this->getData(self::CATALOG_PRICE_RULE_ID);
    }

    public function setCatalogPriceRuleID(int $id)
    {
        $this->setData(self::CATALOG_PRICE_RULE_ID, $id);
    }

    public function getImagePath()
    {
        $imageJson = $this->getData(self::IMAGE_PATH);

        if (is_null($imageJson)) {
            return false;
        }

        $image = json_decode($imageJson, true)[0];

        return $image['url'];
    }

    public function setImagePath($path)
    {
        $this->setData(self::IMAGE_PATH, $path);
    }

    public function checkIdentifier($identifier)
    {
        $slug = explode('/', $identifier)[1];

        return $this->_getResource()->checkSlug($slug);
    }
}
