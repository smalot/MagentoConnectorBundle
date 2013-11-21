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
    const LOGIN                   = 'login';
    const PASSWORD                = 'password';
    const URL                     = 'url';
    const BAD_ATTRIBUTE_SET_CODE  = 'bad';
    const GOOD_ATTRIBUTE_SET_CODE = 'good';
    const NAME                    = 'name';
    const SET_ID                  = 'set_id';
    const STORE_VIEW              = 'admin';

    /**
     * @var MagentoSoapClient
     */
    private $magentoSoapClient;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->magentoSoapClient = new MagentoSoapClient();
        $this->mockSoapClient = $this->getMockBuilder('\SoapClient')
            ->disableOriginalConstructor()
            ->setMethods(array('login', 'call', 'multiCall'))
            ->getMock();
        $this->clientParameters = new MagentoSoapClientParameters(self::LOGIN, self::PASSWORD, self::URL);
    }

    public function testIsConnectedInstanciated()
    {
        $this->assertFalse($this->magentoSoapClient->isConnected());
    }

    /**
     * @expectedException Pim\Bundle\MagentoConnectorBundle\Webservice\ConnectionErrorException
     */
    public function testConnectClientInstanciated()
    {
        $this->magentoSoapClient->connect();
    }

    /**
     * @expectedException Pim\Bundle\MagentoConnectorBundle\Webservice\ConnectionErrorException
     */
    public function testConnectClientWithoutParameters()
    {
        $this->magentoSoapClient->connect();
    }

    /**
     * @expectedException Pim\Bundle\MagentoConnectorBundle\Webservice\InvalidCredentialException
     */
    public function testConnectClientBadCredentials()
    {
        $this->mockSoapClient
            ->expects($this->once())
            ->method('login')
            ->with(
                self::LOGIN,
                self::PASSWORD
            )
            ->will($this->throwException(new \Exception('Bad credentials')));

        $this->magentoSoapClient->setParameters($this->clientParameters);
        $this->magentoSoapClient->setClient($this->mockSoapClient);

        $this->magentoSoapClient->connect();
    }

    public function testConnectClient()
    {
        $this->connectClient();
    }

    /**
     * @expectedException Pim\Bundle\MagentoConnectorBundle\Webservice\AttributeSetNotFoundException
     */
    public function testGetMagentoAttributeSetIdAttributeSetUnknow()
    {
        $this->connectClient();

        $this->mockSoapClient
            ->expects($this->once())
            ->method('call')
            ->with(
                true,
                MagentoSoapClient::SOAP_ACTION_PRODUCT_ATTRIBUTE_SET_LIST,
                null
            )
            ->will($this->returnValue(
                array(
                    array(
                        self::NAME   => self::GOOD_ATTRIBUTE_SET_CODE,
                        self::SET_ID => 1
                    )
                )
            ));

        $this->magentoSoapClient->getMagentoAttributeSetId(self::BAD_ATTRIBUTE_SET_CODE);
    }

    public function testGetMagentoAttributeSetId()
    {
        $this->connectClient();

        $this->mockSoapClient
            ->expects($this->once())
            ->method('call')
            ->with(
                true,
                MagentoSoapClient::SOAP_ACTION_PRODUCT_ATTRIBUTE_SET_LIST,
                null
            )
            ->will($this->returnValue(
                array(
                    array(
                        self::NAME   => self::GOOD_ATTRIBUTE_SET_CODE,
                        self::SET_ID => 1
                    )
                )
            ));

        $this->magentoSoapClient->getMagentoAttributeSetId(self::GOOD_ATTRIBUTE_SET_CODE);
    }

    public function testSetCurrentStoreView()
    {
        $this->connectClient();

        $this->mockSoapClient
            ->expects($this->once())
            ->method('call')
            ->with(
                true,
                MagentoSoapClient::SOAP_ACTION_CATALOG_PRODUCT_CURRENT_STORE,
                'admin'
            );

        $this->magentoSoapClient->setCurrentStoreView(self::STORE_VIEW);
    }

    /**
     * @expectedException Pim\Bundle\MagentoConnectorBundle\Webservice\NotConnectedException
     */
    public function testSendCallsInstanciated()
    {   
        $this->magentoSoapClient->addCall(array());

        $this->magentoSoapClient->sendCalls();
    }

    public function testSendCalls()
    {
        $this->connectClient();
        $this->magentoSoapClient->addCall(array());

        $this->mockSoapClient
            ->expects($this->once())
            ->method('multiCall')
            ->with(
                true,
                array(
                    array()
                )
            );

        $this->magentoSoapClient->sendCalls();
    }

    /**
     * @expectedException Pim\Bundle\MagentoConnectorBundle\Webservice\NotConnectedException
     */
    public function testCallInstanciated()
    {
        $this->magentoSoapClient->call(MagentoSoapClient::SOAP_ACTION_PRODUCT_ATTRIBUTE_SET_LIST);
    }

    private function connectClient()
    {
        $this->mockSoapClient
            ->expects($this->once())
            ->method('login')
            ->with(
                self::LOGIN,
                self::PASSWORD
            )->will($this->returnValue(true));

        $this->magentoSoapClient->setParameters($this->clientParameters);
        $this->magentoSoapClient->setClient($this->mockSoapClient);

        $this->magentoSoapClient->connect();
    }

}