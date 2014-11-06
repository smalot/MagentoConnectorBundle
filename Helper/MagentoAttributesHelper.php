<?php

namespace Pim\Bundle\MagentoConnectorBundle\Helper;

/**
 * This helper allows to manage Magento attributes
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MagentoAttributesHelper
{
    /** @staticvar string */
    protected static $headerAttributeSet = '_attribute_set';

    /** @staticvar string */
    protected static $headerCategory = '_category';

    /** @staticvar string */
    protected static $headerCategoryRoot = '_root_category';

    /** @staticvar string */
    protected static $headerCreatedAt = 'created_at';

    /** @staticvar string */
    protected static $headerProductType = '_type';

    /** @staticvar string */
    protected static $headerProductWebsite = '_product_websites';

    /** @staticvar string */
    protected static $headerSku = 'sku';

    /** @staticvar string */
    protected static $headerStatus = 'status';

    /** @staticvar string */
    protected static $headerStore = '_store';

    /** @staticvar string */
    protected static $headerTaxClassID = 'tax_class_id';

    /** @staticvar string */
    protected static $headerUpdatedAt = 'updated_at';

    /** @staticvar string */
    protected static $headerVisibility = 'visibility';

    /** @staticvar string */
    protected static $headerName = 'name';

    /** @staticvar string */
    protected static $headerDescription = 'description';

    /** @staticvar string */
    protected static $headerShortDescription = 'short_description';

    /** @staticvar string */
    protected static $headerAssociationReplaceSubject = '_links_#toReplace#_sku';

    /** @staticvar string */
    protected static $headerAssociationReplacePattern = '/#toReplace#/';

    /** @staticvar string */
    protected static $headerSuperProductSku = '_super_products_sku';

    /** @staticvar string */
    protected static $headerSuperAttributeCode = '_super_attribute_code';

    /** @staticvar string */
    protected static $headerSuperAttributeOption = '_super_attribute_option';

    /** @staticvar string */
    protected static $headerSuperAttributePrice = '_super_attribute_price_corr';

    /** @staticvar string */
    protected static $productTypeSimple = 'simple';

    /** @staticvar string */
    protected static $productTypeConfigurable = 'configurable';

    /**
     * Returns mandatory attributes needed to create the base product to update associations
     *
     * @return array
     */
    public function getMandatoryAttributeCodesForAssociations()
    {
        return [
            $this->getHeaderSku(),
            $this->getHeaderDescription(),
            $this->getHeaderShortDescription(),
            $this->getHeaderName()
        ];
    }

    /**
     * Returns the header in terms of the type code and the pattern
     *
     * @param string $typeCode
     *
     * @return string
     */
    public function getAssociationTypeHeader($typeCode)
    {
        return preg_replace(
            $this->getHeaderAssociationReplacePattern(),
            $typeCode,
            $this->getHeaderAssociationReplaceSubject()
        );
    }

    /**
     * @return string
     */
    public static function getProductTypeConfigurable()
    {
        return static::$productTypeConfigurable;
    }

    /**
     * @return string
     */
    public static function getProductTypeSimple()
    {
        return static::$productTypeSimple;
    }

    /**
     * @return string
     */
    public static function getHeaderAssociationReplacePattern()
    {
        return static::$headerAssociationReplacePattern;
    }

    /**
     * @return string
     */
    public static function getHeaderAssociationReplaceSubject()
    {
        return static::$headerAssociationReplaceSubject;
    }

    /**
     * @return string
     */
    public static function getHeaderAttributeSet()
    {
        return static::$headerAttributeSet;
    }

    /**
     * @return string
     */
    public static function getHeaderCategory()
    {
        return static::$headerCategory;
    }

    /**
     * @return string
     */
    public static function getHeaderCategoryRoot()
    {
        return static::$headerCategoryRoot;
    }

    /**
     * @return string
     */
    public static function getHeaderCreatedAt()
    {
        return static::$headerCreatedAt;
    }

    /**
     * @return string
     */
    public static function getHeaderDescription()
    {
        return static::$headerDescription;
    }

    /**
     * @return string
     */
    public static function getHeaderName()
    {
        return static::$headerName;
    }

    /**
     * @return string
     */
    public static function getHeaderProductType()
    {
        return static::$headerProductType;
    }

    /**
     * @return string
     */
    public static function getHeaderProductWebsite()
    {
        return static::$headerProductWebsite;
    }

    /**
     * @return string
     */
    public static function getHeaderShortDescription()
    {
        return static::$headerShortDescription;
    }

    /**
     * @return string
     */
    public static function getHeaderSku()
    {
        return static::$headerSku;
    }

    /**
     * @return string
     */
    public static function getHeaderStatus()
    {
        return static::$headerStatus;
    }

    /**
     * @return string
     */
    public static function getHeaderStore()
    {
        return static::$headerStore;
    }

    /**
     * @return string
     */
    public static function getHeaderSuperAttributeCode()
    {
        return static::$headerSuperAttributeCode;
    }

    /**
     * @return string
     */
    public static function getHeaderSuperAttributeOption()
    {
        return static::$headerSuperAttributeOption;
    }

    /**
     * @return string
     */
    public static function getHeaderSuperAttributePrice()
    {
        return static::$headerSuperAttributePrice;
    }

    /**
     * @return string
     */
    public static function getHeaderSuperProductSku()
    {
        return static::$headerSuperProductSku;
    }

    /**
     * @return string
     */
    public static function getHeaderTaxClassID()
    {
        return static::$headerTaxClassID;
    }

    /**
     * @return string
     */
    public static function getHeaderUpdatedAt()
    {
        return static::$headerUpdatedAt;
    }

    /**
     * @return string
     */
    public static function getHeaderVisibility()
    {
        return static::$headerVisibility;
    }
}
