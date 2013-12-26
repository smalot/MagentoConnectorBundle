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
    public function getWebservice(MagentoSoapClientParameters $clientParameters)
    {
        $client = new MagentoSoapClient($clientParameters);

        $magentoVersion = $this->getMagentoVersion($client);

        switch ($magentoVersion) {
            case 1.8:
                $magentoWebservice = new MagentoWebservice($client);
            break;
            case 1.7:
                $magentoWebservice = new MagentoWebservice($client);
            break;
            case 1.6:
                $magentoWebservice = new MagentoWebservice($client);
            break;
            default:
                $magentoWebservice = new MagentoWebservice($client);
        }

        return $magentoWebservice;
    }

    protected function getMagentoVersion($client)
    {
        return (float) $client->call('magento.info')['magento_version'];
    }
}
