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
    const CURRENCY          = 'EUR';

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
        $context = $this->getContext();

        $product = $this->getProductMock($this->getSampleAttributes());

        $this->normalizer->normalize($product, null, $context);
        $this->assertTrue(true);
    }

    /**
     * @expectedException Pim\Bundle\MagentoConnectorBundle\Normalizer\AttributeNotFoundException
     */
    public function testNormalizeAttributeNotFound()
    {
        $context = $this->getContext();

        $product = $this->getProductMock($this->getUnknowAttributes());

        $this->normalizer->normalize($product, null, $context);
        $this->assertTrue(true);
    }

    /**
     * @expectedException Pim\Bundle\MagentoConnectorBundle\Normalizer\InvalidScopeMatchException
     */
    public function testNormalizeInvalidScope()
    {
        $context = $this->getContext();

        $product = $this->getProductMock($this->getInvalidScopeAttributes());

        $this->normalizer->normalize($product, null, $context);
        $this->assertTrue(true);
    }

    protected function getProductMock($attributes)
    {
        $values = $this->getSampleProductValues($attributes);

        $product = $this->getMock('Pim\Bundle\CatalogBundle\Model\Product');

        $product->expects($this->any())
            ->method('getValues')
            ->will($this->returnValue($values));

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

    protected function getSampleProductValues($attributes)
    {
        $values = new \Doctrine\Common\Collections\ArrayCollection();

        foreach ($attributes as $sampleAttribute) {
            $attribute = $this->getAttributeMock($sampleAttribute);

            $productValue = $this->getMock('Pim\Bundle\CatalogBundle\Model\ProductValue');

            $productValue->expects($this->any())
                ->method('getAttribute')
                ->will($this->returnValue($attribute));

            $productValue->expects($this->any())
                ->method('getData')
                ->will($this->returnValue($sampleAttribute['value']));

            $values->add($productValue);
        }

        return $values;
    }

    protected function getAttributeMock($value)
    {
        $attribute = $this->getMockBuilder('Pim\Bundle\FlexibleEntityBundle\Entity\Attribute')
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

    protected function getSampleAttributes()
    {
        $colors = new \Doctrine\Common\Collections\ArrayCollection();
        $colorRed = $this->getMock('\Pim\Bundle\CatalogBundle\Entity\AttributeOption');
        $colorRed->expects($this->any())
            ->method('getCode')
            ->will($this->returnValue('red'));
        $colorBlue = $this->getMock('\Pim\Bundle\CatalogBundle\Entity\AttributeOption');
        $colorBlue->expects($this->any())
            ->method('getCode')
            ->will($this->returnValue('blue'));

        $colors->add($colorRed);
        $colors->add($colorBlue);

        $size = $this->getMock('Pim\Bundle\CatalogBundle\Model\Metric');

        $price = $this->getMock('\Pim\Bundle\CatalogBundle\Model\ProductPrice');
        $price->expects($this->any())
            ->method('getData')
            ->will($this->returnValue(10.22));
        $price->expects($this->any())
            ->method('getCurrency')
            ->will($this->returnValue(self::CURRENCY));

        $prices = new \Doctrine\Common\Collections\ArrayCollection();
        $prices->add($price);

        $sizes = new \Doctrine\Common\Collections\ArrayCollection();
        $sizes->add('XS');

        return array(
            'name' => array(
                'scopable'     => true,
                'translatable' => true,
                'value'        => 'Name',
                'code'         => 'name',
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
            ),
            'status' => array(
                'scopable'     => false,
                'translatable' => true,
                'value'        => true,
                'code'         => 'status',
                'method'       => 'isEnabled',
            ),
            'visibility' => array(
                'scopable'     => false,
                'translatable' => false,
                'value'        => true,
                'code'         => 'visibility',
                'method'       => 'isEnabled',
            ),
            'visibility' => array(
                'scopable'     => false,
                'translatable' => false,
                'value'        => true,
                'code'         => 'visibility',
                'method'       => 'isEnabled',
            ),
            'price' => array(
                'scopable'     => false,
                'translatable' => false,
                'value'        => $prices,
                'code'         => 'price',
            ),
            'tax_class_id' => array(
                'scopable'     => false,
                'translatable' => true,
                'value'        => 0,
                'code'         => 'tax_class_id'
            ),
            'colors' => array(
                'scopable'     => false,
                'translatable' => false,
                'value'        => $colors,
                'code'         => 'colors'
            ),
            'color' => array(
                'scopable'     => false,
                'translatable' => false,
                'value'        => $colorBlue,
                'code'         => 'color'
            ),
            'size' => array(
                'scopable'     => false,
                'translatable' => false,
                'value'        => $size,
                'code'         => 'size'
            ),
            'sizes' => array(
                'scopable'     => false,
                'translatable' => false,
                'value'        => $sizes,
                'code'         => 'sizes'
            ),
        );
    }

    protected function getUnknowAttributes()
    {
        return array(
            'attribute_example' => array(
                'scopable'     => false,
                'translatable' => false,
                'value'        => true,
                'code'         => 'attribute_example',
                'type'         => 'string',
            )
        );
    }

    protected function getInvalidScopeAttributes()
    {
        return array(
            'status' => array(
                'scopable'     => false,
                'translatable' => false,
                'value'        => true,
                'code'         => 'status',
                'method'       => 'isEnabled',
            ),
        );
    }

    protected function getContext()
    {
        return array(
            'magentoStoreViews'        => array(
                array('code' => 'admin'),
                array('code' => 'en_us'),
                array('code' => 'fr_fr'),
            ),
            'magentoAttributesOptions' => array(
                'colors' => array(
                    'blue' => 4,
                    'red' => 3
                ),
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
            'currency'                 => self::CURRENCY,
            'magentoAttributes'        => array(
                'name' => array(
                    'code'     => 'name',
                    'required' => 1,
                    'scope'    => 'global',
                ),
                'description' => array(
                    'code'     => 'description',
                    'required' => 1,
                    'scope'    => 'store',
                ),
                'short_description' => array(
                    'code' => 'short_description',
                    'required' => 1,
                    'scope' => 'store',
                ),
                'sku' => array(
                    'code'     => 'sku',
                    'required' => 1,
                    'scope'    => 'global',
                ),
                'weight' => array(
                    'code'     => 'weight',
                    'required' => 1,
                    'scope'    => 'global',
                ),
                'status' => array(
                    'code'     => 'status',
                    'required' => 1,
                    'scope'    => 'website',
                ),
                'visibility' => array(
                    'code'     => 'visibility',
                    'required' => 1,
                    'scope'    => 'store',
                ),
                'created_at' => array(
                    'code'     => 'created_at',
                    'required' => 1,
                    'scope'    => 'global',
                ),
                'updated_at' => array(
                    'code'     => 'updated_at',
                    'required' => 1,
                    'scope'    => 'global',
                ),
                'price' => array(
                    'code'     => 'price',
                    'required' => 1,
                    'scope'    => 'global',
                ),
                'colors' => array(
                    'code'     => 'colors',
                    'required' => 1,
                    'scope'    => 'global',
                ),
                'color' => array(
                    'code'     => 'color',
                    'required' => 1,
                    'scope'    => 'global',
                ),
                'size' => array(
                    'code'     => 'size',
                    'required' => 1,
                    'scope'    => 'global',
                ),
                'sizes' => array(
                    'code'     => 'sizes',
                    'required' => 1,
                    'scope'    => 'global',
                ),
                'tax_class_id' => array(
                    'code'     => 'tax_class_id',
                    'required' => 1,
                    'scope'    => 'website',
                )
            )
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
}