<?php

namespace Pim\Bundle\MagentoConnectorBundle\Normalizer;

/**
 * This dictionary allows to manage constants about API Import
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class LabelDictionary
{
    /** @staticvar string */
    const ATTRIBUTE_SET_HEADER = '_attribute_set';

    /** @staticvar string */
    const CATEGORY_HEADER = '_category';

    /** @staticvar string */
    const CATEGORY_ROOT_HEADER = '_root_category';

    /** @staticvar string */
    const CREATED_AT_HEADER = 'created_at';

    /** @staticvar string */
    const PRODUCT_TYPE_HEADER = '_type';

    /** @staticvar string */
    const PRODUCT_WEBSITE_HEADER = '_product_websites';

    /** @staticvar string */
    const SKU_HEADER = 'sku';

    /** @staticvar string */
    const STATUS_HEADER = 'status';

    /** @staticvar string */
    const STORE_HEADER = '_store';

    /** @staticvar string */
    const TAX_CLASS_ID_HEADER = 'tax_class_id';

    /** @staticvar string */
    const UPDATED_AT_HEADER = 'updated_at';

    /** @staticvar string */
    const VISIBILITY_HEADER = 'visibility';

    /** @staticvar string */
    const NAME_HEADER = 'name';

    /** @staticvar string */
    const DESCRIPTION_HEADER = 'description';

    /** @staticvar string */
    const SHORT_DESCRIPTION_HEADER = 'short_description';

    /** @staticvar string */
    const MEDIA_IMAGE_HEADER = '_media_image';

    /** @staticvar string */
    const MEDIA_DISABLED_HEADER = '_media_is_disabled';

    /** @staticvar string */
    const ASSOCIATION_REPLACE_SUBJECT_HEADER = '_links_#toReplace#_sku';

    /** @staticvar string */
    const ASSOCIATION_REPLACE_PATTERN_HEADER = '/#toReplace#/';

    /** @staticvar string */
    const SUPER_PRODUCT_SKU_HEADER = '_super_products_sku';

    /** @staticvar string */
    const SUPER_ATTRIBUTE_CODE_HEADER = '_super_attribute_code';

    /** @staticvar string */
    const SUPER_ATTRIBUTE_OPTION_HEADER = '_super_attribute_option';

    /** @staticvar string */
    const SUPER_ATTRIBUTE_PRICE_HEADER = '_super_attribute_price_corr';

    /** @staticvar string */
    const SIMPLE_PRODUCT_TYPE = 'simple';

    /** @staticvar string */
    const CONFIGURABLE_PRODUCT_TYPE = 'configurable';

    /**
     * Returns mandatory attributes needed to create the base product to update associations
     *
     * @return array
     */
    public static function getMandatoryAssociationAttributes()
    {
        return [
            static::SKU_HEADER,
            static::DESCRIPTION_HEADER,
            static::SHORT_DESCRIPTION_HEADER,
            static::NAME_HEADER
        ];
    }

    /**
     * Returns the header in terms of the type code and the pattern
     *
     * @param string $typeCode
     *
     * @return string
     */
    public static function getAssociationTypeHeader($typeCode)
    {
        return preg_replace(
            static::ASSOCIATION_REPLACE_PATTERN_HEADER,
            $typeCode,
            static::ASSOCIATION_REPLACE_SUBJECT_HEADER
        );
    }
}
