<?php

namespace Pim\Bundle\MagentoConnectorBundle\Tests\Unit\Processor;

use Pim\Bundle\MagentoConnectorBundle\Processor\ProductMagentoProcessor;
use Pim\Bundle\MagentoConnectorBundle\Webservice\AttributeSetNotFoundException;

/**
 * Test related class
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductMagentoProcessorTest extends \PHPUnit_Framework_TestCase
{
    const LOGIN             = 'login';
    const PASSWORD          = 'password';
    const URL               = 'url';
    const CHANNEL           = 'channel';
    const PRICE             = '13.37';
    const NAME              = 'Product example';
    const DESCRIPTION       = 'Description';
    const SHORT_DESCRIPTION = 'Short description';
    const WEIGHT            = '10';
    const STATUS            = 1;
    const VISIBILITY        = 4;
    const TAX_CLASS_ID      = 0;
    const ATTRIBUTE_NAME    = 'name';

    const DEFAULT_LOCALE    = 'en_US';

    protected $channelManager;
    protected $magentoSoapClient;
    protected $processor;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->channelManager = $this->getMockBuilder('Pim\Bundle\CatalogBundle\Manager\ChannelManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->magentoSoapClient = $this->getMock('Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClient');

        $this->processor = new ProductMagentoProcessor(
            $this->channelManager,
            $this->magentoSoapClient
        );

        $this->processor->setSoapUsername(self::LOGIN);
        $this->processor->setSoapApiKey(self::PASSWORD);
        $this->processor->setSoapUrl(self::URL);
        $this->processor->setChannel(self::CHANNEL);
    }

    /**
     * Test instance of current instance tested
     */
    public function testInstanceOfMagentoProductProcessor()
    {
        $this->assertInstanceOf(
            'Pim\\Bundle\\MagentoConnectorBundle\\Processor\\ProductMagentoProcessor',
            $this->processor
        );
    }

    public function testProcess()
    {
        $product = $this->getProductMock();
        $this->channelManager = $this->getChannelManagerMock();

        $this->magentoSoapClient
            ->expects($this->once())
            ->method('getAttributeList')
            ->will($this->returnValue(
                array(
                    array(
                        'code' => 'name',
                        'required' => '1',
                        'scope' => 'store'
                    ),
                    array(
                        'code' => 'description',
                        'required' => '1',
                        'scope' => 'store'
                    ),
                    array(
                        'code' => 'short_description',
                        'required' => '1',
                        'scope' => 'store'
                    ),
                    array(
                        'code' => 'sku',
                        'required' => '1',
                        'scope' => 'global'
                    ),
                    array(
                        'code' => 'weight',
                        'required' => '1',
                        'scope' => 'global'
                    ),
                    array(
                        'code' => 'status',
                        'required' => '1',
                        'scope' => 'website'
                    ),
                    array(
                        'code' => 'visibility',
                        'required' => '1',
                        'scope' => 'store'
                    ),
                    array(
                        'code' => 'created_at',
                        'required' => '1',
                        'scope' => 'global'
                    ),
                    array(
                        'code' => 'updated_at',
                        'required' => '1',
                        'scope' => 'global'
                    ),
                    array(
                        'code' => 'price_type',
                        'required' => '1',
                        'scope' => 'global'
                    ),
                    array(
                        'code' => 'sku_type',
                        'required' => '1',
                        'scope' => 'global'
                    ),
                    array(
                        'code' => 'weight_type',
                        'required' => '1',
                        'scope' => 'global'
                    ),
                    array(
                        'code' => 'shipment_type',
                        'required' => '1',
                        'scope' => 'global'
                    ),
                    array(
                        'code' => 'links_purchased_separately',
                        'required' => '1',
                        'scope' => 'global'
                    ),
                    array(
                        'code' => 'samples_title',
                        'required' => '1',
                        'scope' => 'store'
                    ),
                    array(
                        'code' => 'links_title',
                        'required' => '1',
                        'scope' => 'store'
                    ),
                    array(
                        'code' => 'price',
                        'required' => '1',
                        'scope' => 'website'
                    ),
                    array(
                        'code' => 'tax_class_id',
                        'required' => '1',
                        'scope' => 'website'
                    ),
                    array(
                        'code' => 'price_view',
                        'required' => '1',
                        'scope' => 'global'
                    )
                )
            ));

        $processor = new ProductMagentoProcessor(
            $this->channelManager,
            $this->magentoSoapClient
        );

        $processor->setSoapUsername(self::LOGIN);
        $processor->setSoapApiKey(self::PASSWORD);
        $processor->setSoapUrl(self::URL);
        $processor->setChannel(self::CHANNEL);



        $processor->process(array($product));
    }

    /**
     * @expectedException Oro\Bundle\BatchBundle\Item\InvalidItemException
     */
    public function testProcessAttributeSetNotFound()
    {
        $family = $this->getMockBuilder('Pim\Bundle\CatalogBundle\Entity\Family')
            ->disableOriginalConstructor()
            ->setMethods(array('getCode'))
            ->getMock();
        $family->expects($this->once())
            ->method('getCode')
            ->will($this->returnValue(self::CHANNEL));

        $product = $this->getMock('Pim\Bundle\CatalogBundle\Entity\Product');

        $product->expects($this->once())
            ->method('getFamily')
            ->will($this->returnValue($family));

        $this->magentoSoapClient
            ->expects($this->once())
            ->method('getAttributeSetId')
            ->will($this->throwException(new AttributeSetNotFoundException()));

        $this->processor->process(array($product));
    }

    public function testGetConfigurationFields()
    {
        $configurationFields = $this->processor->getConfigurationFields();

        $this->assertTrue(isset($configurationFields['soapUsername']));
        $this->assertTrue(isset($configurationFields['soapApiKey']));
        $this->assertTrue(isset($configurationFields['soapUrl']));
        $this->assertTrue(isset($configurationFields['channel']));
        $this->assertTrue(isset($configurationFields['defaultLocale']));
    }

    public function testSettersAndGetters()
    {
        $this->assertEquals($this->processor->getSoapUsername(), self::LOGIN);
        $this->assertEquals($this->processor->getSoapApiKey(), self::PASSWORD);
        $this->assertEquals($this->processor->getSoapUrl(), self::URL);
        $this->assertEquals($this->processor->getChannel(), self::CHANNEL);

        $this->processor->setDefaultLocale(self::DEFAULT_LOCALE);
        $this->assertEquals($this->processor->getDefaultLocale(), self::DEFAULT_LOCALE);
    }

    protected function getProductMock()
    {
        $family = $this->getMockBuilder('Pim\Bundle\CatalogBundle\Entity\Family')
            ->disableOriginalConstructor()
            ->setMethods(array('getCode'))
            ->getMock();
        $family->expects($this->once())
            ->method('getCode')
            ->will($this->returnValue(self::CHANNEL));

        // $priceProductValue = $this->getMock('Pim\Bundle\CatalogBundle\Entity\ProductValue');
        // $priceProductValue->expects($this->once())->method('getData')->will($this->returnValue(self::PRICE));

        // $priceCollection = $this->getMock('Doctrine\Common\Collections\ArrayCollection');
        // $priceCollection->expects($this->once())->method('first')->will($this->returnValue($priceProductValue));

        // $price = $this->getMockBuilder('Pim\Bundle\CatalogBundle\Entity\ProductPrice')
        //     ->disableOriginalConstructor()
        //     ->setMethods(array('getPrices'))
        //     ->getMock();
        // $price->expects($this->once())->method('getPrices')->will($this->returnValue($priceCollection));

        $product = $this->getMock('Pim\Bundle\CatalogBundle\Entity\Product');

        $attribute = $this->getMockBuilder('Pim\Bundle\CatalogBundle\Entity\ProductValue')
            ->disableOriginalConstructor()
            ->setMethods(array('getCode', 'getTranslatable', 'getScopable'))
            ->getMock();
        $attribute->expects($this->any())
            ->method('getCode')
            ->will($this->returnValue(self::ATTRIBUTE_NAME));
        $attribute->expects($this->any())
            ->method('getTranslatable')
            ->will($this->returnValue(true));
        $attribute->expects($this->any())
            ->method('getScopable')
            ->will($this->returnValue(true));

        $attributes = array(
            $attribute
        );

        $product->expects($this->once())
            ->method('getFamily')
            ->will($this->returnValue($family));

        $product->expects($this->any())
            ->method('getAllAttributes')
            ->will($this->returnValue($attributes));

        $map = array(
            array('name',              self::DEFAULT_LOCALE, self::CHANNEL, self::NAME),
            array('short_description', self::DEFAULT_LOCALE, self::CHANNEL, self::DESCRIPTION),
            array('short_description', self::DEFAULT_LOCALE, self::CHANNEL, self::SHORT_DESCRIPTION),
            array('weight',            self::DEFAULT_LOCALE, self::CHANNEL, self::WEIGHT),
            array('status',            self::DEFAULT_LOCALE, self::CHANNEL, self::STATUS),
            array('visibility',        self::DEFAULT_LOCALE, self::CHANNEL, self::VISIBILITY),
            array('tax_class_id',      self::DEFAULT_LOCALE, self::CHANNEL, self::TAX_CLASS_ID),
            //array('price',             null,                 null,          $price)
        );

        $product->expects($this->any())
            ->method('getValue')
            ->will($this->returnValueMap($map));

        return $product;
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

        $locale->expects($this->once())
            ->method('getCode')
            ->will($this->returnValue(self::DEFAULT_LOCALE));

        $channel = $this->getMockBuilder('Pim\Bundle\CatalogBundle\Entity\Channel')
            ->disableOriginalConstructor()
            ->setMethods(array('getLocales'))
            ->getMock();
        $channel->expects($this->once())
            ->method('getLocales')
            ->will($this->returnValue(array($locale)));

        $channelManager
            ->expects($this->once())
            ->method('getChannels')
            ->with(array('code' => self::CHANNEL))
            ->will($this->returnValue(array($channel)));

        return $channelManager;
    }
}