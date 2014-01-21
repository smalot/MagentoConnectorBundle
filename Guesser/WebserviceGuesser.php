<?php

namespace Pim\Bundle\MagentoConnectorBundle\Guesser;

use Pim\Bundle\MagentoConnectorBundle\Webservice\Webservice;
use Pim\Bundle\MagentoConnectorBundle\Webservice\Webservice16;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientParameters;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClient;

/**
 * A magento soap client to abstract interaction with the magento api
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class WebserviceGuesser extends AbstractGuesser
{
    /**
     * Get the Webservice corresponding to the given Magento parameters
     * @param MagentoSoapClientParameters $clientParameters
     *
     * @throws NotSupportedVersionException If the magento version is not supported
     * @return Webservice
     */
    public function getWebservice(MagentoSoapClientParameters $clientParameters)
    {
        $client = new MagentoSoapClient($clientParameters);

        $magentoVersion = $this->getMagentoVersion($client);

        switch ($magentoVersion) {
            case AbstractGuesser::MAGENTO_VERSION_1_8:
            case AbstractGuesser::MAGENTO_VERSION_1_7:
                $webservice = new Webservice($client);
                break;
            case AbstractGuesser::MAGENTO_VERSION_1_6:
                $webservice = new Webservice16($client);
                break;
            default:
                throw new NotSupportedVersionException(AbstractGuesser::MAGENTO_VERSION_NOT_SUPPORTED_MESSAGE);
        }

        return $webservice;
    }
}
