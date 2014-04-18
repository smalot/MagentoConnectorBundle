<?php

namespace Pim\Bundle\MagentoConnectorBundle\Webservice;

/**
 * A magento soap webservice that handle magento attributes
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class AttributeWebservice extends AbstractWebservice
{
    /**
     * Get attribute options for all attributes
     *
     */
    public function getAllAttributesOptions()
    {
        $attributeList = $this->getAllAttributes();

        foreach ($attributeList as $attributeCode => $attribute) {
            if (in_array($attribute['type'], array(self::SELECT, self::MULTI_SELECT))) {
                if (!isset($this->attributeOptionList[$attributeCode])) {
                    $this->attributeOptionList[$attributeCode] = $this->getAttributeOptions($attributeCode);
                }
            }
        }

        return $this->attributeOptionList;
    }

    /**
     * Get all attributes from magento
     *
     * @return array
     */
    public function getAllAttributes()
    {
        if (!$this->attributeList) {
            $attributeSetList = $this->getAttributeSetList();
            foreach (array_keys($attributeSetList) as $attributeSet) {
                $attributes = $this->getAttributeList($attributeSet);
                $this->attributeSetList[$attributeSet] = array();

                foreach ($attributes as $attribute) {
                    $this->attributeList[$attribute['code']]                = $attribute;
                    $this->attributeSetList[$attributeSet][$attributeSet]   = $attribute['code'];
                }
            }
        }

        return $this->attributeList;
    }

    /**
     * Get attribute list for a given attribute set code
     *
     * @param string $attributeSetCode the attribute set code
     *
     * @return array
     */
    public function getAttributeList($attributeSetCode)
    {
        if (!isset($this->attributes[$attributeSetCode])) {
            $id = $this->getAttributeSetId($attributeSetCode);

            $this->attributes[$attributeSetCode] = $this->client->call(
                self::SOAP_ACTION_PRODUCT_ATTRIBUTE_LIST,
                $id
            );
        }

        return $this->attributes[$attributeSetCode];
    }

    /**
     * Get magento attributeSets from the magento api
     * @param string $code the attributeSet id
     *
     * @throws AttributeSetNotFoundException If If the attribute doesn't exist on Magento side
     * @return void
     */
    public function getAttributeSetId($code)
    {
        $this->getAttributeSetList();

        if (isset($this->magentoAttributeSets[$code])) {
            return $this->magentoAttributeSets[$code];
        } else {
            throw new AttributeSetNotFoundException(
                'The attribute set for code "' . $code . '" was not found on Magento. Please create it before proceed.'
            );
        }
    }

    /**
     * Create an attribute
     * @param array $attribute
     */
    public function createAttribute($attribute)
    {
        $this->client->call(
            self::SOAP_ACTION_ATTRIBUTE_CREATE,
            array($attribute)
        );
    }

    /**
     * Update an attribute
     * @param array $attribute
     */
    public function updateAttribute($attribute)
    {
        $this->client->call(
            self::SOAP_ACTION_ATTRIBUTE_UPDATE,
            $attribute
        );
    }

    /**
     * Delete an attribute
     * @param string $attributeCode
     */
    public function deleteAttribute($attributeCode)
    {
        $this->client->call(
            self::SOAP_ACTION_ATTRIBUTE_REMOVE,
            $attributeCode
        );
    }

    /**
     * Get options for the given attribute
     *
     * @param string $attributeCode Attribute code
     *
     * @return array the formated options for the given attribute
     */
    public function getAttributeOptions($attributeCode)
    {
        $options = $this->client->call(
            self::SOAP_ACTION_ATTRIBUTE_OPTION_LIST,
            array($attributeCode, self::ADMIN_STOREVIEW)
        );

        $formatedOptions = array();

        foreach ($options as $option) {
            $formatedOptions[$option['label']] = $option['value'];
        }

        return $formatedOptions;
    }

    /**
     * Get the magento attributeSet list from the magento platform
     *
     * @return void
     */
    protected function getAttributeSetList()
    {
        // On first call we get the magento attribute set list
        // (to bind them with our product's families)
        if (!$this->magentoAttributeSets) {
            $attributeSets = $this->client->call(
                self::SOAP_ACTION_PRODUCT_ATTRIBUTE_SET_LIST
            );

            foreach ($attributeSets as $attributeSet) {
                $this->magentoAttributeSets[$attributeSet['name']] =
                    $attributeSet['set_id'];
            }
        }

        return $this->magentoAttributeSets;
    }
}
