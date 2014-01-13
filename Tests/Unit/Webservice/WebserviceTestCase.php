<?php

namespace Pim\Bundle\MagentoConnectorBundle\Tests\Unit\Webservice;

use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClient;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientParameters;

/**
 * Tools for webservice tests classes
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class WebserviceTestCase extends \PHPUnit_Framework_TestCase
{
    const LOGIN    = 'login';
    const PASSWORD = 'password';
    const URL      = 'http://magento.dev/';

    /**
     * Get an instanciated ClientParameters
     * @return ClientParameters
     */
    protected function getClientParameters()
    {
        return new MagentoSoapClientParameters(self::LOGIN, self::PASSWORD, self::URL);
    }

    /**
     * Get a SoapClient mock
     * @return \SoapClientMock
     */
    protected function getSoapClientMock()
    {
        $soapClientMock = $this->getMockBuilder('\SoapClient')
            ->disableOriginalConstructor()
            ->setMethods(array('login', 'call', 'multiCall'))
            ->getMock();

        return $soapClientMock;
    }

    /**
     * Get a connected SoapClient
     * @return SoapClientMock
     */
    protected function getConnectedSoapClientMock()
    {
        $soapClientMock = $this->getSoapClientMock();

        $soapClientMock->expects($this->once())
            ->method('login')
            ->with(
                self::LOGIN,
                self::PASSWORD
            )
            ->will(
                $this->returnValue(
                    true
                )
            );

        return $soapClientMock;
    }

    /**
     * Get a connected MagentoSoapClient
     * @param SoapClient $soapClient
     *
     * @return MagentoSoapClient
     */
    protected function getConnectedMagentoSoapClient($soapClient)
    {
        $clientParameters = $this->getClientParameters();

        $magentoSoapClient = new MagentoSoapClient($clientParameters, $soapClient);

        return $magentoSoapClient;
    }

    /**
     * Get a connected MagentoSoapClientMock
     * @return MagentoSoapClientMock
     */
    protected function getConnectedMagentoSoapClientMock()
    {
        $magentoSoapClientMock = $this->getMockBuilder('Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClient')
            ->disableOriginalConstructor()
            ->getMock();

        return $magentoSoapClientMock;
    }
}
