<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Normalizer;

use Pim\Bundle\MagentoConnectorBundle\Normalizer\ProductValueNormalizer;
use Pim\Bundle\CatalogBundle\Entity\Attribute;
use Pim\Bundle\MagentoConnectorBundle\Mapper\MappingCollection;
use Pim\Bundle\CatalogBundle\Entity\AttributeTranslation;
use Pim\Bundle\CatalogBundle\Model\ProductValue;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class AttributeNormalizerSpec extends ObjectBehavior
{
    function let(ProductValueNormalizer $productValueNormalizer, Attribute $attribute, MappingCollection $attributeMapping, MappingCollection $storeViewMapping)
    {
        $this->beConstructedWith($productValueNormalizer);
        $attribute->isUnique()->willReturn(true);
        $attribute->isRequired()->willReturn(false);
        $attributeMapping->getTarget('attribute_code')->willReturn('attribute_code_mapped');
    }

    function it_supports_validation_of_product_interface_objects(Attribute $attribute)
    {
        $this->supportsNormalization($attribute, 'MagentoArray')->shouldReturn(true);
    }

    function it_normalize_a_new_attribute($attribute, $attributeMapping, $storeViewMapping)
    {
        $context = array(
            'defaultLocale'            => 'locale',
            'magentoAttributes'        => array(),
            'magentoAttributesOptions' => array(),
            'magentoStoreViews'        => array(),
            'attributeMapping'         => $attributeMapping,
            'storeViewMapping'         => $storeViewMapping,
            'create'                   => true
        );


        $attribute->getAttributeType()->willReturn('pim_catalog_text');
        $attribute->isLocalizable()->willReturn(true);
        $attribute->getDefaultValue()->willReturn(null);
        $attribute->getCode()->willReturn('attribute_code');

        $this->normalize($attribute, 'MagentoArray', $context)->shouldReturn(array(
            'attribute_code'                => 'attribute_code_mapped',
            'frontend_input'                => 'text',
            'scope'                         => 'store',
            'default_value'                 => '',
            'is_unique'                     => '1',
            'is_required'                   => '0',
            'apply_to'                      => '',
            'is_configurable'               => '0',
            'is_searchable'                 => '1',
            'is_visible_in_advanced_search' => '1',
            'is_comparable'                 => '1',
            'is_used_for_promo_rules'       => '1',
            'is_visible_on_front'           => '1',
            'used_in_product_listing'       => '1',
            'additional_fields'             => array(),
            'frontend_label'                => array(array('store_id' => 0, 'label' => 'attribute_code')),
        ));
    }

    function it_normalize_an_updated_attribute($attribute, $attributeMapping, $storeViewMapping)
    {
        $context = array(
            'defaultLocale'            => 'locale',
            'magentoAttributes'        => array('attribute_code_mapped' => array('type' => 'text')),
            'magentoAttributesOptions' => array(),
            'magentoStoreViews'        => array(),
            'attributeMapping'         => $attributeMapping,
            'storeViewMapping'         => $storeViewMapping,
            'create'                   => false
        );

        $attribute->getAttributeType()->willReturn('pim_catalog_text');
        $attribute->isLocalizable()->willReturn(true);
        $attribute->getDefaultValue()->willReturn(null);
        $attribute->getCode()->willReturn('attribute_code');

        $this->normalize($attribute, 'MagentoArray', $context)->shouldReturn(array(
            'attribute_code_mapped',
            array(
                'scope'                         => 'store',
                'default_value'                 => '',
                'is_unique'                     => '1',
                'is_required'                   => '0',
                'apply_to'                      => '',
                'is_configurable'               => '0',
                'is_searchable'                 => '1',
                'is_visible_in_advanced_search' => '1',
                'is_comparable'                 => '1',
                'is_used_for_promo_rules'       => '1',
                'is_visible_on_front'           => '1',
                'used_in_product_listing'       => '1',
                'additional_fields'             => array(),
                'frontend_label'                => array(array('store_id' => 0, 'label' => 'attribute_code')),
            )
        ));
    }

    function it_throws_an_exception_if_attribute_type_changed($attribute, $attributeMapping, $storeViewMapping)
    {
        $context = array(
            'defaultLocale'            => 'locale',
            'magentoAttributes'        => array('attribute_code_mapped' => array('type' => 'text')),
            'magentoAttributesOptions' => array(),
            'magentoStoreViews'        => array(),
            'attributeMapping'         => $attributeMapping,
            'storeViewMapping'         => $storeViewMapping,
            'create'                   => false
        );

        $attribute->getAttributeType()->willReturn('pim_catalog_simpleselect');
        $attribute->isLocalizable()->willReturn(true);
        $attribute->getDefaultValue()->willReturn(null);
        $attribute->getCode()->willReturn('attribute_code');

        $this->shouldThrow('Pim\Bundle\MagentoConnectorBundle\Normalizer\Exception\AttributeTypeChangedException')->during('normalize', array($attribute, 'MagentoArray', $context));
    }

    function it_shoulds_not_throws_an_exception_if_attribute_type_change_is_ignored($attribute, $attributeMapping, $storeViewMapping)
    {
        $context = array(
            'defaultLocale'            => 'locale',
            'magentoAttributes'        => array('tax_class_id' => array('type' => 'text')),
            'magentoAttributesOptions' => array(),
            'magentoStoreViews'        => array(),
            'attributeMapping'         => $attributeMapping,
            'storeViewMapping'         => $storeViewMapping,
            'create'                   => false
        );

        $attribute->getAttributeType()->willReturn('pim_catalog_simpleselect');
        $attribute->isLocalizable()->willReturn(true);
        $attribute->getDefaultValue()->willReturn(null);
        $attribute->getCode()->willReturn('tax_class_id');

        $attributeMapping->getTarget('tax_class_id')->willReturn('tax_class_id');

        $this->normalize($attribute, 'MagentoArray', $context)->shouldReturn(array(
            'tax_class_id',
            array(
                'scope'                         => 'store',
                'default_value'                 => '',
                'is_unique'                     => '1',
                'is_required'                   => '0',
                'apply_to'                      => '',
                'is_configurable'               => '1',
                'is_searchable'                 => '1',
                'is_visible_in_advanced_search' => '1',
                'is_comparable'                 => '1',
                'is_used_for_promo_rules'       => '1',
                'is_visible_on_front'           => '1',
                'used_in_product_listing'       => '1',
                'additional_fields'             => array(),
                'frontend_label'                => array(array('store_id' => 0, 'label' => 'tax_class_id')),
            )
        ));
    }

    function it_throws_an_exception_if_attribute_code_is_note_valid_type_changed($attribute, $attributeMapping, $storeViewMapping)
    {
        $context = array(
            'defaultLocale'            => 'locale',
            'magentoAttributes'        => array('attribute_code_mapped' => array('type' => 'text')),
            'magentoAttributesOptions' => array(),
            'magentoStoreViews'        => array(),
            'attributeMapping'         => $attributeMapping,
            'storeViewMapping'         => $storeViewMapping,
            'create'                   => true
        );

        $attribute->getAttributeType()->willReturn('pim_catalog_simpleselect');
        $attribute->isLocalizable()->willReturn(true);
        $attribute->getDefaultValue()->willReturn(null);

        $attribute->getCode()->willReturn('Attribute_code');
        $this->shouldThrow('Pim\Bundle\MagentoConnectorBundle\Normalizer\Exception\InvalidAttributeNameException')->during('normalize', array($attribute, 'MagentoArray', $context));

        $attribute->getCode()->willReturn('2ttribute_code');
        $this->shouldThrow('Pim\Bundle\MagentoConnectorBundle\Normalizer\Exception\InvalidAttributeNameException')->during('normalize', array($attribute, 'MagentoArray', $context));

        $attribute->getCode()->willReturn('attributeCode');
        $this->shouldThrow('Pim\Bundle\MagentoConnectorBundle\Normalizer\Exception\InvalidAttributeNameException')->during('normalize', array($attribute, 'MagentoArray', $context));
    }

    function it_normalizes_all_attribute_labels($attribute, $attributeMapping, $storeViewMapping, AttributeTranslation $translation)
    {
        $context = array(
            'defaultLocale'            => 'locale',
            'magentoAttributes'        => array('attribute_code_mapped' => array('type' => 'text')),
            'magentoAttributesOptions' => array(),
            'magentoStoreViews'        => array(array('store_id' => 1, 'code' => 'fr_fr'), array('store_id' => 2, 'code' => 'test')),
            'attributeMapping'         => $attributeMapping,
            'storeViewMapping'         => $storeViewMapping,
            'create'                   => false
        );

        $attribute->getAttributeType()->willReturn('pim_catalog_text');
        $attribute->isLocalizable()->willReturn(true);
        $attribute->getDefaultValue()->willReturn(null);
        $attribute->getCode()->willReturn('attribute_code');
        $attribute->getTranslations()->willReturn(array($translation));

        $translation->getLocale()->willReturn('de_DE');
        $translation->getLabel()->willReturn('Attribut kod !');

        $storeViewMapping->getSource('fr_fr')->willReturn('fr_FR');
        $storeViewMapping->getSource('test')->willReturn('de_DE');

        $this->normalize($attribute, 'MagentoArray', $context)->shouldReturn(array(
            'attribute_code_mapped',
            array(
                'scope'                         => 'store',
                'default_value'                 => '',
                'is_unique'                     => '1',
                'is_required'                   => '0',
                'apply_to'                      => '',
                'is_configurable'               => '0',
                'is_searchable'                 => '1',
                'is_visible_in_advanced_search' => '1',
                'is_comparable'                 => '1',
                'is_used_for_promo_rules'       => '1',
                'is_visible_on_front'           => '1',
                'used_in_product_listing'       => '1',
                'additional_fields'             => array(),
                'frontend_label'                => array(
                    array('store_id' => 0, 'label' => 'attribute_code'),
                    array('store_id' => 1, 'label' => 'attribute_code'),
                    array('store_id' => 2, 'label' => 'Attribut kod !'),
                ),
            )
        ));
    }

    function it_normalize_a_new_attribute_with_default_value($attribute, $attributeMapping, $storeViewMapping, $productValueNormalizer, ProductValue $productValue)
    {
        $context = array(
            'defaultLocale'            => 'locale',
            'magentoAttributes'        => array(),
            'magentoAttributesOptions' => array(),
            'magentoStoreViews'        => array(),
            'attributeMapping'         => $attributeMapping,
            'storeViewMapping'         => $storeViewMapping,
            'create'                   => true
        );

        $attribute->getAttributeType()->willReturn('pim_catalog_text');
        $attribute->isLocalizable()->willReturn(true);
        $attribute->getDefaultValue()->willReturn($productValue);
        $attribute->getCode()->willReturn('attribute_code');

        $productValueNormalizer->normalize($productValue, 'MagentoArray', Argument::cetera())->willReturn('defaultValue');

        $this->normalize($attribute, 'MagentoArray', $context)->shouldReturn(array(
            'attribute_code'                => 'attribute_code_mapped',
            'frontend_input'                => 'text',
            'scope'                         => 'store',
            'default_value'                 => 'defaultValue',
            'is_unique'                     => '1',
            'is_required'                   => '0',
            'apply_to'                      => '',
            'is_configurable'               => '0',
            'is_searchable'                 => '1',
            'is_visible_in_advanced_search' => '1',
            'is_comparable'                 => '1',
            'is_used_for_promo_rules'       => '1',
            'is_visible_on_front'           => '1',
            'used_in_product_listing'       => '1',
            'additional_fields'             => array(),
            'frontend_label'                => array(array('store_id' => 0, 'label' => 'attribute_code')),
        ));
    }
}
