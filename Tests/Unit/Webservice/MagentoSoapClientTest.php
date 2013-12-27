<?php

namespace Pim\Bundle\MagentoConnectorBundle\Tests\Unit\Webservice;

use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClient;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientParameters;

/**
 * Test related class
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MagentoSoapClientTest extends \PHPUnit_Framework_TestCase
{
    const LOGIN    = 'login';
    const PASSWORD = 'password';
    const URL      = 'http://magento.dev/';

    public function testConnect()
    {
        $soapClientMock    = $this->getConnectedSoapClientMock();
        $magentoSoapClient = $this->getConnectedMagentoSoapClient($soapClientMock);

        $this->assertTrue($magentoSoapClient->isConnected());
    }

    /**
     * @expectedException Pim\Bundle\MagentoConnectorBundle\Webservice\InvalidCredentialException
     */
    public function testConnectWrongCredentials()
    {
        $clientParameters = $this->getClientParameters();
        $soapClientMock   = $this->getConnectedSoapClientMock();

        $soapClientMock->expects($this->once())
            ->method('login')
            ->with(
                self::LOGIN,
                self::PASSWORD
            )
            ->will($this->throwException(
                new \Exception()
            ));

        $magentoSoapClient = new MagentoSoapClient($clientParameters, $soapClientMock);
    }

    /**
     * @expectedException Pim\Bundle\MagentoConnectorBundle\Webservice\InvalidCredentialException
     */
    public function testConnectConnexionErrorException()
    {
        $clientParameters = $this->getClientParameters();

        $magentoSoapClient = new MagentoSoapClient($clientParameters);
    }

    public function testCall()
    {
        $soapClientMock    = $this->getConnectedSoapClientMock();
        $magentoSoapClient = $this->getConnectedMagentoSoapClient($soapClientMock);

        $magentoSoapClient->call('test');
    }

    public function testAddCall()
    {
        $soapClientMock    = $this->getConnectedSoapClientMock();
        $soapClientMock->expects($this->once())
            ->method('multiCall')
            ->will($this->returnValue(array('response')));
        $magentoSoapClient = $this->getConnectedMagentoSoapClient($soapClientMock);

        $magentoSoapClient->addCall(array(), 1);
    }

    public function testSendCalls()
    {
        $soapClientMock    = $this->getConnectedSoapClientMock();
        $soapClientMock->expects($this->once())
            ->method('multiCall')
            ->will($this->returnValue(array('response')));
        $magentoSoapClient = $this->getConnectedMagentoSoapClient($soapClientMock);

        $magentoSoapClient->addCall(array());

        $magentoSoapClient->sendCalls();
    }

    protected function getClientParameters()
    {
        return new MagentoSoapClientParameters(self::LOGIN, self::PASSWORD, self::URL);
    }

    protected function getSoapClientMock()
    {
        $soapClientMock = $this->getMockBuilder('\SoapClient')
            ->disableOriginalConstructor()
            ->setMethods(array('login', 'call', 'multiCall'))
            ->getMock();

        return $soapClientMock;
    }

    protected function getConnectedSoapClientMock()
    {
        $soapClientMock = $this->getSoapClientMock();

        $soapClientMock->expects($this->once())
            ->method('login')
            ->with(
                self::LOGIN,
                self::PASSWORD
            )
            ->will($this->returnValue(
                true
            ));

        return $soapClientMock;
    }

    protected function getConnectedMagentoSoapClient($soapClient)
    {
        $clientParameters = $this->getClientParameters();

        $magentoSoapClient = new MagentoSoapClient($clientParameters, $soapClient);

        return $magentoSoapClient;
    }
}
