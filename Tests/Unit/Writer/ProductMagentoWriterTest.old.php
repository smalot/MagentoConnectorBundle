<?php

namespace Pim\Bundle\MagentoConnectorBundle\Tests\Unit\Writer;

use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClient;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientParameters;
use Pim\Bundle\MagentoConnectorBundle\Writer\ProductMagentoWriter;

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
    const IMAGES         = 'images';
    const SKU            = 'sku';
    const IMAGE_FILENAME = 'test.jpeg';
    const JPEG_TYPE_MIME = 'image/jpeg';

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->channelManager = $this->getMockBuilder('Pim\Bundle\CatalogBundle\Manager\ChannelManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->magentoSoapClient = $this->getMock('Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClient');
        $this->magentoSoapClient->expects($this->any())
            ->method('getImages')
            ->will($this->returnValue(array()));


        $this->writer = new ProductMagentoWriter($this->channelManager, $this->magentoSoapClient);

        $this->writer->setSoapUsername(self::LOGIN);
        $this->writer->setSoapApiKey(self::PASSWORD);
        $this->writer->setSoapUrl(self::URL);
        $this->writer->setChannel(self::CHANNEL);
    }

    public function testWriteInstanciated()
    {
        $this->channelManager = $this->getChannelManagerMock();

        $this->writer = new ProductMagentoWriter($this->channelManager, $this->magentoSoapClient);

        $this->writer->setSoapUsername(self::LOGIN);
        $this->writer->setSoapApiKey(self::PASSWORD);
        $this->writer->setSoapUrl(self::URL);
        $this->writer->setChannel(self::CHANNEL);

        $items = array(array(array(
            MagentoSoapClient::SOAP_DEFAULT_STORE_VIEW => array(
                '1',
                '1',
                '1',
                '1',
                '1',
            ),
            self::DEFAULT_LOCALE                       => array(

            )
        )));

        $this->writer->write($items);
    }

    public function testWrite()
    {
        $this->channelManager = $this->getChannelManagerMock();

        $this->writer = new ProductMagentoWriter($this->channelManager, $this->magentoSoapClient);

        $this->writer->setSoapUsername(self::LOGIN);
        $this->writer->setSoapApiKey(self::PASSWORD);
        $this->writer->setSoapUrl(self::URL);
        $this->writer->setChannel(self::CHANNEL);

        $items = array(array(array(
            MagentoSoapClient::SOAP_DEFAULT_STORE_VIEW => array(
                '1',
                '1',
                '1',
            ),
            self::DEFAULT_LOCALE                       => array(

            ),
            self::IMAGES                               => array(
                array(
                    self::SKU,
                    array(
                        'file' => array(
                            'name' => self::IMAGE_FILENAME,
                            'content' => '',
                            'mime'    => self::JPEG_TYPE_MIME
                        ),
                        'label'    => self::IMAGE_FILENAME,
                        'position' => 0,
                        'types'    => array(MagentoSoapClient::SMALL_IMAGE),
                        'exclude'  => 0
                    )
                )
            )
        )));

        $this->writer->write($items);
    }

    protected function getChannelManagerMock()
    {
        $channelManager = $this->getMockBuilder('Pim\Bundle\CatalogBundle\Manager\ChannelManager')
            ->disableOriginalConstructor()
            ->getMock();

        $locale = $this->getMockBuilder('Pim\Bundle\CatalogBundle\Entity\Locale')
            ->disableOriginalConstructor()
            ->setMethods(array('getCode'))
            ->getMock();

        $locale->expects($this->any())
            ->method('getCode')
            ->will($this->returnValue(self::DEFAULT_LOCALE));

        $channel = $this->getMockBuilder('Pim\Bundle\CatalogBundle\Entity\Channel')
            ->disableOriginalConstructor()
            ->setMethods(array('getLocales'))
            ->getMock();
        $channel->expects($this->any())
            ->method('getLocales')
            ->will($this->returnValue(array($locale)));

        $channelManager
            ->expects($this->any())
            ->method('getChannels')
            ->with(array('code' => self::CHANNEL))
            ->will($this->returnValue(array($channel)));

        return $channelManager;
    }

    public function testGetConfigurationFields()
    {
        $configurationFields = $this->writer->getConfigurationFields();

        $this->assertTrue(isset($configurationFields['soapUsername']));
        $this->assertTrue(isset($configurationFields['soapApiKey']));
        $this->assertTrue(isset($configurationFields['soapUrl']));
        $this->assertTrue(isset($configurationFields['channel']));
    }

    public function testSettersAndGetters()
    {
        $this->assertEquals($this->writer->getSoapUsername(), self::LOGIN);
        $this->assertEquals($this->writer->getSoapApiKey(), self::PASSWORD);
        $this->assertEquals($this->writer->getSoapUrl(), self::URL);
        $this->assertEquals($this->writer->getChannel(), self::CHANNEL);
    }
}
