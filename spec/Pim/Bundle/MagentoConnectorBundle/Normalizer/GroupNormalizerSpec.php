<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Normalizer;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\Group;
use Pim\Bundle\CatalogBundle\Entity\GroupType;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\VariantGroupHelper;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

class GroupNormalizerSpec extends ObjectBehavior
{
    public function let(VariantGroupHelper $variantGroupHelper)
    {
        $this->beConstructedWith($variantGroupHelper);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType('\Pim\Bundle\MagentoConnectorBundle\Normalizer\GroupNormalizer');
    }

    public function it_returns_true_if_the_normalizer_can_support_given_data(Group $group)
    {
        $this->supportsNormalization($group, 'api_import')->shouldReturn(true);
    }

    public function it_returns_false_if_the_normalizer_can_not_support_given_data($object)
    {
        $this->supportsNormalization($object, 'api_import')->shouldReturn(false);
    }

    public function it_returns_false_if_the_normalizer_can_not_support_given_data_because_of_the_format(
        Group $group
    ) {
        $this->supportsNormalization($group, 'foo_bar')->shouldReturn(false);
    }

    public function it_sets_serializer_as_a_normalizer(Serializer $serializer)
    {
        $this->setSerializer($serializer)->shouldReturn(null);
    }

    public function it_does_not_set_an_object_as_a_normalizer(SerializerInterface $object)
    {
        $this->shouldThrow('\LogicException')->during('setSerializer', [$object]);
    }

    public function it_redirects_a_variant_group_to_the_helper_and_returns_normalized_data(
        GroupType $groupType,
        Group $group,
        Serializer $normalizer,
        $variantGroupHelper
    ) {
        $configurable = [
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
        ];

        $group->getType()->willReturn($groupType);
        $groupType->isVariant()->willReturn(true);

        $variantGroupHelper->setSerializer($normalizer)->shouldBeCalled();
        $variantGroupHelper->normalize($group, 'api_import', [])->willReturn($configurable);

        $this->setSerializer($normalizer);
        $this->normalize($group, 'api_import', [])->shouldReturn($configurable);
    }

    public function it_returns_an_empty_array_if_group_to_normalize_is_not_a_variant_group(
        Group $group,
        GroupType $groupType
    ) {
        $group->getType()->willReturn($groupType);
        $groupType->isVariant()->willReturn(false);

        $this->normalize($group, 'api_import', [])->shouldReturn([]);
    }
}
