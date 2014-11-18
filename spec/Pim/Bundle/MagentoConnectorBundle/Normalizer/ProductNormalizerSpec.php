<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Normalizer;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\Category;
use Pim\Bundle\CatalogBundle\Entity\Family;
use Pim\Bundle\CatalogBundle\Model\Product;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValue;
use Pim\Bundle\MagentoConnectorBundle\Helper\MagentoAttributesHelper;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\MappingException;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

class ProductNormalizerSpec extends ObjectBehavior
{
    public function let()
    {
        $attributesHelper = new MagentoAttributesHelper();
        $this->beConstructedWith($attributesHelper);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType('\Pim\Bundle\MagentoConnectorBundle\Normalizer\ProductNormalizer');
    }

    public function it_returns_true_if_the_normalizer_can_support_given_data(ProductInterface $product)
    {
        $this->supportsNormalization($product, 'api_import')->shouldReturn(true);
    }

    public function it_returns_false_if_the_normalizer_can_not_support_given_data($object)
    {
        $this->supportsNormalization($object, 'api_import')->shouldReturn(false);
    }

    public function it_returns_false_if_the_normalizer_can_not_support_given_data_because_of_the_format(
        ProductInterface $product
    ) {
        $this->supportsNormalization($product, 'foo_bar')->shouldReturn(false);
    }

    public function it_sets_serializer_as_a_normalizer(Serializer $serializer)
    {
        $this->setSerializer($serializer)->shouldReturn(null);
    }

    public function it_does_not_set_an_object_as_a_normalizer(SerializerInterface $object)
    {
        $this->shouldThrow('\LogicException')->during('setSerializer', [$object]);
    }

    public function it_normalizes_a_product_to_an_array(
        Serializer $normalizer,
        Product $product,
        ProductValue $productValue1,
        ProductValue $productValue2,
        ProductValue $productValue3,
        ProductValue $productValue4,
        ProductValue $productValue5,
        Family $family,
        \DateTime $datetime,
        Category $category
    ) {
        $context = [
            'defaultStoreView'    => 'Default',
            'defaultLocale'       => 'en_US',
            'website'             => 'base',
            'defaultCurrency'     => 'USD',
            'visibility'          => '4',
            'enabled'             => '1',
            'storeViewMapping'    => [
                'fr_FR' => 'fr_fr'
            ],
            'userCategoryMapping' => [
                'my_category_root' => 'Default Category'
            ]
        ];

        $product->getValues()->willReturn([$productValue1, $productValue2, $productValue3, $productValue4, $productValue5]);
        $product->isEnabled()->willReturn(true);
        $product->getFamily()->willReturn($family);
        $product->getCreated()->willReturn($datetime);
        $product->getUpdated()->willReturn($datetime);
        $product->getCategories()->willReturn([$category]);

        $datetime->format('Y-m-d H:i:s')->willReturn('2042-01-01 13:37:00');
        $family->getCode()->willReturn('my_family');

        $normalizer->normalize($productValue1, 'api_import', $context)->willReturn(['Default' => ['my_metric_attribute' => '420.000']]);
        $normalizer->normalize($productValue2, 'api_import', $context)->willReturn(['fr_fr' => ['bar' => 'foo']]);
        $normalizer->normalize($productValue3, 'api_import', $context)->willReturn([
            [
                '_store'                   => '',
                'my_multiselect_attribute' => 'option1'
            ],
            [
                '_store'                   => '',
                'my_multiselect_attribute' => 'option2'
            ]
        ]);
        $normalizer->normalize($productValue4, 'api_import', $context)->willReturn([
            [
                'my_media_attribute'         => '1-sku_000-my_media_attribute---my_image.jpg',
                'my_media_attribute_content' => 'bar_base64_code',
                '_store'                     => ''
            ]
        ]);
        $normalizer->normalize($category, 'api_import', $context)->willReturn([
            'category' => 'my_category_2/my_category',
            'root'     => 'my_category_root'
        ]);
        $normalizer->normalize($productValue5, 'api_import', $context)->willReturn(['Default' => ['sku' => 'sku-000']]);

        $this->setSerializer($normalizer);
        $this->normalize($product, 'api_import', $context)->shouldReturn([
            [
                'my_metric_attribute' => '420.000',
                'sku'                 => 'sku-000',
                '_type'               => 'simple',
                '_product_websites'   => 'base',
                'status'              => 1,
                'visibility'          => 4,
                '_attribute_set'      => 'my_family',
                'created_at'          => '2042-01-01 13:37:00',
                'updated_at'          => '2042-01-01 13:37:00',
                '_store'              => 'Default'
            ],
            [
                'bar'    => 'foo',
                '_store' => 'fr_fr',
            ],
            [
                '_store'                   => '',
                'my_multiselect_attribute' => 'option1'
            ],
            [
                '_store'                   => '',
                'my_multiselect_attribute' => 'option2'
            ],
            [
                'my_media_attribute'         => '1-sku_000-my_media_attribute---my_image.jpg',
                'my_media_attribute_content' => 'bar_base64_code',
                '_store'                     => ''
            ],
            [
                '_category'      => 'my_category_2/my_category',
                '_root_category' => 'Default Category'
            ]
        ]);
    }

    public function it_throws_an_exception_during_normalization_if_the_mapping_does_not_match_for_root_category(
        Serializer $normalizer,
        Product $product,
        ProductValue $productValue,
        Family $family,
        \DateTime $datetime,
        Category $category
    ) {
        $context = [
            'defaultStoreView'    => 'Default',
            'defaultLocale'       => 'en_US',
            'website'             => 'base',
            'defaultCurrency'     => 'USD',
            'visibility'          => '4',
            'enabled'             => '1',
            'storeViewMapping'    => [
                'fr_FR' => 'fr_fr'
            ],
            'userCategoryMapping' => [
                'Master catalog' => 'Default Category'
            ]
        ];

        $product->getValues()->willReturn([$productValue]);
        $product->isEnabled()->willReturn(true);
        $product->getFamily()->willReturn($family);
        $product->getCreated()->willReturn($datetime);
        $product->getUpdated()->willReturn($datetime);
        $product->getCategories()->willReturn([$category]);

        $datetime->format('Y-m-d H:i:s')->willReturn('2042-01-01 13:37:00');
        $family->getCode()->willReturn('my_family');

        $normalizer->normalize($productValue, 'api_import', $context)->willReturn(['Default' => ['sku' => 'sku-000']]);
        $normalizer->normalize($category, 'api_import', $context)->willReturn([
            'category' => 'my_category_2/my_category',
            'root'     => 'my_category_root'
        ]);

        $this->setSerializer($normalizer);
        $this->shouldThrow(
            new MappingException('Category root "my_category_root" not corresponding with user category mapping')
        )->duringNormalize($product, 'api_import', $context);
    }
}
