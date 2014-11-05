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
    const HEADER_ATTRIBUTE_SET = '_attribute_set';

    /** @staticvar string */
    const HEADER_CATEGORY = '_category';

    /** @staticvar string */
    const HEADER_CATEGORY_ROOT = '_root_category';

    /** @staticvar string */
    const HEADER_CREATED_AT = 'created_at';

    /** @staticvar string */
    const HEADER_PRODUCT_TYPE = '_type';

    /** @staticvar string */
    const HEADER_PRODUCT_WEBSITE = '_product_websites';

    /** @staticvar string */
    const HEADER_SKU = 'sku';

    /** @staticvar string */
    const HEADER_STATUS = 'status';

    /** @staticvar string */
    const HEADER_STORE = '_store';

    /** @staticvar string */
    const HEADER_TAX_CLASS_ID = 'tax_class_id';

    /** @staticvar string */
    const HEADER_UPDATED_AT = 'updated_at';

    /** @staticvar string */
    const HEADER_VISIBILITY = 'visibility';

    /** @staticvar string */
    const HEADER_NAME = 'name';

    /** @staticvar string */
    const HEADER_DESCRIPTION = 'description';

    /** @staticvar string */
    const HEADER_SHORT_DESCRIPTION = 'short_description';

    /** @staticvar string */
    const HEADER_ASSOCIATION_REPLACE_SUBJECT = '_links_#_sku';

    /** @staticvar string */
    const HEADER_ASSOCIATION_REPLACE_PATTERN = '/#/';

    /** @staticvar string */
    const HEADER_SUPER_PRODUCT_SKU = '_super_products_sku';

    /** @staticvar string */
    const HEADER_SUPER_ATTRIBUTE_CODE = '_super_attribute_code';

    /** @staticvar string */
    const HEADER_SUPER_ATTRIBUTE_OPTION = '_super_attribute_option';

    /** @staticvar string */
    const HEADER_SUPER_ATTRIBUTE_PRICE = '_super_attribute_price_corr';

    /** @staticvar string */
    const PRODUCT_TYPE_SIMPLE = 'simple';

    /** @staticvar string */
    const PRODUCT_TYPE_CONFIGURABLE = 'configurable';

    /**
     * Returns mandatory attributes needed to create the base product to update associations
     *
     * @return array
     */
    public function getMandatoryAttributeCodesForAssociations()
    {
        return [
            static::HEADER_SKU,
            static::HEADER_DESCRIPTION,
            static::HEADER_SHORT_DESCRIPTION,
            static::HEADER_NAME
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
            static::HEADER_ASSOCIATION_REPLACE_PATTERN,
            $typeCode,
            static::HEADER_ASSOCIATION_REPLACE_SUBJECT
        );
    }
}
