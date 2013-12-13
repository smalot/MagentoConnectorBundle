<?php

namespace Pim\Bundle\MagentoConnectorBundle\Tests\Unit\Normalizer;

use Pim\Bundle\MagentoConnectorBundle\Normalizer\ProductCreateNormalizer;

/**
 * Test related class
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductCreateNormalizerTest extends \PHPUnit_Framework_TestCase
{
    const PRICE             = '13.37';
    const NAME              = 'Product example';
    const DESCRIPTION       = 'Description';
    const SHORT_DESCRIPTION = 'Short description';
    const WEIGHT            = '10';
    const STATUS            = 1;
    const VISIBILITY        = 4;
    const TAX_CLASS_ID      = 0;
    const DEFAULT_LOCALE    = 'en_US';
    const CHANNEL           = 'channel';
    const SKU               = 'sku-010';

    protected function setUp()
    {
        $this->channelManager = $this->getChannelManagerMock();
        $this->mediaManager   = $this->getMockBuilder('Pim\Bundle\CatalogBundle\Manager\MediaManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->normalizer = new ProductCreateNormalizer(
            $this->channelManager,
            $this->mediaManager
        );
    }

    public function testNormalize()
    {
        $context = array(
            'magentoStoreViews'        => array(
                array('code' => 'admin'),
                array('code' => 'en_us'),
                array('code' => 'fr_fr'),
            ),
            'magentoAttributesOptions' => array(
                'color' => array(
                    'blue' => 4,
                    'red' => 3
                )
            ),
            'taxClassId'               => 4,
            'defaultLocale'            => 'en_US',
            'channel'                  => 'channel',
            'website'                  => 'base',
            'enabled'                  => true,
            'visibility'               => 4,
            'attributeSetId'           => 10,
            'magentoAttributes'        => array(
                array(
                    'code'     => 'name',
                    'required' => 1,
                    'scope'    => 'store',
                ),
                array(
                    'code'     => 'description',
                    'required' => 1,
                    'scope'    => 'store',
                ),
                array(
                    'code' => 'short_description',
                    'required' => 1,
                    'scope' => 'store',
                ),
                array(
                    'code'     => 'sku',
                    'required' => 1,
                    'scope'    => 'global',
                ),
                array(
                    'code'     => 'weight',
                    'required' => 1,
                    'scope'    => 'global',
                ),
                array(
                    'code'     => 'status',
                    'required' => 1,
                    'scope'    => 'website',
                ),
                array(
                    'code'     => 'visibility',
                    'required' => 1,
                    'scope'    => 'store',
                ),
                array(
                    'code'     => 'created_at',
                    'required' => 1,
                    'scope'    => 'global',
                ),
                array(
                    'code'     => 'updated_at',
                    'required' => 1,
                    'scope'    => 'global',
                ),
                array(
                    'code'     => 'price',
                    'required' => 1,
                    'scope'    => 'website',
                ),
                array(
                    'code'     => 'tax_class_id',
                    'required' => 1,
                    'scope'    => 'website',
                )
            )
        );

        $product = $this->getProductMock();

        $this->normalizer->normalize($product, null, $context);
        $this->assertTrue(true);
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

        $locale = $this->getMockBuilder('Pim\Bundle\CatalogBundle\Entity\Locale')
            ->disableOriginalConstructor()
            ->setMethods(array('getCode'))
            ->getMock();

        $locale->expects($this->any())
            ->method('__toString')
            ->will($this->returnValue(self::DEFAULT_LOCALE));

        $channel = $this->getMockBuilder('Pim\Bundle\CatalogBundle\Entity\Channel')
            ->disableOriginalConstructor()
            ->setMethods(array('getLocales'))
            ->getMock();
        $channel->expects($this->once())
            ->method('getLocales')
            ->will($this->returnValue(array($locale)));

        $channelManager
            ->expects($this->any())
            ->method('getChannels')
            ->with(array('code' => self::CHANNEL))
            ->will($this->returnValue(array($channel)));

        return $channelManager;
    }
}