<?php

namespace Pim\Bundle\MagentoConnectorBundle\Tests\Unit\Processor;

use Pim\Bundle\MagentoConnectorBundle\Processor\ProductMagentoProcessor;

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
    const CHANNEL           = 'ecommerce';
    const PRICE             = '13.37';
    const NAME              = 'Product example';
    const DESCRIPTION       = 'Description';
    const SHORT_DESCRIPTION = 'Short description';
    const WEIGHT            = '10';
    const STATUS            = 1;
    const VISIBILITY        = 4;
    const TAX_CLASS_ID      = 0;

    const DEFAULT_LOCALE = 'en_US';

    protected $channelManager;
    protected $magentoSoapClient;
    protected $processor;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->channelManager    = $this->getChannelManagerMock();
        $this->magentoSoapClient = $this->getMock('Pim\Bundle' . 
            '\MagentoConnectorBundle\Webservice\MagentoSoapClient');

        $this->processor         = new ProductMagentoProcessor(
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

    public function testProcessAttributeSetNotFound()
    {
        $family = $this->getMockBuilder('Pim\Bundle\CatalogBundle\Entity\Family')
            ->disableOriginalConstructor()
            ->setMethods(array('getCode'))
            ->getMock();
        $family->expects($this->once())
            ->method('getCode')
            ->will($this->returnValue(self::CHANNEL));

        $priceProductValue = $this->getMock('Pim\Bundle\CatalogBundle\Entity\ProductValue');
        $priceProductValue->expects($this->once())->method('getData')->will($this->returnValue(self::PRICE));

        $priceCollection = $this->getMock('Doctrine\Common\Collections\ArrayCollection');
        $priceCollection->expects($this->once())->method('first')->will($this->returnValue($priceProductValue));

        $price = $this->getMock('Pim\Bundle\CatalogBundle\Entity\ProductPrice');
        $price->expects($this->once())->method('getPrices')->will($this->returnValue($priceCollection));

        $product = $this->getMock('Pim\Bundle\CatalogBundle\Entity\Product');

        $product->expects($this->once())
            ->method('getFamily')
            ->will($this->returnValue($family));

        $map = array(
            'name',              self::DEFAULT_LOCALE, self::CHANNEL, self::NAME,
            'description',       self::DEFAULT_LOCALE, self::CHANNEL, self::DESCRIPTION,
            'short_description', self::DEFAULT_LOCALE, self::CHANNEL, self::SHORT_DESCRIPTION,
            'weight',            self::DEFAULT_LOCALE, self::CHANNEL, self::WEIGHT,
            'status',            self::DEFAULT_LOCALE, self::CHANNEL, self::STATUS,
            'visibility',        self::DEFAULT_LOCALE, self::CHANNEL, self::VISIBILITY,
            'tax_class_id',      self::DEFAULT_LOCALE, self::CHANNEL, self::TAX_CLASS_ID,
            'price',             null,                 null,          $price
        );

        $product->expects($this->any())
            ->method('getValue')
            ->will($this->returnValueMap($map));

        $this->processor->process(array($product));
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    protected function getChannelManagerMock()
    {
        return $this
            ->getMockBuilder('Pim\Bundle\CatalogBundle\Manager\ChannelManager')
            ->disableOriginalConstructor()
            ->getMock();
    }
}