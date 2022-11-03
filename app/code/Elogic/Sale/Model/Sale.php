<?php

namespace Elogic\Sale\Model;

use Elogic\Sale\Api\Data\SaleInterface;
use Magento\Framework\Model\AbstractModel;

class Sale extends AbstractModel implements SaleInterface
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Elogic\Sale\Model\ResourceModel\Sale::class);
    }

    /**
     * @return array|mixed|string|null
     */
    public function getTitle()
    {
        return $this->getData(self::TITLE);
    }

    /**
     * @param $title
     *
     * @return \Elogic\Sale\Model\Sale|void
     */
    public function setTitle($title)
    {
        $this->setData(self::TITLE, $title);
    }

    /**
     * @return array|mixed|string|null
     */
    public function getSlug()
    {
        return $this->getData(self::SLUG);
    }

    /**
     * @param  string  $slug
     *
     * @return \Elogic\Sale\Model\Sale|void
     */
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

    /**
     * @return array|mixed|string|null
     */
    public function getDescription()
    {
        return $this->getData(self::DESCRIPTION);
    }

    /**
     * @param  string  $description
     *
     * @return \Elogic\Sale\Model\Sale|void
     */
    public function setDescription(string $description)
    {
        $this->setData(self::DESCRIPTION, $description);
    }

    /**
     * @return array|mixed|string|null
     */
    public function getPercentDiscount()
    {
        return $this->getData(self::PERCENT_DISCOUNT);
    }

    /**
     * @param  string  $percent
     *
     * @return \Elogic\Sale\Model\Sale|void
     */
    public function setPercentDiscount(string $percent)
    {
        $this->setData(self::PERCENT_DISCOUNT, $percent);
    }

    /**
     * @return array|mixed|string|null
     */
    public function getValidFrom()
    {
        return $this->getData(self::VALID_FROM);
    }

    /**
     * @param  string  $date
     *
     * @return \Elogic\Sale\Model\Sale|void
     */
    public function setValidFrom(string $date)
    {
        $this->setData(self::VALID_FROM, $date);
    }

    /**
     * @return array|mixed|string|null
     */
    public function getValidUntil()
    {
        return $this->getData(self::VALID_UNTIL);
    }

    /**
     * @param $date
     *
     * @return \Elogic\Sale\Model\Sale|void
     */
    public function setValidUntil($date)
    {
        $this->setData(self::VALID_UNTIL, $date);
    }

    /**
     * @return mixed|string|null
     */
    public function getProducts()
    {
        return json_decode($this->getData(self::PRODUCTS));
    }

    /**
     * @param  array  $ids
     *
     * @return \Elogic\Sale\Model\Sale|void
     */
    public function setProducts(array $ids)
    {
        $this->setData(self::PRODUCTS, json_encode($ids));
    }

    /**
     * @return array|int|mixed|null
     */
    public function getCatalogPriceRuleID()
    {
        return $this->getData(self::CATALOG_PRICE_RULE_ID);
    }

    /**
     * @param  int  $id
     *
     * @return \Elogic\Sale\Model\Sale|void
     */
    public function setCatalogPriceRuleID(int $id)
    {
        $this->setData(self::CATALOG_PRICE_RULE_ID, $id);
    }

    /**
     * @return false|int|mixed
     */
    public function getImagePath()
    {
        $imageJson = $this->getData(self::IMAGE_PATH);

        if (is_null($imageJson)) {
            return false;
        }

        $image = json_decode($imageJson, true)[0];

        return $image['url'];
    }

    /**
     * @param $path
     *
     * @return \Elogic\Sale\Model\Sale|void
     */
    public function setImagePath($path)
    {
        $this->setData(self::IMAGE_PATH, $path);
    }
}
