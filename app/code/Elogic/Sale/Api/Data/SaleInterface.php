<?php

namespace Elogic\Sale\Api\Data;

interface SaleInterface
{
    /**#@+
     * Constants defined for keys of  data array
     */
    const SALE_ID = 'entity_id';
    const TITLE = 'title';
    const SLUG = 'slug';
    const DESCRIPTION = 'description';
    const PERCENT_DISCOUNT = 'percent_discount';
    const VALID_FROM = 'valid_from';
    const VALID_UNTIL = 'valid_until';
    const PRODUCTS = 'products';
    const CATALOG_PRICE_RULE_ID = 'catalog_price_rule_id';
    const IMAGE_PATH = 'sale_image_path';
    const ATTRIBUTES = [
        self::SALE_ID,
        self::TITLE,
        self::SLUG,
        self::DESCRIPTION,
        self::PERCENT_DISCOUNT,
        self::VALID_FROM,
        self::VALID_UNTIL,
        self::PRODUCTS,
        self::CATALOG_PRICE_RULE_ID,
        self::IMAGE_PATH,
    ];
    /**#@-*/

    /**
     * Sale id
     *
     * @return int|null
     */
    public function getId();

    /**
     * Sale title
     *
     * @return string|null
     */
    public function getTitle();

    /**
     * Set sale title
     *
     * @param  string  $title
     *
     * @return $this
     */
    public function setTitle(string $title);

    /**
     * Sale slug
     *
     * @return string|null
     */
    public function getSlug();

    /**
     * Set sale slug
     *
     * @param  string  $slug
     *
     * @return $this
     */
    public function setSlug(string $slug = '');

    /**
     * Sale description
     *
     * @return string|null
     */
    public function getDescription();

    /**
     * Set sale description
     *
     * @param  string  $description
     *
     * @return $this
     */
    public function setDescription(string $description);

    /**
     * Sale percent discount
     *
     * @return string|null
     */
    public function getPercentDiscount();

    /**
     * Set sale percent discount
     *
     * @param  string  $percent
     *
     * @return $this
     */
    public function setPercentDiscount(string $percent);

    /**
     * Sale valid from
     *
     * @return string|null
     */
    public function getValidFrom();

    /**
     * Set sale valid from
     *
     * @param  string  $date
     *
     * @return $this
     */
    public function setValidFrom(string $date);

    /**
     * Sale valid until
     *
     * @return string|null
     */
    public function getValidUntil();

    /**
     * Set sale valid until
     *
     * @param $date
     *
     * @return $this
     */
    public function setValidUntil($date);

    /**
     * Sale description
     *
     * @return string|null
     */
    public function getProducts();

    /**
     * Set sale description
     *
     * @param  array  $ids
     *
     * @return $this
     */
    public function setProducts(array $ids);

    /**
     * Sale catalog price rule id
     *
     * @return int
     */
    public function getCatalogPriceRuleID();

    /**
     * Set catalog price rule id
     *
     * @param  int  $id
     *
     * @return $this
     */
    public function setCatalogPriceRuleID(int $id);

    /**
     * Sale image path
     *
     * @return int|false
     */
    public function getImagePath();

    /**
     * Set image path
     *
     * @return $this
     */
    public function setImagePath($path);
}