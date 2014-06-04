<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Webservice;

use PhpSpec\ObjectBehavior;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MagentoSoapClientParametersRegistrySpec extends ObjectBehavior
{
    function it_gives_a_single_instance_of_magento_soap_client_parameters()
    {
        $clientParameters = $this->getInstance('soap', 'apikey', 'magento.url', '/api/soap/?wsdl');
        $this->getInstance('soap', 'apikey', 'magento.url', '/api/soap/?wsdl')->shouldReturn($clientParameters);
    }
}