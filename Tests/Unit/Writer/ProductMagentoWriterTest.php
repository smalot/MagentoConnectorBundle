<?php

namespace Pim\Bundle\MagentoConnectorBundle\Tests\Unit\Writer;

use Pim\Bundle\MagentoConnectorBundle\Writer\ProductMagentoWriter;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientParameters;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoWebservice;

/**
 * Test related class
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductMagentoWriterTest extends \PHPUnit_Framework_TestCase
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
        $magentoWebserviceGuesserMock = $this->getMagentoWebserviceGuesserMock();

        $writer = new ProductMagentoWriter($channelManagerMock, $magentoWebserviceGuesserMock);

        $products = array(array(
            array(
                MagentoWebservice::SOAP_DEFAULT_STORE_VIEW => array(
                    '1',
                    '1',
                    '1',
                    '1',
                    '1',
                ),
                self::DEFAULT_LOCALE                       => array(

                ),
                MagentoWebservice::IMAGES                  => array(

                )
            ),
            array(
                MagentoWebservice::SOAP_DEFAULT_STORE_VIEW => array(
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
        $magentoWebserviceGuesserMock = $this->getMagentoWebserviceGuesserMock();

        $writer = new ProductMagentoWriter($channelManagerMock, $magentoWebserviceGuesserMock);

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
     * Get a all settled ProductMagentoWriter
     * @param  ChannelManager           $channelManager
     * @param  MagentoWebserviceGuesser $magentoWebserviceGuesser
     * @return ProductMagentoWriter
     */
    protected function getProductMagentoWriter(
        ChannelManager $channelManager,
        MagentoWebserviceGuesser $magentoWebserviceGuesser
    ) {
        $writer = new ProductMagentoWriter($channelManager, $magentoWebserviceGuesser);

        $writer->setSoapUsername(self::LOGIN);
        $writer->setSoapApiKey(self::PASSWORD);
        $writer->setSoapUrl(self::URL);
        $writer->setChannel(self::CHANNEL);

        return $writer;
    }

    /**
     * Get a MagentoWebserviceGuesser mock
     * @return MagentoWebserviceGuesserMock
     */
    protected function getMagentoWebserviceGuesserMock()
    {
        $magentoWebservice = $this->getMockBuilder('Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoWebservice')
            ->disableOriginalConstructor()
            ->getMock();

        $magentoWebservice->expects($this->any())
            ->method('getImages')
            ->will($this->returnValue(array(array('file' => 'filename.jpg'))));

        $magentoWebserviceGuesserMock = $this->getMockBuilder(
            'Pim\Bundle\MagentoConnectorBundle\Guesser\MagentoWebserviceGuesser'
        )
        ->disableOriginalConstructor()
        ->getMock();

        $magentoWebserviceGuesserMock->expects($this->any())
            ->method('getWebservice')
            ->with(new MagentoSoapClientParameters(null, null, null))
            ->will($this->returnValue($magentoWebservice));

        return $magentoWebserviceGuesserMock;
    }

    /**
     * Test configurable fields
     */
    public function testGetConfigurationFields()
    {
        $channelManagerMock           = $this->getChannelManagerMock();
        $magentoWebserviceGuesserMock = $this->getMagentoWebserviceGuesserMock();

        $writer = new ProductMagentoWriter($channelManagerMock, $magentoWebserviceGuesserMock);

        $configurationFields = $writer->getConfigurationFields();

        $this->assertTrue(isset($configurationFields['soapUsername']));
        $this->assertTrue(isset($configurationFields['soapApiKey']));
        $this->assertTrue(isset($configurationFields['soapUrl']));
        $this->assertTrue(isset($configurationFields['channel']));
    }
}
