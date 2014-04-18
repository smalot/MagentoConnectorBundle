<?php

namespace Pim\Bundle\MagentoConnectorBundle\Webservice;

/**
 * A magento soap webservice that handle magento options
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OptionWebservice extends AbstractWebservice
{
    /**
     * Create an option
     * @param array $option
     */
    public function createOption($option)
    {
        $this->client->call(
            self::SOAP_ACTION_ATTRIBUTE_OPTION_ADD,
            $option
        );
    }

    /**
     * Delete an option
     * @param string $optionId
     * @param string $attributeCode
     */
    public function deleteOption($optionId, $attributeCode)
    {
        $this->client->call(
            self::SOAP_ACTION_ATTRIBUTE_OPTION_REMOVE,
            array(
                $attributeCode,
                $optionId,
            )
        );
    }
}