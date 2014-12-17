<?php

namespace Pim\Bundle\MagentoConnectorBundle\Helper;

use Pim\Bundle\CatalogBundle\AttributeType\AbstractAttributeType;

/**
 * This attribute mapping helper allows to retrieve mapping between PIM and Magento attribute and backend types
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
     * @return string
     */
    public function getMagentoAttributeType($pimAttributeType)
    {
        $mapping = $this->getAttributeMapping();

        return isset($mapping[$pimAttributeType]) ? $mapping[$pimAttributeType] : 'text';
    }

    /**
     * Converts PIM backend type to Magento backend type
     *
     * @param string $pimBackendType
     *
     * @return string
     */
    public function getMagentoBackendType($pimBackendType)
    {
        $mapping = $this->getBackendMapping();

        return isset($mapping[$pimBackendType]) ? $mapping[$pimBackendType] : 'varchar';
    }

    /**
     * Returns mapping between PIM and Magento attribute types
     * There isn't identifier because it's mapped automatically with the Magento SKU
     *
     * @return array
     */
    protected function getAttributeMapping()
    {
        return [
            'pim_catalog_simpleselect'     => 'select',
            'pim_catalog_multiselect'      => 'multiselect',
            'pim_catalog_metric'           => 'text',
            'pim_catalog_text'             => 'text',
            'pim_catalog_textarea'         => 'textarea',
            'pim_catalog_price_collection' => 'price',
            'pim_catalog_date'             => 'date',
            'pim_catalog_number'           => 'text',
            'pim_catalog_image'            => 'media_image',
            'pim_catalog_boolean'          => 'boolean',
            'pim_catalog_file'             => 'text'
        ];
    }

    /**
     * Returns mapping between PIM and Magento backend types
     *
     * @return array
     */
    protected function getBackendMapping()
    {
        return [
            AbstractAttributeType::BACKEND_TYPE_BOOLEAN    => 'int',
            AbstractAttributeType::BACKEND_TYPE_DATE       => 'datetime',
            AbstractAttributeType::BACKEND_TYPE_DATETIME   => 'datetime',
            AbstractAttributeType::BACKEND_TYPE_DECIMAL    => 'decimal',
            AbstractAttributeType::BACKEND_TYPE_INTEGER    => 'int',
            AbstractAttributeType::BACKEND_TYPE_OPTIONS    => 'varchar',
            AbstractAttributeType::BACKEND_TYPE_OPTION     => 'varchar',
            AbstractAttributeType::BACKEND_TYPE_TEXT       => 'text',
            AbstractAttributeType::BACKEND_TYPE_VARCHAR    => 'varchar',
            AbstractAttributeType::BACKEND_TYPE_MEDIA      => 'varchar',
            AbstractAttributeType::BACKEND_TYPE_METRIC     => 'varchar',
            AbstractAttributeType::BACKEND_TYPE_PRICE      => 'varchar',
            AbstractAttributeType::BACKEND_TYPE_COLLECTION => 'varchar',
            AbstractAttributeType::BACKEND_TYPE_ENTITY     => 'varchar',
        ];
    }
}
