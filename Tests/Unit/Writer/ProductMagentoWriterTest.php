<?php

namespace Pim\Bundle\MagentoConnectorBundle\Tests\Unit\Writer;

use Pim\Bundle\MagentoConnectorBundle\Writer\ProductWriter;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientParameters;
use Pim\Bundle\MagentoConnectorBundle\Webservice\Webservice;

/**
 * Test related class
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductWriterTest extends \PHPUnit_Framework_TestCase
{
    const LOGIN          = 'login';
    const PASSWORD       = 'password';
    const URL            = 'url';
    const CHANNEL        = 'channel';
    const DEFAULT_LOCALE = 'en_US';

    /**
     * Test the corresponding method
     */
    public function testWriteInstanciated()
    {
        $channelManagerMock           = $this->getChannelManagerMock();
        $webserviceGuesserMock = $this->getWebserviceGuesserMock();

        $writer = new ProductWriter($channelManagerMock, $webserviceGuesserMock);

        $products = array(array(
            array(
                Webservice::SOAP_DEFAULT_STORE_VIEW => array(
                    '1',
                    '1',
                    '1',
                    '1',
                    '1',
                ),
                self::DEFAULT_LOCALE                       => array(

                ),
                Webservice::IMAGES                  => array(

                )
            ),
            array(
                Webservice::SOAP_DEFAULT_STORE_VIEW => array(
                    '1',
                    '1',
                    '1'
                )
            )
        ));

        $writer->write($products);
    }

    /**
     * Test setters and getters
     */
    public function testSettersAndGetters()
    {
        $channelManagerMock           = $this->getChannelManagerMock();
        $webserviceGuesserMock = $this->getWebserviceGuesserMock();

        $writer = new ProductWriter($channelManagerMock, $webserviceGuesserMock);

        $writer->setSoapUsername(self::LOGIN);
        $writer->setSoapApiKey(self::PASSWORD);
        $writer->setSoapUrl(self::URL);
        $writer->setChannel(self::CHANNEL);

        $this->assertEquals($writer->getSoapUsername(), self::LOGIN);
        $this->assertEquals($writer->getSoapApiKey(), self::PASSWORD);
        $this->assertEquals($writer->getSoapUrl(), self::URL);
        $this->assertEquals($writer->getChannel(), self::CHANNEL);
    }

    /**
     * Get the channel manager mock
     * @return ChannelManagerMock
     */
    protected function getChannelManagerMock()
    {
        $channelManager = $this->getMockBuilder('Pim\Bundle\CatalogBundle\Manager\ChannelManager')
            ->disableOriginalConstructor()
            ->getMock();

        return $channelManager;
    }

    /**
     * Get a all settled ProductWriter
     * @param ChannelManager    $channelManager
     * @param WebserviceGuesser $webserviceGuesser
     *
     * @return ProductWriter
     */
    protected function getProductWriter(
        ChannelManager $channelManager,
        WebserviceGuesser $webserviceGuesser
    ) {
        $writer = new ProductWriter($channelManager, $webserviceGuesser);

        $writer->setSoapUsername(self::LOGIN);
        $writer->setSoapApiKey(self::PASSWORD);
        $writer->setSoapUrl(self::URL);
        $writer->setChannel(self::CHANNEL);

        return $writer;
    }

    /**
     * Get a WebserviceGuesser mock
     * @return WebserviceGuesserMock
     */
    protected function getWebserviceGuesserMock()
    {
        $webservice = $this->getMockBuilder('Pim\Bundle\MagentoConnectorBundle\Webservice\Webservice')
            ->disableOriginalConstructor()
            ->getMock();

        $webservice->expects($this->any())
            ->method('getImages')
            ->will($this->returnValue(array(array('file' => 'filename.jpg'))));

        $webserviceGuesserMock = $this->getMockBuilder(
            'Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser'
        )
        ->disableOriginalConstructor()
        ->getMock();

        $webserviceGuesserMock->expects($this->any())
            ->method('getWebservice')
            ->with(new MagentoSoapClientParameters(null, null, null))
            ->will($this->returnValue($webservice));

        return $webserviceGuesserMock;
    }

    /**
     * Test configurable fields
     */
    public function testGetConfigurationFields()
    {
        $channelManagerMock           = $this->getChannelManagerMock();
        $webserviceGuesserMock = $this->getWebserviceGuesserMock();

        $writer = new ProductWriter($channelManagerMock, $webserviceGuesserMock);

        $configurationFields = $writer->getConfigurationFields();

        $this->assertTrue(isset($configurationFields['soapUsername']));
        $this->assertTrue(isset($configurationFields['soapApiKey']));
        $this->assertTrue(isset($configurationFields['soapUrl']));
        $this->assertTrue(isset($configurationFields['channel']));
    }
}
