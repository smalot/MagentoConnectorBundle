<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Normalizer;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\Attribute;
use Pim\Bundle\CatalogBundle\Entity\AttributeOption;
use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\CatalogBundle\Entity\Group;
use Pim\Bundle\CatalogBundle\Model\Product;
use Pim\Bundle\CatalogBundle\Model\ProductValue;
use Pim\Bundle\MagentoConnectorBundle\Helper\PriceHelper;
use Pim\Bundle\MagentoConnectorBundle\Helper\ValidProductHelper;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\Exception\TypeNotFoundException;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Serializer;

class VariantGroupHelperSpec extends ObjectBehavior
{
    public function let(PriceHelper $priceHelper, ValidProductHelper $validProductHelper)
    {
        $this->beConstructedWith($priceHelper, $validProductHelper);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType('\Pim\Bundle\MagentoConnectorBundle\Normalizer\VariantGroupHelper');
    }

    public function it_sets_a_normalizer(NormalizerInterface $normalizer)
    {
        $this->setSerializer($normalizer)->shouldReturn(null);
    }

    public function it_normalizes_a_variant_group_to_the_api_import_format(
        Group $group,
        Attribute $variantAxis,
        Attribute $attribute1,
        Attribute $attribute2,
        Product $product1,
        Product $product2,
        Channel $channel,
        Serializer $normalizer,
        ProductValue $productValue,
        AttributeOption $option,
        ProductValue $productValue2,
        AttributeOption $option2,
        Collection $collection,
        $validProductHelper,
        $priceHelper
    ) {
        $context = [
            'channel' => $channel,
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

        $simpleProduct = [
            [
                'sku'                 => 'sku-000',
                '_type'               => 'simple',
                '_product_websites'   => 'base',
                'status'              => 1,
                'visibility'          => 4,
                '_attribute_set'      => 'my_family',
                'created_at'          => '2042-01-01 13:37:00',
                'updated_at'          => '2042-01-01 13:37:00',
                'my_metric_attribute' => '420.000',
                '_store'              => 'Default',
                'variant_attribute'   => 'baz'
            ],
            [
                'bar'    => 'foo',
                '_store' => 'fr_fr',
            ],
            [
                '_category'      => 'my_category_2/my_category',
                '_root_category' => 'Default Category'
            ]
        ];

        $group->getAttributes()->willReturn($collection);
        $group->getProducts()->willReturn(new ArrayCollection([$product1, $product2]));

        $collection->toArray()->willReturn([$variantAxis]);

        $variantAxis->getCode()->willReturn('variant_attribute');

        $validProductHelper->getValidProducts(
            $channel,
            Argument::type('\Doctrine\Common\Collections\ArrayCollection')
        )->willReturn([$product1, $product2]);

        $priceHelper->computePriceChanges(Argument::cetera())->shouldBeCalled();

        $normalizer->normalize($product1, 'api_import', $context)->willReturn($simpleProduct);
        $normalizer->normalize($option, 'api_import', $context)->willReturn('quz');
        $normalizer->normalize($option2, 'api_import', $context)->willReturn('fum');

        $product1->getAttributes()->willReturn([$attribute1, $attribute2]);
        $product1->getValue('variant_attribute')->willReturn($productValue);
        $product1->getValue('custom_attribute')->shouldNotBeCalled();
        $product1->getIdentifier()->willReturn('sku-000');

        $product2->getAttributes()->willReturn([$attribute1]);
        $product2->getValue('variant_attribute')->willReturn($productValue2);
        $product2->getIdentifier()->willReturn('sku-005');

        $attribute1->getCode()->willReturn('variant_attribute');
        $attribute2->getCode()->willReturn('custom_attribute');

        $productValue->getOption()->willReturn($option);
        $productValue2->getOption()->willReturn($option2);

        $this->setSerializer($normalizer);
        $this->normalize($group, 'api_import', $context)->shouldReturn([
            [
                'sku'                 => 'sku-000',
                '_type'               => 'configurable',
                '_product_websites'   => 'base',
                'status'              => 1,
                'visibility'          => 4,
                '_attribute_set'      => 'my_family',
                'created_at'          => '2042-01-01 13:37:00',
                'updated_at'          => '2042-01-01 13:37:00',
                'my_metric_attribute' => '420.000',
                '_store'              => 'Default',
            ],
            [
                'bar'    => 'foo',
                '_store' => 'fr_fr',
            ],
            [
                '_category'      => 'my_category_2/my_category',
                '_root_category' => 'Default Category'
            ],
            [
                '_super_products_sku' => 'sku-000',
                '_super_attribute_code' => 'variant_attribute',
                '_super_attribute_option' => 'quz',
                '_super_attribute_price_corr' => 0
            ],
            [
                '_super_products_sku' => 'sku-005',
                '_super_attribute_code' => 'variant_attribute',
                '_super_attribute_option' => 'fum',
                '_super_attribute_price_corr' => 0
            ]
        ]);
    }

    public function it_throws_an_error_if_the_configurable_can_not_be_build_from_a_simple_product(
        Group $group,
        Attribute $variantAxis,
        Product $product1,
        Product $product2,
        Channel $channel,
        Serializer $normalizer,
        Collection $collection,
        $validProductHelper,
        $priceHelper
    ) {
        $context = [
            'channel' => $channel,
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

        $simpleProduct = [
            [
                'sku'                 => 'sku-000',
                '_product_websites'   => 'base',
                'status'              => 1,
                'visibility'          => 4,
                '_attribute_set'      => 'my_family',
                'created_at'          => '2042-01-01 13:37:00',
                'updated_at'          => '2042-01-01 13:37:00',
                'my_metric_attribute' => '420.000',
                '_store'              => 'Default',
                'variant_attribute'   => 'baz'
            ],
            [
                'bar'    => 'foo',
                '_store' => 'fr_fr',
            ],
            [
                '_category'      => 'my_category_2/my_category',
                '_root_category' => 'Default Category'
            ]
        ];

        $group->getAttributes()->willReturn($collection);
        $group->getProducts()->willReturn(new ArrayCollection([$product1, $product2]));

        $collection->toArray()->willReturn([$variantAxis]);

        $variantAxis->getCode()->willReturn('variant_attribute');

        $validProductHelper->getValidProducts(
            $channel,
            Argument::type('\Doctrine\Common\Collections\ArrayCollection')
        )->willReturn([$product1, $product2]);

        $priceHelper->computePriceChanges(Argument::cetera())->shouldBeCalled();

        $normalizer->normalize($product1, 'api_import', $context)->willReturn($simpleProduct);

        $this->setSerializer($normalizer);
        $this->shouldThrow(
            new TypeNotFoundException(
                sprintf(
                    'Simple product to transform : %s' . PHP_EOL .
                    'Can\'t transform simple product to configurable. ' .
                    'The field "_type" is not found in the simple product ' .
                    'and can not be switch to "configurable" from "simple".',
                    json_encode($simpleProduct)
                )
            )
        )->duringNormalize($group, 'api_import', $context);
    }
}
