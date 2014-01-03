<?php

namespace Pim\Bundle\MagentoConnectorBundle\Webservice;

/**
 * A magento soap client to abstract interaction with the magento api
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MagentoWebserviceGuesser
{
    /**
     * Get the MagentoWebservice corresponding to the given Magento parameters
     * @param  MagentoSoapClientParameters $clientParameters
     * @return MagentoWebservice
     */
    public function getWebservice(MagentoSoapClientParameters $clientParameters)
    {
        $client = new MagentoSoapClient($clientParameters);

        $magentoVersion = $this->getMagentoVersion($client);

        switch ($magentoVersion) {
            case '1.8':
            case '1.7':
                $magentoWebservice = new MagentoWebservice($client);
            break;
            case '1.6':
                $magentoWebservice = new MagentoWebservice16($client);
            break;
            default:
                throw new NotSupportedVersionException('Your Magento version is not supported yet.');
        }

        return $magentoWebservice;
    }

    /**
     * Get the Magento version for the given client
     * @param  MagentoSoapClient $client
     * @return float
     */
    protected function getMagentoVersion(MagentoSoapClient $client)
    {
        $magentoVersion = $client->call('magento.info')['magento_version'];

        $pattern = '/^(?P<version>[0-9]\.[0-9])/';

        if (preg_match($pattern, $magentoVersion, $matches)){
            return $matches['version'];
        } else {
            return $magentoVersion;
        }
    }
}
