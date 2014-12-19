<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Normalizer;

use Doctrine\Common\Collections\Collection;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\AttributeOption;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Model\AbstractProductMedia;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValue;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\Exception\BackendTypeNotFoundException;
use Prophecy\Argument;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

class ProductValueNormalizerSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType('\Pim\Bundle\MagentoConnectorBundle\Normalizer\ProductValueNormalizer');
    }

    public function it_returns_true_if_the_normalizer_can_support_given_data(ProductValue $productValue)
    {
        $this->supportsNormalization($productValue, 'api_import')->shouldReturn(true);
    }

    public function it_returns_false_if_the_normalizer_can_not_support_given_data($object)
    {
        $this->supportsNormalization($object, 'api_import')->shouldReturn(false);
    }

    public function it_returns_false_if_the_normalizer_can_not_support_given_data_because_of_the_format(
        ProductValue $productValue
    ) {
        $this->supportsNormalization($productValue, 'foo_bar')->shouldReturn(false);
    }

    public function it_sets_serializer_as_a_normalizer(Serializer $serializer)
    {
        $this->setSerializer($serializer)->shouldReturn(null);
    }

    public function it_does_not_set_an_object_as_a_normalizer(SerializerInterface $object)
    {
        $this->shouldThrow('\LogicException')->during('setSerializer', [$object]);
    }

    public function it_normalizes_a_product_value_not_localized_in_default_store_view(
        ProductValue $productValue,
        AbstractAttribute $attribute,
        Serializer $normalizer
    ) {
        $context = [
            'defaultStoreView' => 'Default',
            'defaultLocale'    => 'en_US',
            'storeViewMapping' => [
                'fr_FR' => 'fr_fr'
            ]
        ];

        $productValue->getLocale()->willReturn('fr_FR');
        $productValue->getAttribute()->willReturn($attribute);
        $productValue->getData()->willReturn('foo');

        $attribute->getCode()->willReturn('bar');
        $attribute->getBackendType()->shouldBeCalled()->willReturn('varchar');

        $normalizer->normalize(Argument::cetera())->shouldNotBeCalled();

        $this->setSerializer($normalizer);
        $this->normalize($productValue, 'api_import', $context)->shouldReturn(['fr_fr' => ['bar' => 'foo']]);
    }

    public function it_normalizes_a_product_value_localized_in_default_store_view(
        ProductValue $productValue,
        AbstractAttribute $attribute,
        Serializer $normalizer
    ) {
        $context = [
            'defaultStoreView' => 'Default',
            'defaultLocale'    => 'en_US',
            'storeViewMapping' => [
                'fr_FR' => 'fr_fr'
            ]
        ];

        $productValue->getLocale()->willReturn('en_US');
        $productValue->getAttribute()->willReturn($attribute);
        $productValue->getData()->willReturn('foo');

        $attribute->getCode()->willReturn('bar');
        $attribute->getBackendType()->shouldBeCalled()->willReturn('varchar');

        $normalizer->normalize(Argument::cetera())->shouldNotBeCalled();

        $this->setSerializer($normalizer);
        $this->normalize($productValue, 'api_import', $context)->shouldReturn(['Default' => ['bar' => 'foo']]);
    }

    public function it_throws_an_exception_if_the_product_value_can_not_be_normalized_because_the_attribute_backend_type_is_not_supported(
        ProductValue $productValue,
        ProductValue $identifier,
        AbstractAttribute $attribute,
        Serializer $normalizer,
        ProductInterface $product
    ) {
        $context = [
            'defaultStoreView' => 'Default',
            'defaultLocale'    => 'en_US',
            'storeViewMapping' => [
                'fr_FR' => 'fr_fr'
            ]
        ];

        $productValue->getLocale()->willReturn('en_US');
        $productValue->getAttribute()->willReturn($attribute);
        $productValue->getData()->willReturn('foo');
        $productValue->getEntity()->willReturn($product);

        $attribute->getCode()->willReturn('bar');
        $attribute->getBackendType()->shouldBeCalled()->willReturn('not_supported');

        $normalizer->normalize(Argument::cetera())->shouldNotBeCalled();

        $product->getIdentifier()->willReturn($identifier);
        $identifier->__toString()->willReturn('sku-001');

        $this->setSerializer($normalizer);
        $this->shouldThrow(new BackendTypeNotFoundException(
            sprintf(
                'Backend type "not_supported" of attribute "bar" from product "sku-001" is not supported yet in ' .
                'ProductValueNormalizer and can not be normalized.'
            )
        ))->duringNormalize($productValue, 'api_import', $context);
    }

    public function it_normalizes_a_decimal_product_value(
        ProductValue $productValue,
        AbstractAttribute $attribute,
        Serializer $normalizer
    ) {
        $context = [
            'defaultStoreView' => 'Default',
            'defaultLocale'    => 'en_US',
            'storeViewMapping' => [
                'fr_FR' => 'fr_fr'
            ]
        ];

        $productValue->getLocale()->willReturn('en_US');
        $productValue->getAttribute()->willReturn($attribute);
        $productValue->getData()->willReturn('42.000');

        $attribute->getCode()->willReturn('my_decimal_attribute');
        $attribute->getBackendType()->shouldBeCalled()->willReturn('decimal');

        $normalizer->normalize(Argument::cetera())->shouldNotBeCalled();

        $this->setSerializer($normalizer);
        $this->normalize($productValue, 'api_import', $context)->shouldReturn([
            'Default' => [
                'my_decimal_attribute' => (double) 42
            ]
        ]);
    }

    public function it_normalizes_a_boolean_product_value(
        ProductValue $productValue,
        AbstractAttribute $attribute,
        Serializer $normalizer
    ) {
        $context = [
            'defaultStoreView' => 'Default',
            'defaultLocale'    => 'en_US',
            'storeViewMapping' => [
                'fr_FR' => 'fr_fr'
            ]
        ];

        $productValue->getLocale()->willReturn('en_US');
        $productValue->getAttribute()->willReturn($attribute);
        $productValue->getData()->willReturn(true);

        $attribute->getCode()->willReturn('my_boolean_attribute');
        $attribute->getBackendType()->shouldNotBeCalled();

        $normalizer->normalize(Argument::cetera())->shouldNotBeCalled();

        $this->setSerializer($normalizer);
        $this->normalize($productValue, 'api_import', $context)->shouldReturn([
            'Default' => ['my_boolean_attribute' => 1]
        ]);
    }

    public function it_normalizes_a_multiselect_product_value_localized_in_default_store_view(
        ProductValue $productValue,
        AbstractAttribute $attribute,
        Serializer $normalizer,
        Collection $optionColl
    ) {
        $context = [
            'defaultStoreView' => 'Default',
            'defaultLocale'    => 'en_US',
            'storeViewMapping' => [
                'fr_FR' => 'fr_fr'
            ]
        ];

        $productValue->getLocale()->willReturn('en_US');
        $productValue->getAttribute()->willReturn($attribute);
        $productValue->getData()->willReturn($optionColl);

        $attribute->getCode()->willReturn('my_multiselect_attribute');

        $normalizer->normalize($optionColl, 'api_import', $context)->shouldBeCalled()->willReturn(['option1', 'option2']);

        $this->setSerializer($normalizer);
        $this->normalize($productValue, 'api_import', $context)->shouldReturn([
            [
                '_store' => '',
                'my_multiselect_attribute' => 'option1'
            ],
            [
                '_store' => '',
                'my_multiselect_attribute' => 'option2'
            ]
        ]);
    }

    public function it_normalizes_an_attribute_option_product_value(
        ProductValue $productValue,
        AbstractAttribute $attribute,
        Serializer $normalizer,
        AttributeOption $option
    ) {
        $context = [
            'defaultStoreView' => 'Default',
            'defaultLocale'    => 'en_US',
            'storeViewMapping' => [
                'fr_FR' => 'fr_fr'
            ]
        ];

        $productValue->getLocale()->willReturn('en_US');
        $productValue->getAttribute()->willReturn($attribute);
        $productValue->getData()->willReturn($option);

        $attribute->getCode()->willReturn('my_option_attribute');

        $normalizer->normalize($option, 'api_import', $context)->shouldBeCalled()->willReturn('option1');

        $this->setSerializer($normalizer);
        $this->normalize($productValue, 'api_import', $context)->shouldReturn(['Default' => ['my_option_attribute' => 'option1']]);
    }

    public function it_normalizes_a_media_product_value(
        ProductValue $productValue,
        AbstractAttribute $attribute,
        Serializer $normalizer,
        AbstractProductMedia $media
    ) {
        $context = [
            'defaultStoreView' => 'Default',
            'defaultLocale'    => 'en_US',
            'storeViewMapping' => [
                'fr_FR' => 'fr_fr'
            ]
        ];

        $productValue->getLocale()->willReturn('en_US');
        $productValue->getAttribute()->willReturn($attribute);
        $productValue->getData()->willReturn($media);

        $attribute->getCode()->willReturn('my_media_attribute');

        $normalizer->normalize($media, 'api_import', $context)->shouldBeCalled()->willReturn([
            [
                'my_media_attribute' => '1-sku_000-my_media_attribute---my_image.jpg',
                'my_media_attribute_content' => 'bar_base64_code'
            ]
        ]);

        $this->setSerializer($normalizer);
        $this->normalize($productValue, 'api_import', $context)->shouldReturn([
            [
                'my_media_attribute' => '1-sku_000-my_media_attribute---my_image.jpg',
                'my_media_attribute_content' => 'bar_base64_code',
                '_store' => ''
            ]
        ]);
    }
}
