<?php

namespace Pim\Bundle\MagentoConnectorBundle\Webservice;

use Pim\Bundle\CatalogBundle\Model\ProductInterface;

/**
 * A magento soap client to abstract interaction with the magento ee api
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class WebserviceEE extends Webservice
{
    /**
     * Get options for the given attribute
     *
     * @param string $attributeCode Attribute code
     *
     * @return array the formated options for the given attribute
     */
    public function getAttributeOptions($attributeCode)
    {
        if (!in_array($attributeCode, $this->getIgnoredAttributes())) {
            $options = $this->client->call(
                self::SOAP_ACTION_ATTRIBUTE_OPTION_LIST,
                array($attributeCode, self::ADMIN_STOREVIEW)
            );
        } else {
            $options = array();
        }

        $formatedOptions = array();

        foreach ($options as $option) {
            $formatedOptions[$option['label']] = $option['value'];
        }

        return $formatedOptions;
    }

    protected function getIgnoredAttributes()
    {
        return array(
            'is_returnable'
        );
    }
}
