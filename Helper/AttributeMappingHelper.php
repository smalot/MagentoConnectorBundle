<?php

namespace Pim\Bundle\MagentoConnectorBundle\Helper;

/**
 * This attribute mapping helper allows to retrieve mapping between PIM and Magento attribute types
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeMappingHelper
{
    /**
     * Converts PIM attribute type to Magento attribute type
     *
     * @param string $pimAttributeType
     *
     * @return string|null
     */
    public function getMagentoAttributeTypeFor($pimAttributeType)
    {
        $type = null;
        $mapping = static::getMapping();

        if (array_key_exists($pimAttributeType, $mapping)) {
            $type = $mapping[$pimAttributeType];
        } else {
            $type = 'text';
        }

        return $type;
    }

    /**
     * Returns mapping between PIM and Magento attribute types
     *
     * @return array
     */
    protected static function getMapping()
    {
        return [
            'pim_catalog_simpleselect'     => 'select',
            'pim_catalog_multiselect'      => 'multiselect',
            'pim_catalog_identifier'       => null,
            'pim_catalog_metric'           => 'text',
            'pim_catalog_text'             => 'text',
            'pim_catalog_textarea'         => 'textarea',
            'pim_catalog_price_collection' => 'price',
            'pim_catalog_date'             => 'date',
            'pim_catalog_number'           => 'text',
            'pim_catalog_image'            => 'media_image',
            'pim_catalog_boolean'          => 'boolean',
            'pim_catalog_file'             => null
        ];
    }
}
