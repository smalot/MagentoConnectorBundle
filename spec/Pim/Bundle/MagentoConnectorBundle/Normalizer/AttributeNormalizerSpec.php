<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Normalizer;

use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\AttributeOption;
use Pim\Bundle\CatalogBundle\Entity\AttributeOptionValue;
use Pim\Bundle\CatalogBundle\Entity\AttributeTranslation;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\MagentoConnectorBundle\Helper\AttributeMappingHelper;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

class AttributeNormalizerSpec extends ObjectBehavior
{
    function let(AttributeMappingHelper $mappingHelper)
    {
        $this->beConstructedWith($mappingHelper);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('\Pim\Bundle\MagentoConnectorBundle\Normalizer\AttributeNormalizer');
    }

    function it_returns_true_if_the_normalizer_can_support_given_data(AbstractAttribute $attribute)
    {
        $this->supportsNormalization($attribute, 'api_import')->shouldReturn(true);
    }

    function it_returns_false_if_the_normalizer_can_not_support_given_data($object)
    {
        $this->supportsNormalization($object, 'api_import')->shouldReturn(false);
    }

    function it_returns_false_if_the_normalizer_can_not_support_given_data_because_of_the_format(
        AbstractAttribute $attribute
    ) {
        $this->supportsNormalization($attribute, 'foo_bar')->shouldReturn(false);
    }

    public function it_sets_serializer_as_a_normalizer(Serializer $serializer)
    {
        $this->setSerializer($serializer)->shouldReturn(null);
    }

    public function it_does_not_set_an_object_as_a_normalizer(SerializerInterface $object)
    {
        $this->shouldThrow('\LogicException')->during('setSerializer', [$object]);
    }

    function it_normalizes_a_supported_attribute(
        AbstractAttribute $attribute,
        AttributeTranslation $translation,
        $mappingHelper
    ) {
        $context = [
            'defaultLocale' => 'en_US',
            'visibility' => true
        ];

        $translation->getLabel()->willReturn('My attribute');

        $attribute->getBackendType()->willReturn('text');
        $attribute->getAttributeType()->willReturn('pim_catalog_text');
        $attribute->getCode()->willReturn('attribute_code');
        $attribute->getDefaultValue()->willReturn('value');
        $attribute->getTranslation('en_US')->willReturn($translation);
        $attribute->isRequired()->willReturn(true);
        $attribute->isUnique()->willReturn(false);

        $mappingHelper->getMagentoAttributeType('pim_catalog_text')->shouldBeCalled()->willReturn('text');
        $mappingHelper->getMagentoBackendType('text')->shouldBeCalled()->willReturn('text');

        $this->normalize($attribute, 'api_import', $context)->shouldReturn([
            'attribute_id'     => 'attribute_code',
            'default'          => 'value',
            'input'            => 'text',
            'type'             => 'text',
            'label'            => 'My attribute',
            'global'           => 0,
            'required'         => 1,
            'visible_on_front' => 1,
            'unique'           => 0
        ]);
    }

    function it_normalizes_an_attribute_with_options(
        AbstractAttribute $attribute,
        AttributeTranslation $translation,
        AttributeOption $option,
        AttributeOptionValue $optionValue,
        Serializer $normalizer,
        $mappingHelper
    ) {
        $context = [
            'defaultLocale'    => 'en_US',
            'defaultStoreView' => 'Default',
            'visibility'       => true,
            'storeViewMapping' => [
                'fr_FR' => 'fr_fr'
            ],
        ];

        $translation->getLabel()->willReturn('My attribute');

        $attribute->getAttributeType()->willReturn('pim_catalog_simpleselect');
        $attribute->getBackendType()->willReturn('option');
        $attribute->getCode()->willReturn('attribute_code');
        $attribute->getDefaultValue()->willReturn('My option');
        $attribute->getTranslation('en_US')->willReturn($translation);
        $attribute->isRequired()->willReturn(true);
        $attribute->isUnique()->willReturn(false);
        $attribute->getOptions()->willReturn([$option]);

        $option->getCode()->willReturn('option_code');
        $option->getSortOrder()->willReturn(0);
        $option->getOptionValues()->willReturn([$optionValue]);

        $mappingHelper->getMagentoAttributeType('pim_catalog_simpleselect')->shouldBeCalled()->willReturn('select');
        $mappingHelper->getMagentoBackendType('option')->shouldBeCalled()->willReturn('varchar');

        $normalizer->normalize($optionValue, 'api_import', $context)->willReturn([0 => 'My option']);

        $this->setSerializer($normalizer);

        $this->normalize($attribute, 'api_import', $context)->shouldReturn([
            'attribute_id'     => 'attribute_code',
            'default'          => 'My option',
            'input'            => 'select',
            'type'             => 'varchar',
            'label'            => 'My attribute',
            'global'           => 0,
            'required'         => 1,
            'visible_on_front' => 1,
            'unique'           => 0,
            'option'           => [
                'value' => [
                    'option_code' => [0 => 'My option']
                ],
                'order' => [
                    'option_code' => 0
                ]
            ]
        ]);
    }
}
