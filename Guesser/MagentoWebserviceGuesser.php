<?php

namespace Pim\Bundle\MagentoConnectorBundle\Guesser;

use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoWebservice;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoWebservice16;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientParameters;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClient;

/**
 * A magento soap client to abstract interaction with the magento api
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MagentoWebserviceGuesser extends MagentoGuesser
{
    /**
     * Get the MagentoWebservice corresponding to the given Magento parameters
     * @param  MagentoSoapClientParameters  $clientParameters
     * @throws NotSupportedVersionException If the magento version is not supported
     * @return MagentoWebservice
     */
    public function getWebservice(MagentoSoapClientParameters $clientParameters)
    {
        $client = new MagentoSoapClient($clientParameters);

        $magentoVersion = $this->getMagentoVersion($client);

        switch ($magentoVersion) {
            case MagentoGuesser::MAGENTO_VERSION_1_8:
            case MagentoGuesser::MAGENTO_VERSION_1_7:
                $magentoWebservice = new MagentoWebservice($client);
                break;
            case MagentoGuesser::MAGENTO_VERSION_1_6:
                $magentoWebservice = new MagentoWebservice16($client);
                break;
            default:
                throw new NotSupportedVersionException('Your Magento version is not supported yet.');
        }

        return $magentoWebservice;
    }
}
