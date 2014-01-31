<?php

namespace Pim\Bundle\MagentoConnectorBundle\Tests\Unit\Normalizer;

use Pim\Bundle\MagentoConnectorBundle\Normalizer\ProductNormalizer;

/**
 * Test related class
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductNormalizerTest extends \PHPUnit_Framework_TestCase
{
    const CURRENCY          = 'EUR';
    const DEFAULT_LOCALE    = 'en_US';
    const CHANNEL           = 'channel';
    const ENABLED           = true;
    const VISIBILITY        = 4;
    const MAGENTO_URL       = 'http://mangeto.dev';
    const PIM_GROUPED       = 'grouped';

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->channelManager         = $this->getChannelManagerMock();
        $this->mediaManager           = $this->getMediaManagerMock();
        $this->productValueNormalizer = $this->getProductValueNormalizerMock();
        $this->categoryMappingManager = $this->getCategoryMappingManagerMock();
        $this->associationTypeManager = $this->getAssociationTypeManagerMock();

        $this->normalizer = new ProductNormalizer(
            $this->channelManager,
            $this->mediaManager,
            $this->productValueNormalizer,
            $this->categoryMappingManager,
            $this->associationTypeManager,
            self::ENABLED,
            self::VISIBILITY,
            self::CURRENCY,
            self::MAGENTO_URL
        );
    }

    /**
     * Test the corresponding method
     */
    public function testNormalizeCreate()
    {
        $context = $this->getContext();

        $product = $this->getProductMock($this->getSampleAttributes());

        $this->normalizer->normalize($product, null, $context);
    }

    /**
     * Test the corresponding method
     */
    public function testNormalizeUpdate()
    {
        $context = $this->getContext();
        $context['create'] = false;

        $product = $this->getProductMock($this->getSampleAttributes());

        $this->normalizer->normalize($product, null, $context);
    }

    /**
     * Get an unknow attribute
     * @return array
     */
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

    /**
     * Get a attribute with an invalid scope for magento
     * @return array
     */
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

    /**
     * Get an attribute with an invalid option
     * @return array
     */
    protected function getAttributeWithInvalidOption()
    {
        $colors = new \Doctrine\Common\Collections\ArrayCollection();

        $colorGreen = $this->getMock('\Pim\Bundle\CatalogBundle\Entity\AttributeOption');
        $colorGreen->expects($this->any())
            ->method('getCode')
            ->will($this->returnValue('green'));

        $colors->add($colorGreen);

        return array(
            'colors' => array(
                'scopable'     => false,
                'translatable' => false,
                'value'        => $colors,
                'code'         => 'colors'
            ),
        );
    }

    /**
     * Get a default context
     * @return array
     */
    protected function getContext()
    {
        return array(
            'create' => false,
            'magentoStoreViews'        => array(
                array('code' => 'admin'),
                array('code' => 'en_us'),
                array('code' => 'fr_fr'),
            ),
            'storeViewMapping' => array(array('test', 'fr_FR')),
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
            'categoryMapping'          => array(),
            'pimGrouped'               => self::PIM_GROUPED,
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
                    'required' => true,
                    'scope'    => 'global',
                ),
                'description' => array(
                    'code'     => 'description',
                    'required' => true,
                    'scope'    => 'store',
                ),
                'short_description' => array(
                    'code' => 'short_description',
                    'required' => true,
                    'scope' => 'store',
                ),
                'sku' => array(
                    'code'     => 'sku',
                    'required' => true,
                    'scope'    => 'global',
                ),
                'weight' => array(
                    'code'     => 'weight',
                    'required' => true,
                    'scope'    => 'global',
                ),
                'status' => array(
                    'code'     => 'status',
                    'required' => true,
                    'scope'    => 'website',
                ),
                'visibility' => array(
                    'code'     => 'visibility',
                    'required' => true,
                    'scope'    => 'store',
                ),
                'created_at' => array(
                    'code'     => 'created_at',
                    'required' => true,
                    'scope'    => 'global',
                ),
                'updated_at' => array(
                    'code'     => 'updated_at',
                    'required' => true,
                    'scope'    => 'global',
                ),
                'price' => array(
                    'code'     => 'price',
                    'required' => true,
                    'scope'    => 'global',
                ),
                'colors' => array(
                    'code'     => 'colors',
                    'required' => true,
                    'scope'    => 'global',
                ),
                'color' => array(
                    'code'     => 'color',
                    'required' => true,
                    'scope'    => 'global',
                ),
                'size' => array(
                    'code'     => 'size',
                    'required' => true,
                    'scope'    => 'global',
                ),
                'sizes' => array(
                    'code'     => 'sizes',
                    'required' => true,
                    'scope'    => 'global',
                ),
                'tax_class_id' => array(
                    'code'     => 'tax_class_id',
                    'required' => true,
                    'scope'    => 'website',
                ),
                'image' => array(
                    'code'     => 'image',
                    'required' => true,
                    'scope'    => 'global'
                )
            )
        );
    }

    /**
     * Test the supported normalization
     */
    public function testSupportsNormalization()
    {
        $product = $this->getProductMock($this->getSampleAttributes());

        $this->assertTrue($this->normalizer->supportsNormalization($product, 'MagentoArray'));
    }

    /**
     * Get a simple product mock
     * @param array $attributes product attributes
     *
     * @return ProductMock
     */
    protected function getProductMock($attributes)
    {
        $values = $this->getSampleProductValues($attributes);

        $product = $this->getMock('Pim\Bundle\CatalogBundle\Model\Product');

        $product->expects($this->any())
            ->method('getValues')
            ->will($this->returnValue($values));

        $product->expects($this->any())
            ->method('getCategories')
            ->will($this->returnValue(array()));

        return $product;
    }

    /**
     * Get an array of sample attributes
     * @return array
     */
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

        $file = $this->getMockBuilder('Symfony\Component\HttpFoundation\File\File')
            ->disableOriginalConstructor()
            ->getMock();
        $file->expects($this->any())
            ->method('getMimeType')
            ->will($this->returnValue('image/jpeg'));

        $image = $this->getMock('Pim\Bundle\CatalogBundle\Model\Media');
        $image->expects($this->any())
            ->method('getFile')
            ->will($this->returnValue($file));

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
            'image' => array(
                'scopable'     => false,
                'translatable' => false,
                'value'        => $image,
                'code'         => 'image'
            )
        );
    }

    /**
     * Get sample product values
     * @param array $attributes Array of attributes
     *
     * @see getSampleAttributes
     * @return ArrayCollection
     */
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

    /**
     * Get an attribute mock based on an attribute array
     * @param array $value
     *
     * @see getSampleAttributes
     * @return AttributeMock
     */
    protected function getAttributeMock($value)
    {
        $attribute = $this->getMockBuilder('Pim\Bundle\CatalogBundle\Entity\Attribute')
            ->disableOriginalConstructor()
            ->setMethods(array('getCode', 'isLocalizable', 'isScopable'))
            ->getMock();
        $attribute->expects($this->any())
            ->method('getCode')
            ->will($this->returnValue($value['code']));
        $attribute->expects($this->any())
            ->method('isLocalizable')
            ->will($this->returnValue($value['translatable']));
        $attribute->expects($this->any())
            ->method('isScopable')
            ->will($this->returnValue($value['scopable']));

        return $attribute;
    }

    /**
     * Get the channelManager mock
     * @return ChannelManagerMock
     */
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
            ->method('getChannelByCode')
            ->with(self::CHANNEL)
            ->will($this->returnValue($channel));

        return $channelManager;
    }

    /**
     * Get the media manager mock
     * @return MediaManager
     */
    protected function getMediaManagerMock()
    {
        $mediaManager = $this->getMockBuilder('Pim\Bundle\CatalogBundle\Manager\MediaManager')
            ->disableOriginalConstructor()
            ->getMock();

        $mediaManager->expects($this->any())
            ->method('getBase64')
            ->will($this->returnValue('imageBase64Encoded'));

        return $mediaManager;
    }

    /**
     * Get the value normalizer mock
     * @return ProductValueNormalizer
     */
    protected function getProductValueNormalizerMock()
    {
        $productValueNormalizer =
            $this->getMockBuilder('Pim\Bundle\MagentoConnectorBundle\Normalizer\ProductValueNormalizer')
                ->disableOriginalConstructor()
                ->getMock();

        $productValueNormalizer->expects($this->any())
            ->method('normalize')
            ->will($this->returnValue(array('attribute' => '12')));

        return $productValueNormalizer;
    }

    /**
     * Get the category mapping manager mock
     * @return CategoryMappingManager
     */
    protected function getCategoryMappingManagerMock()
    {
        $categoryMappingManager =
            $this->getMockBuilder('Pim\Bundle\MagentoConnectorBundle\Manager\CategoryMappingManager')
                ->disableOriginalConstructor()
                ->getMock();

        return $categoryMappingManager;
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
