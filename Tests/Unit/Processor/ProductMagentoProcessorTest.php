<?php

namespace Pim\Bundle\MagentoConnectorBundle\Tests\Unit\Processor;

use Pim\Bundle\MagentoConnectorBundle\Processor\ProductMagentoProcessor;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientParameters;

/**
 * Test related class
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductMagentoProcessorTest extends \PHPUnit_Framework_TestCase
{
    const LOGIN                = 'login';
    const PASSWORD             = 'password';
    const URL                  = 'url';
    const CHANNEL              = 'channel';
    const DEFAULT_LOCALE       = 'en_US';
    const SKU                  = 'sku-010';
    const NEW_SKU              = 'sku-011';
    const CURRENCY             = 0;
    const ENABLED              = true;
    const VISIBILITY           = 4;
    const WEBSITE              = 0;
    const ATTRIBUTE_SET_ID     = 0;
    const NEW_ATTRIBUTE_SET_ID = 1;
    const FAMILY               = 'shirt';
    const NEW_FAMILY           = 'mug';

    /**
     * Test the related method
     */
    public function testProcess()
    {
        $processor = $this->getSimpleProcessor();

        $product = $this->getMock('Pim\Bundle\CatalogBundle\Model\Product');
        $product->expects($this->any())
            ->method('getFamily')
            ->will($this->returnValue($this->getFamilyMock()));
        $product->expects($this->any())
            ->method('getIdentifier')
            ->will($this->returnValue(self::SKU));

        $products = array($product);

        $processor->process($products);
    }

    /**
     * @expectedException Oro\Bundle\BatchBundle\Item\InvalidItemException
     */
    public function testProcessAttributeSetChanged()
    {
        $processor = $this->getSimpleProcessor();

        $product = $this->getMock('Pim\Bundle\CatalogBundle\Model\Product');
        $product->expects($this->any())
            ->method('getFamily')
            ->will($this->returnValue($this->getChangedFamilyMock()));
        $product->expects($this->any())
            ->method('getIdentifier')
            ->will($this->returnValue(self::SKU));

        $products = array($product);

        $processor->process($products);
    }

    /**
     * Test the product creation process
     */
    public function testProcessProductDoesntExist()
    {
        $processor = $this->getSimpleProcessor();

        $product = $this->getMock('Pim\Bundle\CatalogBundle\Model\Product');
        $product->expects($this->any())
            ->method('getFamily')
            ->will($this->returnValue($this->getChangedFamilyMock()));
        $product->expects($this->any())
            ->method('getIdentifier')
            ->will($this->returnValue(self::NEW_SKU));

        $products = array($product);

        $processor->process($products);
    }

    /**
     * @expectedException Oro\Bundle\BatchBundle\Item\InvalidItemException
     */
    public function testProcessAttributeSetNotFound()
    {
        $magentoWebserviceGuesserMock = $this->getMockBuilder(
            'Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoWebserviceGuesser'
            )
            ->disableOriginalConstructor()
            ->getMock();

        $magentoWebservice = $this->getMagentoWebserviceAttributeSetNotFoundMock();

        $magentoWebserviceGuesserMock->expects($this->any())
            ->method('getWebservice')
            ->with(new MagentoSoapClientParameters(null, null, null))
            ->will($this->returnValue($magentoWebservice));

        $channelManagerMock           = $this->getChannelManagerMock();
        $productNormalizerMock        = $this->getProductNormalizerMock();
        $metricConverterMock          = $this->getMetricConverterMock();

        $processor = new ProductMagentoProcessor(
            $channelManagerMock,
            $magentoWebserviceGuesserMock,
            $productNormalizerMock,
            $metricConverterMock
        );

        $product = $this->getMock('Pim\Bundle\CatalogBundle\Model\Product');
        $product->expects($this->any())
            ->method('getFamily')
            ->will($this->returnValue($this->getChangedFamilyMock()));
        $product->expects($this->any())
            ->method('getIdentifier')
            ->will($this->returnValue(self::SKU));

        $products = array($product);

        $processor->process($products);
    }

    /**
     * @expectedException Oro\Bundle\BatchBundle\Item\InvalidItemException
     */
    public function testProcessInvalidOption()
    {
        $channelManagerMock           = $this->getChannelManagerMock();
        $magentoWebserviceGuesserMock = $this->getMagentoWebserviceGuesserMock();
        $productNormalizerMock  = $this->getExceptionNormalizerMock(
            new \Pim\Bundle\MagentoConnectorBundle\Normalizer\InvalidOptionException()
        );
        $metricConverterMock          = $this->getMetricConverterMock();

        $processor = new ProductMagentoProcessor(
            $channelManagerMock,
            $magentoWebserviceGuesserMock,
            $productNormalizerMock,
            $metricConverterMock
        );

        $product = $this->getMock('Pim\Bundle\CatalogBundle\Model\Product');
        $product->expects($this->any())
            ->method('getFamily')
            ->will($this->returnValue($this->getFamilyMock()));
        $product->expects($this->any())
            ->method('getIdentifier')
            ->will($this->returnValue(self::SKU));

        $products = array($product);

        $processor->process($products);
    }

    /**
     * @expectedException Oro\Bundle\BatchBundle\Item\InvalidItemException
     */
    public function testProcessInvalidScopeMatch()
    {
        $channelManagerMock           = $this->getChannelManagerMock();
        $magentoWebserviceGuesserMock = $this->getMagentoWebserviceGuesserMock();
        $productNormalizerMock  = $this->getExceptionNormalizerMock(
            new \Pim\Bundle\MagentoConnectorBundle\Normalizer\InvalidScopeMatchException()
        );
        $metricConverterMock          = $this->getMetricConverterMock();

        $processor = new ProductMagentoProcessor(
            $channelManagerMock,
            $magentoWebserviceGuesserMock,
            $productNormalizerMock,
            $metricConverterMock
        );

        $product = $this->getMock('Pim\Bundle\CatalogBundle\Model\Product');
        $product->expects($this->any())
            ->method('getFamily')
            ->will($this->returnValue($this->getFamilyMock()));
        $product->expects($this->any())
            ->method('getIdentifier')
            ->will($this->returnValue(self::SKU));

        $products = array($product);

        $processor->process($products);
    }

    /**
     * @expectedException Oro\Bundle\BatchBundle\Item\InvalidItemException
     */
    public function testProcessAttributeNotFound()
    {
        $channelManagerMock           = $this->getChannelManagerMock();
        $magentoWebserviceGuesserMock = $this->getMagentoWebserviceGuesserMock();
        $productNormalizerMock  = $this->getExceptionNormalizerMock(
            new \Pim\Bundle\MagentoConnectorBundle\Normalizer\AttributeNotFoundException()
        );
        $metricConverterMock          = $this->getMetricConverterMock();

        $processor = new ProductMagentoProcessor(
            $channelManagerMock,
            $magentoWebserviceGuesserMock,
            $productNormalizerMock,
            $metricConverterMock
        );

        $product = $this->getMock('Pim\Bundle\CatalogBundle\Model\Product');
        $product->expects($this->any())
            ->method('getFamily')
            ->will($this->returnValue($this->getFamilyMock()));
        $product->expects($this->any())
            ->method('getIdentifier')
            ->will($this->returnValue(self::SKU));

        $products = array($product);

        $processor->process($products);
    }

    /**
     * Test all setters and getters
     */
    public function testSettersAndGetters()
    {
        $processor = $this->getSimpleProcessor();

        $processor->setSoapUsername(self::LOGIN);
        $processor->setSoapApiKey(self::PASSWORD);
        $processor->setSoapUrl(self::URL);
        $processor->setChannel(self::CHANNEL);
        $processor->setDefaultLocale(self::DEFAULT_LOCALE);
        $processor->setCurrency(self::CURRENCY);
        $processor->setEnabled(self::ENABLED);
        $processor->setVisibility(self::VISIBILITY);
        $processor->setWebsite(self::WEBSITE);

        $this->assertEquals($processor->getSoapUsername(), self::LOGIN);
        $this->assertEquals($processor->getSoapApiKey(), self::PASSWORD);
        $this->assertEquals($processor->getSoapUrl(), self::URL);
        $this->assertEquals($processor->getChannel(), self::CHANNEL);
        $this->assertEquals($processor->getDefaultLocale(), self::DEFAULT_LOCALE);
        $this->assertEquals($processor->getCurrency(), self::CURRENCY);
        $this->assertEquals($processor->getEnabled(), self::ENABLED);
        $this->assertEquals($processor->getVisibility(), self::VISIBILITY);
        $this->assertEquals($processor->getWebsite(), self::WEBSITE);
    }

    /**
     * Test configuration fields
     */
    public function testGetConfigurationFields()
    {
        $processor = $this->getSimpleProcessor();

        $configurationFields = $processor->getConfigurationFields();

        $this->assertTrue(isset($configurationFields['soapUsername']));
        $this->assertTrue(isset($configurationFields['soapApiKey']));
        $this->assertTrue(isset($configurationFields['soapUrl']));
        $this->assertTrue(isset($configurationFields['channel']));
        $this->assertTrue(isset($configurationFields['defaultLocale']));
    }

    /**
     * Get a product family mock
     * @return FamilyMock
     */
    protected function getFamilyMock()
    {
        $family = $this->getMockBuilder('Pim\Bundle\CatalogBundle\Entity\Family')
            ->disableOriginalConstructor()
            ->setMethods(array('getCode'))
            ->getMock();
        $family->expects($this->any())
            ->method('getCode')
            ->will($this->returnValue(self::FAMILY));

        return $family;
    }

    /**
     * Get a product family which doesn't exist on Magento side
     * @return FamilyMock
     */
    protected function getChangedFamilyMock()
    {
        $family = $this->getMockBuilder('Pim\Bundle\CatalogBundle\Entity\Family')
            ->disableOriginalConstructor()
            ->setMethods(array('getCode'))
            ->getMock();
        $family->expects($this->any())
            ->method('getCode')
            ->will($this->returnValue(self::NEW_FAMILY));

        return $family;
    }

    /**
     * Get a simple processor
     * @return ProductMagentoProcessor
     */
    protected function getSimpleProcessor()
    {
        $channelManagerMock           = $this->getChannelManagerMock();
        $magentoWebserviceGuesserMock = $this->getMagentoWebserviceGuesserMock();
        $productNormalizerMock        = $this->getProductNormalizerMock();
        $metricConverterMock          = $this->getMetricConverterMock();

        $processor = new ProductMagentoProcessor(
            $channelManagerMock,
            $magentoWebserviceGuesserMock,
            $productNormalizerMock,
            $metricConverterMock
        );

        return $processor;
    }

    /**
     * Get a normalizer who will throw given the exception
     * @param  Exception $exception
     * @return ProductUpdateNormalizer
     */
    public function getExceptionNormalizerMock(\Exception $exception)
    {
        $mock = $this->getMockBuilder('Pim\Bundle\MagentoConnectorBundle\Normalizer\ProductNormalizer')
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects($this->once())
            ->method('normalize')
            ->will($this->throwException(
                $exception
            ));

        return $mock;
    }

    /**
     * Get a channel manager mock
     * @return ChannelManagerMock
     */
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

    /**
     * Get a channel mock
     * @return ChannelMock
     */
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

    /**
     * Get a MagentoWebserviceGuesser mock
     * @return MagentoWebserviceGuesserMock
     */
    protected function getMagentoWebserviceGuesserMock()
    {
        $magentoWebserviceGuesserMock = $this->getMockBuilder(
            'Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoWebserviceGuesser'
            )
            ->disableOriginalConstructor()
            ->getMock();

        $magentoWebservice = $this->getMagentoWebserviceMock();

        $magentoWebserviceGuesserMock->expects($this->any())
            ->method('getWebservice')
            ->with(new MagentoSoapClientParameters(null, null, null))
            ->will($this->returnValue($magentoWebservice));

        return $magentoWebserviceGuesserMock;
    }

    /**
     * Get a MagentoWebservice mock
     * @return MagentoWebserviceMock
     */
    protected function getMagentoWebserviceMock()
    {
        $magentoWebservice = $this->getMockBuilder('Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoWebservice')
            ->disableOriginalConstructor()
            ->getMock();

        $magentoWebservice->expects($this->any())
            ->method('getProductsStatus')
            ->will($this->returnValue(array(
                array(
                    'sku' => self::SKU,
                    'set' => self::ATTRIBUTE_SET_ID
                )
            )));

        $map = array(
            array(self::FAMILY,     self::ATTRIBUTE_SET_ID),
            array(self::NEW_FAMILY, self::NEW_ATTRIBUTE_SET_ID)
        );

        $magentoWebservice->expects($this->any())
            ->method('getAttributeSetId')
            ->will($this->returnValueMap(
                $map
            ));

        return $magentoWebservice;
    }

    /**
     * Get a MagentoWebservice mock who will throw an AttributeSetNotFound on getAttributeSetId call
     * @return MagentoWebserviceMock
     */
    protected function getMagentoWebserviceAttributeSetNotFoundMock()
    {
        $magentoWebservice = $this->getMockBuilder('Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoWebservice')
            ->disableOriginalConstructor()
            ->getMock();

        $magentoWebservice->expects($this->any())
            ->method('getProductsStatus')
            ->will($this->returnValue(array(
                array(
                    'sku' => self::SKU,
                    'set' => self::ATTRIBUTE_SET_ID
                )
            )));

        $magentoWebservice->expects($this->any())
            ->method('getAttributeSetId')
            ->will($this->throwException(
                new \Pim\Bundle\MagentoConnectorBundle\Webservice\AttributeSetNotFoundException()
            ));

        return $magentoWebservice;
    }

    /**
     * Get a ProductNormalizer mock
     * @return ProductNormalizerMock
     */
    protected function getProductNormalizerMock()
    {
        $mock = $this->getMockBuilder('Pim\Bundle\MagentoConnectorBundle\Normalizer\ProductNormalizer')
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

    /**
     * Get a metricConverter mock
     * @return MetricConverterMock
     */
    protected function getMetricConverterMock()
    {
        return $this->getMockBuilder('Pim\Bundle\ImportExportBundle\Converter\MetricConverter')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
