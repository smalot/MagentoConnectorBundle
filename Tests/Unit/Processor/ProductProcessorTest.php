<?php

namespace Pim\Bundle\MagentoConnectorBundle\Tests\Unit\Processor;

use Pim\Bundle\MagentoConnectorBundle\Processor\ProductProcessor;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientParameters;

/**
 * Test related class
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductProcessorTest extends \PHPUnit_Framework_TestCase
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
        $webserviceGuesserMock = $this->getMockBuilder(
            'Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser'
        )
            ->disableOriginalConstructor()
            ->getMock();

        $webservice = $this->getWebserviceAttributeSetNotFoundMock();

        $webserviceGuesserMock->expects($this->any())
            ->method('getWebservice')
            ->with(new MagentoSoapClientParameters(null, null, null))
            ->will($this->returnValue($webservice));

        $channelManagerMock           = $this->getChannelManagerMock();
        $normalizerGuesserMock        = $this->getNormalizerGuesserMock();
        $metricConverterMock          = $this->getMetricConverterMock();
        $associationTypeManager       = $this->getAssociationTypeManagerMock();

        $processor = new ProductProcessor(
            $webserviceGuesserMock,
            $normalizerGuesserMock,
            $channelManagerMock,
            $metricConverterMock,
            $associationTypeManager
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
        $webserviceGuesserMock        = $this->getWebserviceGuesserMock();
        $productNormalizerGuesserMock = $this->getExceptionNormalizerGuesserMock(
            new \Pim\Bundle\MagentoConnectorBundle\Normalizer\Exception\InvalidOptionException()
        );
        $metricConverterMock          = $this->getMetricConverterMock();
        $associationTypeManager       = $this->getAssociationTypeManagerMock();

        $processor = new ProductProcessor(
            $webserviceGuesserMock,
            $productNormalizerGuesserMock,
            $channelManagerMock,
            $metricConverterMock,
            $associationTypeManager
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
        $webserviceGuesserMock        = $this->getWebserviceGuesserMock();
        $productNormalizerGuesserMock = $this->getExceptionNormalizerGuesserMock(
            new \Pim\Bundle\MagentoConnectorBundle\Normalizer\Exception\InvalidScopeMatchException()
        );
        $metricConverterMock          = $this->getMetricConverterMock();
        $associationTypeManager       = $this->getAssociationTypeManagerMock();

        $processor = new ProductProcessor(
            $webserviceGuesserMock,
            $productNormalizerGuesserMock,
            $channelManagerMock,
            $metricConverterMock,
            $associationTypeManager
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
        $webserviceGuesserMock        = $this->getWebserviceGuesserMock();
        $productNormalizerGuesserMock = $this->getExceptionNormalizerGuesserMock(
            new \Pim\Bundle\MagentoConnectorBundle\Normalizer\Exception\AttributeNotFoundException()
        );
        $metricConverterMock          = $this->getMetricConverterMock();
        $associationTypeManager       = $this->getAssociationTypeManagerMock();

        $processor = new ProductProcessor(
            $webserviceGuesserMock,
            $productNormalizerGuesserMock,
            $channelManagerMock,
            $metricConverterMock,
            $associationTypeManager
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
     * @return ProductProcessor
     */
    protected function getSimpleProcessor()
    {
        $channelManagerMock     = $this->getChannelManagerMock();
        $webserviceGuesserMock  = $this->getWebserviceGuesserMock();
        $normalizerGuesserMock  = $this->getNormalizerGuesserMock();
        $metricConverterMock    = $this->getMetricConverterMock();
        $associationTypeManager = $this->getAssociationTypeManagerMock();

        $processor = new ProductProcessor(
            $webserviceGuesserMock,
            $normalizerGuesserMock,
            $channelManagerMock,
            $metricConverterMock,
            $associationTypeManager
        );

        return $processor;
    }

    /**
     * Get a normalizer who will throw given the exception
     * @param Exception $exception
     *
     * @return ProductUpdateNormalizer
     */
    public function getExceptionNormalizerMock(\Exception $exception)
    {
        $mock = $this->getMockBuilder('Pim\Bundle\MagentoConnectorBundle\Normalizer\ProductNormalizer')
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects($this->once())
            ->method('normalize')
            ->will(
                $this->throwException(
                    $exception
                )
            );

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
     * Get a WebserviceGuesser mock
     * @return WebserviceGuesserMock
     */
    protected function getWebserviceGuesserMock()
    {
        $webserviceGuesserMock = $this->getMockBuilder(
            'Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser'
        )
            ->disableOriginalConstructor()
            ->getMock();

        $webservice = $this->getWebserviceMock();

        $webserviceGuesserMock->expects($this->any())
            ->method('getWebservice')
            ->with(new MagentoSoapClientParameters(null, null, null))
            ->will($this->returnValue($webservice));

        return $webserviceGuesserMock;
    }

    /**
     * Get a Webservice mock
     * @return WebserviceMock
     */
    protected function getWebserviceMock()
    {
        $webservice = $this->getMockBuilder('Pim\Bundle\MagentoConnectorBundle\Webservice\Webservice')
            ->disableOriginalConstructor()
            ->getMock();

        $webservice->expects($this->any())
            ->method('getProductsStatus')
            ->will(
                $this->returnValue(
                    array(
                        array(
                            'sku' => self::SKU,
                            'set' => self::ATTRIBUTE_SET_ID
                        )
                    )
                )
            );

        $map = array(
            array(self::FAMILY,     self::ATTRIBUTE_SET_ID),
            array(self::NEW_FAMILY, self::NEW_ATTRIBUTE_SET_ID)
        );

        $webservice->expects($this->any())
            ->method('getAttributeSetId')
            ->will(
                $this->returnValueMap(
                    $map
                )
            );

        return $webservice;
    }

    /**
     * Get a Webservice mock who will throw an AttributeSetNotFound on getAttributeSetId call
     * @return WebserviceMock
     */
    protected function getWebserviceAttributeSetNotFoundMock()
    {
        $webservice = $this->getMockBuilder('Pim\Bundle\MagentoConnectorBundle\Webservice\Webservice')
            ->disableOriginalConstructor()
            ->getMock();

        $webservice->expects($this->any())
            ->method('getProductsStatus')
            ->will(
                $this->returnValue(
                    array(
                        array(
                            'sku' => self::SKU,
                            'set' => self::ATTRIBUTE_SET_ID
                        )
                    )
                )
            );

        $webservice->expects($this->any())
            ->method('getAttributeSetId')
            ->will(
                $this->throwException(
                    new \Pim\Bundle\MagentoConnectorBundle\Webservice\AttributeSetNotFoundException()
                )
            );

        return $webservice;
    }

    /**
     * Get a WebserviceGuesser mock
     * @return WebserviceGuesserMock
     */
    protected function getNormalizerGuesserMock()
    {
        $productNormalizerGuesserMock = $this->getMockBuilder(
            'Pim\Bundle\MagentoConnectorBundle\Guesser\NormalizerGuesser'
        )
        ->disableOriginalConstructor()
        ->getMock();

        $magentoNormalizer = $this->getProductNormalizerMock();

        $productNormalizerGuesserMock->expects($this->any())
            ->method('getProductNormalizer')
            ->will($this->returnValue($magentoNormalizer));

        return $productNormalizerGuesserMock;
    }

    /**
     * Get a WebserviceGuesser mock which will return a exception thrower normalizer
     * @param \Exception $exception
     *
     * @return WebserviceGuesserMock
     */
    protected function getExceptionNormalizerGuesserMock(\Exception $exception)
    {
        $productNormalizerGuesserMock = $this->getMockBuilder(
            'Pim\Bundle\MagentoConnectorBundle\Guesser\NormalizerGuesser'
        )
        ->disableOriginalConstructor()
        ->getMock();

        $magentoNormalizer = $this->getExceptionNormalizerMock($exception);

        $productNormalizerGuesserMock->expects($this->any())
            ->method('getProductNormalizer')
            ->will($this->returnValue($magentoNormalizer));

        return $productNormalizerGuesserMock;
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
            ->will(
                $this->returnValue(
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
                )
            );

        return $mock;
    }

    /**
     * Get a metricConverter mock
     * @return MetricConverterMock
     */
    protected function getMetricConverterMock()
    {
        return $this->getMockBuilder('Pim\Bundle\TransformBundle\Converter\MetricConverter')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Get the association type manager mock
     * @return AssociationTypeManager
     */
    protected function getAssociationTypeManagerMock()
    {
        $associationTypeManager =
            $this->getMockBuilder('Pim\Bundle\MagentoConnectorBundle\Manager\AssociationTypeManager')
                ->disableOriginalConstructor()
                ->getMock();

        return $associationTypeManager;
    }
}
