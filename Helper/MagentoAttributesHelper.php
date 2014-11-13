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
    protected static $attributeSetHeader = '_attribute_set';

    /** @staticvar string */
    protected static $categoryHeader = '_category';

    /** @staticvar string */
    protected static $categoryRootHeader = '_root_category';

    /** @staticvar string */
    protected static $createdAtHeader = 'created_at';

    /** @staticvar string */
    protected static $productTypeHeader = '_type';

    /** @staticvar string */
    protected static $productWebsiteHeader = '_product_websites';

    /** @staticvar string */
    protected static $skuHeader = 'sku';

    /** @staticvar string */
    protected static $statusHeader = 'status';

    /** @staticvar string */
    protected static $storeHeader = '_store';

    /** @staticvar string */
    protected static $taxClassIDHeader = 'tax_class_id';

    /** @staticvar string */
    protected static $updatedAtHeader = 'updated_at';

    /** @staticvar string */
    protected static $visibilityHeader = 'visibility';

    /** @staticvar string */
    protected static $nameHeader = 'name';

    /** @staticvar string */
    protected static $descriptionHeader = 'description';

    /** @staticvar string */
    protected static $shortDescriptionHeader = 'short_description';

    /** @staticvar string */
    protected static $associationReplaceSubjectHeader = '_links_#toReplace#_sku';

    /** @staticvar string */
    protected static $associationReplacePatternHeader = '/#toReplace#/';

    /** @staticvar string */
    protected static $superProductSkuHeader = '_super_products_sku';

    /** @staticvar string */
    protected static $superAttributeCodeHeader = '_super_attribute_code';

    /** @staticvar string */
    protected static $superAttributeOptionHeader = '_super_attribute_option';

    /** @staticvar string */
    protected static $superAttributePriceHeader = '_super_attribute_price_corr';

    /** @staticvar string */
    protected static $simpleProductType = 'simple';

    /** @staticvar string */
    protected static $configurableProductType = 'configurable';

    /**
     * Returns mandatory attributes needed to create the base product to update associations
     *
     * @return array
     */
    public function getMandatoryAttributeCodesForAssociations()
    {
        return [
            $this->getSkuHeader(),
            $this->getDescriptionHeader(),
            $this->getShortDescriptionHeader(),
            $this->getNameHeader()
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
            $this->getAssociationReplacePatternHeader(),
            $typeCode,
            $this->getAssociationReplaceSubjectHeader()
        );
    }

    /**
     * @return string
     */
    public static function getConfigurableProductType()
    {
        return static::$configurableProductType;
    }

    /**
     * @return string
     */
    public static function getSimpleProductType()
    {
        return static::$simpleProductType;
    }

    /**
     * @return string
     */
    public static function getAssociationReplacePatternHeader()
    {
        return static::$associationReplacePatternHeader;
    }

    /**
     * @return string
     */
    public static function getAssociationReplaceSubjectHeader()
    {
        return static::$associationReplaceSubjectHeader;
    }

    /**
     * @return string
     */
    public static function getAttributeSetHeader()
    {
        return static::$attributeSetHeader;
    }

    /**
     * @return string
     */
    public static function getCategoryHeader()
    {
        return static::$categoryHeader;
    }

    /**
     * @return string
     */
    public static function getCategoryRootHeader()
    {
        return static::$categoryRootHeader;
    }

    /**
     * @return string
     */
    public static function getCreatedAtHeader()
    {
        return static::$createdAtHeader;
    }

    /**
     * @return string
     */
    public static function getDescriptionHeader()
    {
        return static::$descriptionHeader;
    }

    /**
     * @return string
     */
    public static function getNameHeader()
    {
        return static::$nameHeader;
    }

    /**
     * @return string
     */
    public static function getProductTypeHeader()
    {
        return static::$productTypeHeader;
    }

    /**
     * @return string
     */
    public static function getProductWebsiteHeader()
    {
        return static::$productWebsiteHeader;
    }

    /**
     * @return string
     */
    public static function getShortDescriptionHeader()
    {
        return static::$shortDescriptionHeader;
    }

    /**
     * @return string
     */
    public static function getSkuHeader()
    {
        return static::$skuHeader;
    }

    /**
     * @return string
     */
    public static function getStatusHeader()
    {
        return static::$statusHeader;
    }

    /**
     * @return string
     */
    public static function getStoreHeader()
    {
        return static::$storeHeader;
    }

    /**
     * @return string
     */
    public static function getSuperAttributeCodeHeader()
    {
        return static::$superAttributeCodeHeader;
    }

    /**
     * @return string
     */
    public static function getSuperAttributeOptionHeader()
    {
        return static::$superAttributeOptionHeader;
    }

    /**
     * @return string
     */
    public static function getSuperAttributePriceHeader()
    {
        return static::$superAttributePriceHeader;
    }

    /**
     * @return string
     */
    public static function getSuperProductSkuHeader()
    {
        return static::$superProductSkuHeader;
    }

    /**
     * @return string
     */
    public static function getTaxClassIDHeader()
    {
        return static::$taxClassIDHeader;
    }

    /**
     * @return string
     */
    public static function getUpdatedAtHeader()
    {
        return static::$updatedAtHeader;
    }

    /**
     * @return string
     */
    public static function getVisibilityHeader()
    {
        return static::$visibilityHeader;
    }
}
