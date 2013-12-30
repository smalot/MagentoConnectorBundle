<?php

namespace Pim\Bundle\MagentoConnectorBundle\Tests\Unit\Processor;

use Pim\Bundle\MagentoConnectorBundle\Processor\ProductMagentoProcessor;
use Pim\Bundle\MagentoConnectorBundle\Webservice\AttributeSetNotFoundException;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\InvalidScopeMatchException;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\AttributeNotFoundException;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\InvalidOptionException;

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
    const ENABLED           = true;
    const WEBSITE           = 0;
    const PRICE             = '13.37';
    const NAME              = 'Product example';
    const DESCRIPTION       = 'Description';
    const SHORT_DESCRIPTION = 'Short description';
    const WEIGHT            = '10';
    const STATUS            = 1;
    const VISIBILITY        = 4;
    const CURRENCY          = 0;
    const ATTRIBUTE_NAME    = 'name';
    const SKU               = 'sku-010';
    const SET               = '10';
    const TAX_CLASS_ID      = 4;

    const DEFAULT_LOCALE    = 'en_US';

    protected $channelManager;
    protected $magentoWebservice;
    protected $processor;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->channelManager = $this->getChannelManagerMock();
        $this->magentoWebservice = $this->getMagentoWebserviceMock();

        $this->productCreateNormalizer = $this->getProductCreateNormalizerMock();
        $this->productUpdateNormalizer = $this->getProductUpdateNormalizerMock();
        $this->metricConverter         = $this->getMockBuilder(
                'Pim\Bundle\ImportExportBundle\Converter\MetricConverter'
            )->disableOriginalConstructor()
            ->getMock();

        $this->metricConverter->expects($this->any())
            ->method('convert')
            ->will($this->returnValue('10.40'));

        $this->processor = new ProductMagentoProcessor(
            $this->channelManager,
            $this->magentoWebservice,
            $this->productCreateNormalizer,
            $this->productUpdateNormalizer,
            $this->metricConverter
        );

        $this->processor->setSoapUsername(self::LOGIN);
        $this->processor->setSoapApiKey(self::PASSWORD);
        $this->processor->setSoapUrl(self::URL);
        $this->processor->setChannel(self::CHANNEL);
        $this->processor->setEnabled(self::ENABLED);
        $this->processor->setVisibility(self::VISIBILITY);
        $this->processor->setWebsite(self::WEBSITE);
        $this->processor->setCurrency(self::CURRENCY);
    }

    protected function getProductCreateNormalizerMock()
    {
        $mock = $this->getMockBuilder('Pim\Bundle\MagentoConnectorBundle\Normalizer\ProductCreateNormalizer')
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects($this->any())
            ->method('normalize')
            ->will($this->returnValue(
                array(
                    'admin' => array(
                        self::SKU,
                        array(
                            'name'              => 'Simple product edited',
                            'description'       => 'long description',
                            'short_description' => 'short description',
                            'status'            => '0',
                            'visibility'        => '4',
                            'price'             => '12',
                            'tax_class_id'      => '0',
                            'websites'          => array(
                                '0' => 'base',
                            )
                        ),
                        'admin',
                    ),
                    'en_us' => array(
                        self::SKU,
                        array(
                            'name'              => 'Simple product edited',
                            'description'       => 'long description',
                            'short_description' => 'short description',
                        ),
                        'en_us',
                    ),
                    'fr_fr' => array(
                        self::SKU,
                        array(
                            'name'              => 'Exemple de produit',
                            'description'       => 'produit long',
                            'short_description' => 'produit',
                        ),
                        'fr_fr'
                    )
                )
            ));

        return $mock;
    }


    protected function getProductUpdateNormalizerMock()
    {
        $mock = $this->getMockBuilder('Pim\Bundle\MagentoConnectorBundle\Normalizer\ProductUpdateNormalizer')
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects($this->any())
            ->method('normalize')
            ->will($this->returnValue(
                array(
                    'admin' => array(
                        self::SKU,
                        array(
                            'name'              => 'Simple product edited',
                            'description'       => 'long description',
                            'short_description' => 'short description',
                            'status'            => '0',
                            'visibility'        => '4',
                            'price'             => '12',
                            'tax_class_id'      => '0',
                            'websites'          => array(
                                '0' => 'base',
                            )
                        ),
                        'admin',
                    ),
                    'en_us' => array(
                        self::SKU,
                        array(
                            'name'              => 'Simple product edited',
                            'description'       => 'long description',
                            'short_description' => 'short description',
                        ),
                        'en_us',
                    ),
                    'fr_fr' => array(
                        self::SKU,
                        array(
                            'name'              => 'Exemple de produit',
                            'description'       => 'produit long',
                            'short_description' => 'produit',
                        ),
                        'fr_fr'
                    )
                )
            ));

        return $mock;
    }

    protected function getProductUpdateNormalizerMockThrowing($exception)
    {
        $mock = $this
            ->getMockBuilder('Pim\Bundle\MagentoConnectorBundle\Normalizer\ProductUpdateNormalizer')
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects($this->any())
            ->method('normalize')
            ->will($this->throwException($exception));

        return $mock;
    }

    /**
     * @expectedException Oro\Bundle\BatchBundle\Item\InvalidItemException
     */
    public function testNormalizeProductWithInvalidScopeMatch()
    {
        $product = $this->getProductMock();
        $this->channelManager = $this->getChannelManagerMock();

        $magentoWebservice = $this->getMagentoWebserviceMock();

        $processor = new ProductMagentoProcessor(
            $this->channelManager,
            $magentoWebservice,
            $this->getProductCreateNormalizerMock(),
            $this->getProductUpdateNormalizerMockThrowing(
                new InvalidScopeMatchException()
            ),
            $this->metricConverter
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
    public function testNormalizeProductWithAttributeNotFound()
    {
        $product = $this->getProductMock();
        $this->channelManager = $this->getChannelManagerMock();

        $magentoWebservice = $this->getMagentoWebserviceMock();

        $processor = new ProductMagentoProcessor(
            $this->channelManager,
            $magentoWebservice,
            $this->getProductCreateNormalizerMock(),
            $this->getProductUpdateNormalizerMockThrowing(
                new AttributeNotFoundException()
            ),
            $this->metricConverter
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
    public function testNormalizeProductWithInvalidOption()
    {
        $product = $this->getProductMock();
        $this->channelManager = $this->getChannelManagerMock();

        $magentoWebservice = $this->getMagentoWebserviceMock();

        $processor = new ProductMagentoProcessor(
            $this->channelManager,
            $magentoWebservice,
            $this->getProductCreateNormalizerMock(),
            $this->getProductUpdateNormalizerMockThrowing(
                new InvalidOptionException()
            ),
            $this->metricConverter
        );

        $processor->setSoapUsername(self::LOGIN);
        $processor->setSoapApiKey(self::PASSWORD);
        $processor->setSoapUrl(self::URL);
        $processor->setChannel(self::CHANNEL);

        $processor->process(array($product));
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

        $magentoWebservice = $this->getMagentoWebserviceMock();

        $processor = new ProductMagentoProcessor(
            $this->channelManager,
            $magentoWebservice,
            $this->getProductCreateNormalizerMock(),
            $this->getProductUpdateNormalizerMock(),
            $this->metricConverter
        );

        $processor->setSoapUsername(self::LOGIN);
        $processor->setSoapApiKey(self::PASSWORD);
        $processor->setSoapUrl(self::URL);
        $processor->setChannel(self::CHANNEL);

        $processor->process(array($product));
    }

    public function testProcessProductDoesntExist()
    {
        $family  = $this->getFamilyMock();
        $price   = $this->getProductPriceMock();
        $product = $this->getMock('Pim\Bundle\CatalogBundle\Model\Product');

        $attributes = array();

        foreach ($this->getSampleValues() as $key => $value) {
            $attributes[$key] = $this->getAttributeMock($value);
        }

        $product->expects($this->any())
            ->method('getIdentifier')
            ->will($this->returnValue('sku-011'));

        $product->expects($this->any())
            ->method('getFamily')
            ->will($this->returnValue($family));

        $product->expects($this->any())
            ->method('getAllAttributes')
            ->will($this->returnValue($attributes));

        $map = array(
            array('name',              self::DEFAULT_LOCALE, self::CHANNEL, self::NAME),
            array('description',       self::DEFAULT_LOCALE, self::CHANNEL, self::DESCRIPTION),
            array('short_description', self::DEFAULT_LOCALE, self::CHANNEL, self::SHORT_DESCRIPTION),
            array('weight',            self::DEFAULT_LOCALE, self::CHANNEL, self::WEIGHT),
            array('status',            self::DEFAULT_LOCALE, self::CHANNEL, self::STATUS),
            array('visibility',        self::DEFAULT_LOCALE, self::CHANNEL, self::VISIBILITY),
            array('tax_class_id',      self::DEFAULT_LOCALE, self::CHANNEL, self::TAX_CLASS_ID),
            array('price',             self::DEFAULT_LOCALE, self::CHANNEL, $price)
        );

        $product->expects($this->any())
            ->method('getValue')
            ->will($this->returnValueMap($map));

        $this->channelManager = $this->getChannelManagerMock();

        $magentoWebservice = $this->getMagentoWebserviceMock();

        $processor = new ProductMagentoProcessor(
            $this->channelManager,
            $magentoWebservice,
            $this->getProductCreateNormalizerMock(),
            $this->getProductUpdateNormalizerMock(),
            $this->metricConverter
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
    public function testProcessAttributeSetChanged()
    {
        $product = $this->getProductMock();

        $this->channelManager = $this->getChannelManagerMock();

        $magentoWebservice = $this->getMock('Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoWebservice');

        $magentoWebservice
            ->expects($this->any())
            ->method('getAttributeSetId')
            ->will($this->returnValue(10));

        $magentoWebservice = $this->addGetStoreViewListMock($magentoWebservice);
        $magentoWebservice = $this->addGetAttributeListMock($magentoWebservice);

        $magentoWebservice->expects($this->any())
            ->method('getProductsStatus')
            ->will($this->returnValue(
                array(
                    array(
                        'sku' => self::SKU,
                        'set' => 11
                    )
                )
            ));

        $processor = new ProductMagentoProcessor(
            $this->channelManager,
            $magentoWebservice,
            $this->getProductCreateNormalizerMock(),
            $this->getProductUpdateNormalizerMock(),
            $this->metricConverter
        );

        $processor->setSoapUsername(self::LOGIN);
        $processor->setSoapApiKey(self::PASSWORD);
        $processor->setSoapUrl(self::URL);
        $processor->setChannel(self::CHANNEL);

        $processor->process(array($product));
    }

    protected function getMagentoWebserviceMock()
    {
        $magentoSoapClientMock = $this->getMockBuilder('Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClient')
            ->disableOriginalConstructor()
            ->getMock();

        $magentoWebservice = $this->getMockBuilder('Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoWebservice')
            ->disableOriginalConstructor()
            ->getMock();

        $magentoWebservice->expects($this->any())
            ->method('getWebservice')
            ->will($this->returnValue($magentoSoapClientMock));

        $magentoWebservice
            ->expects($this->any())
            ->method('getAttributeSetId')
            ->will($this->returnValue(10));

        $magentoWebservice = $this->addGetStoreViewListMock($magentoWebservice);
        $magentoWebservice = $this->addGetAttributeListMock($magentoWebservice);
        $magentoWebservice = $this->addGetProductsStatusMock($magentoWebservice);

        return $magentoWebservice;
    }

    protected function addGetStoreViewListMock($mock)
    {
        $mock
            ->expects($this->once())
            ->method('getStoreViewsList')
            ->will($this->returnValue(
                array(
                    array(
                        'code' => 'admin'
                    ),
                    array(
                        'code' => 'en_us'
                    ),
                    array(
                        'code' => 'fr_fr'
                    ),
                )
            ));

        return $mock;
    }

    protected function addGetAttributeListMock($mock)
    {
        $mock
            ->expects($this->any())
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
                        'code' => 'price',
                        'required' => '1',
                        'scope' => 'website'
                    ),
                    array(
                        'code' => 'tax_class_id',
                        'required' => '1',
                        'scope' => 'website'
                    ),
                )
            ));

        return $mock;
    }

    protected function addGetProductsStatusMock($mock)
    {
        $mock
            ->expects($this->any())
            ->method('getProductsStatus')
            ->will($this->returnValue(
                array(
                    array(
                        'sku' => self::SKU,
                        'set' => self::SET
                    )
                )
            ));

        return $mock;
    }

    /**
     * @expectedException Oro\Bundle\BatchBundle\Item\InvalidItemException
     */
    public function testProcessAttributeSetNotFound()
    {
        $product = $this->getProductMock();

        $this->magentoWebservice = $this->addGetAttributeListMock($this->magentoWebservice);
        $this->magentoWebservice = $this->addGetProductsStatusMock($this->magentoWebservice);

        $this->magentoWebservice
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
        $this->assertEquals($this->processor->getSoapApiKey(),   self::PASSWORD);
        $this->assertEquals($this->processor->getSoapUrl(),      self::URL);
        $this->assertEquals($this->processor->getChannel(),      self::CHANNEL);
        $this->assertEquals($this->processor->getEnabled(),      self::ENABLED);
        $this->assertEquals($this->processor->getVisibility(),   self::VISIBILITY);
        $this->assertEquals($this->processor->getWebsite(),      self::WEBSITE);
        $this->assertEquals($this->processor->getCurrency(),     self::CURRENCY);

        $this->processor->setDefaultLocale(self::DEFAULT_LOCALE);
        $this->assertEquals($this->processor->getDefaultLocale(), self::DEFAULT_LOCALE);
    }

    protected function getProductMock()
    {
        $family  = $this->getFamilyMock();
        $price   = $this->getProductPriceMock();
        $product = $this->getMock('Pim\Bundle\CatalogBundle\Model\Product');

        $attributes = array();

        foreach ($this->getSampleValues() as $key => $value) {
            $attributes[$key] = $this->getAttributeMock($value);
        }

        $product->expects($this->any())
            ->method('getIdentifier')
            ->will($this->returnValue(self::SKU));

        $product->expects($this->any())
            ->method('getFamily')
            ->will($this->returnValue($family));

        $product->expects($this->any())
            ->method('getAllAttributes')
            ->will($this->returnValue($attributes));

        $map = array(
            array('name',              self::DEFAULT_LOCALE, self::CHANNEL, self::NAME),
            array('description',       self::DEFAULT_LOCALE, self::CHANNEL, self::DESCRIPTION),
            array('short_description', self::DEFAULT_LOCALE, self::CHANNEL, self::SHORT_DESCRIPTION),
            array('weight',            self::DEFAULT_LOCALE, self::CHANNEL, self::WEIGHT),
            array('status',            self::DEFAULT_LOCALE, self::CHANNEL, self::STATUS),
            array('visibility',        self::DEFAULT_LOCALE, self::CHANNEL, self::VISIBILITY),
            array('tax_class_id',      self::DEFAULT_LOCALE, self::CHANNEL, self::TAX_CLASS_ID),
            array('price',             self::DEFAULT_LOCALE, self::CHANNEL, $price)
        );

        $product->expects($this->any())
            ->method('getValue')
            ->will($this->returnValueMap($map));

        return $product;
    }

    protected function getFamilyMock()
    {
        $family = $this->getMockBuilder('Pim\Bundle\CatalogBundle\Entity\Family')
            ->disableOriginalConstructor()
            ->setMethods(array('getCode'))
            ->getMock();
        $family->expects($this->any())
            ->method('getCode')
            ->will($this->returnValue(self::CHANNEL));

        return $family;
    }

    protected function getProductPriceMock()
    {
        $priceProductValue = $this->getMock('Pim\Bundle\CatalogBundle\Model\ProductValue');
        $priceProductValue->expects($this->any())->method('getData')->will($this->returnValue(self::PRICE));

        $priceCollection = $this->getMock('Doctrine\Common\Collections\ArrayCollection');
        $priceCollection->expects($this->any())->method('first')->will($this->returnValue($priceProductValue));

        $price = $this->getMockBuilder('Pim\Bundle\CatalogBundle\Model\ProductPrice')
            ->disableOriginalConstructor()
            ->setMethods(array('getPrices'))
            ->getMock();
        $price->expects($this->any())->method('getPrices')->will($this->returnValue($priceCollection));

        return $price;
    }

    protected function getAttributeMock($value)
    {
        $attribute = $this->getMockBuilder('Pim\Bundle\CatalogBundle\Model\ProductValue')
            ->disableOriginalConstructor()
            ->setMethods(array('getCode', 'isTranslatable', 'isScopable'))
            ->getMock();
        $attribute->expects($this->any())
            ->method('getCode')
            ->will($this->returnValue($value['code']));
        $attribute->expects($this->any())
            ->method('isTranslatable')
            ->will($this->returnValue($value['translatable']));
        $attribute->expects($this->any())
            ->method('isScopable')
            ->will($this->returnValue($value['scopable']));

        return $attribute;
    }

    protected function getSampleValues()
    {
        return array(
            'name' => array(
                'scopable'     => true,
                'translatable' => true,
                'value'        => 'Name',
                'code'         => 'name',
                'type'         => 'string',
            ),
            'long_description' => array(
                'scopable'     => true,
                'translatable' => true,
                'value'        => 'Description',
                'code'         => 'description',
                'mapping'      => 'long_description'
            ),
            'short_description' => array(
                'scopable'     => true,
                'translatable' => true,
                'value'        => 'Short description',
                'code'         => 'short_description',
                'type'         => 'string',
            ),
            'status' => array(
                'scopable'     => false,
                'translatable' => false,
                'value'        => true,
                'code'         => 'status',
                'type'         => 'bool',
                'method'       => 'isEnabled',
            ),
            'visibility' => array(
                'scopable'     => false,
                'translatable' => false,
                'value'        => true,
                'code'         => 'visibility',
                'type'         => 'bool',
                'method'       => 'isEnabled',
            ),
            'tax_class_id' => array(
                'scopable'     => false,
                'translatable' => false,
                'value'        => 0,
                'code'         => 'tax_class_id'
            ),
        );
    }

    protected function getChannelManagerMock()
    {
        $channelManager = $this->getMockBuilder('Pim\Bundle\CatalogBundle\Manager\ChannelManager')
            ->disableOriginalConstructor()
            ->getMock();

        $channelManager->expects($this->any())
            ->method('getChannelByCode')
            ->will($this->returnValue($this->getChannelMock()));

        return $channelManager;
    }

    protected function getChannelMock()
    {
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

        return $channel;
    }
}
