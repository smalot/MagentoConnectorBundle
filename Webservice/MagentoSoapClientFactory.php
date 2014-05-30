<?php

namespace Pim\Bundle\MagentoConnectorBundle\Webservice;

/**
 * A magento soap client factory
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MagentoSoapClientFactory
{
    /**
     * Get a new magento soap client
     * @param MagentoSoapClientParametersRegistry $clientParameters
     *
     * @return MagentoSoapClient
     */
    public function getMagentoSoapClient(MagentoSoapClientParameters $clientParameters)
    {
        return new MagentoSoapClient($clientParameters);
    }
}
