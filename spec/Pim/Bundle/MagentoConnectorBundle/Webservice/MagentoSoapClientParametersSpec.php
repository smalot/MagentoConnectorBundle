<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Webservice;

use PhpSpec\ObjectBehavior;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MagentoSoapClientParametersSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('username', 'api_key', 'http://magento.url', '/api/soap/?wsdl', 'default');
    }

    function it_has_configuration()
    {
        $hash = md5('username' . 'api_key' . 'http://magento.url' . '/api/soap/?wsdl' . 'default' . null . null);
        $this->setValidation(true);

        $this->getHash()->shouldReturn($hash);
        $this->isValid()->shouldReturn(true);
        $this->getSoapUsername()->shouldReturn('username');
        $this->getSoapApiKey()->shouldReturn('api_key');
        $this->getSoapUrl()->shouldReturn('http://magento.url/api/soap/?wsdl');
        $this->getWsdlUrl()->shouldReturn('/api/soap/?wsdl');
        $this->getMagentoUrl()->shouldReturn('http://magento.url');
        $this->getDefaultStoreView()->shouldReturn('default');
        $this->getHttpLogin()->shouldReturn(null);
        $this->getHttpPassword()->shouldReturn(null);
    }
}
