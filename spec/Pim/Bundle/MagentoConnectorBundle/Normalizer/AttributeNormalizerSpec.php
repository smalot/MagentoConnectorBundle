<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Normalizer;

use Pim\Bundle\MagentoConnectorBundle\Normalizer\ProductValueNormalizer;
use Pim\Bundle\CatalogBundle\Entity\Attribute;
use Pim\Bundle\MagentoConnectorBundle\Manager\ProductValueManager;
use Pim\Bundle\MagentoConnectorBundle\Mapper\MappingCollection;
use Pim\Bundle\CatalogBundle\Entity\AttributeTranslation;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class AttributeNormalizerSpec extends ObjectBehavior
{
    protected $baseNormalizedAttribute = array(
        'scope'                         => 'store',
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
        'frontend_label'                => array(array('store_id' => 0, 'label' => 'attribute_code_mapped')),
        'default_value'                 => ''
    );

    protected $baseContext = array(
            'defaultLocale'            => 'locale',
            'magentoAttributes'        => array(),
            'magentoAttributesOptions' => array(),
            'magentoStoreViews'        => array(),
            'create'                   => true
        );

    function let(ProductValueNormalizer $productValueNormalizer, Attribute $attribute, MappingCollection $attributeMapping, MappingCollection $storeViewMapping, ProductValueManager $productValueManager)
    {
        $this->beConstructedWith($productValueNormalizer, $productValueManager);
        $attribute->isUnique()->willReturn(true);
        $attribute->isRequired()->willReturn(false);
        $attribute->isLocalizable()->willReturn(true);
        $attributeMapping->getTarget('attribute_code')->willReturn('attribute_code_mapped');
        $attributeMapping->getTarget('Attribute_code')->willReturn('Attribute_code_mapped');
        $attributeMapping->getTarget('2ttribute_code')->willReturn('2ttribute_code');
        $attributeMapping->getTarget('attributeCode')->willReturn('attributeCode');

        $this->baseContext['attributeMapping'] = $attributeMapping;
        $this->baseContext['storeViewMapping'] = $storeViewMapping;
    }

    function it_supports_validation_of_product_interface_objects(Attribute $attribute)
    {
        $this->supportsNormalization($attribute, 'MagentoArray')->shouldReturn(true);
    }

    function it_normalize_a_new_attribute($attribute)
    {
        $attribute->getAttributeType()->willReturn('pim_catalog_text');
        $attribute->getDefaultValue()->willReturn(null);
        $attribute->getCode()->willReturn('attribute_code');

        $this->normalize($attribute, 'MagentoArray', $this->baseContext)->shouldReturn(array_merge(
            array(
                'attribute_code'                => 'attribute_code_mapped',
                'frontend_input'                => 'text',
            ),
            $this->baseNormalizedAttribute
        ));
    }

    function it_normalize_an_updated_attribute($attribute, $attributeMapping, $storeViewMapping)
    {
        $this->baseContext = array_merge(
            $this->baseContext,
            array(
                'magentoAttributes' => array('attribute_code_mapped' => array('type' => 'text')),
                'create'            => false
            )
        );

        $attribute->getAttributeType()->willReturn('pim_catalog_text');
        $attribute->getDefaultValue()->willReturn(null);
        $attribute->getCode()->willReturn('attribute_code');

        $this->normalize($attribute, 'MagentoArray', $this->baseContext)->shouldReturn(array(
            'attribute_code_mapped',
            $this->baseNormalizedAttribute
        ));
    }

    function it_throws_an_exception_if_attribute_type_changed($attribute, $attributeMapping, $storeViewMapping)
    {
        $this->baseContext = array_merge(
            $this->baseContext,
            array(
                'magentoAttributes' => array('attribute_code_mapped' => array('type' => 'text')),
                'create'            => false
            )
        );

        $attribute->getAttributeType()->willReturn('pim_catalog_simpleselect');
        $attribute->getDefaultValue()->willReturn(null);
        $attribute->getCode()->willReturn('attribute_code');

        $this->shouldThrow('Pim\Bundle\MagentoConnectorBundle\Normalizer\Exception\AttributeTypeChangedException')->during('normalize', array($attribute, 'MagentoArray', $this->baseContext));
    }

    function it_shoulds_not_throws_an_exception_if_attribute_type_change_is_ignored($attribute, $attributeMapping, $storeViewMapping)
    {
        $this->baseContext = array_merge(
            $this->baseContext,
            array(
                'magentoAttributes' => array('tax_class_id' => array('type' => 'text')),
                'create'            => false
            )
        );

        $attribute->getAttributeType()->willReturn('pim_catalog_simpleselect');
        $attribute->getDefaultValue()->willReturn(null);
        $attribute->getCode()->willReturn('tax_class_id');

        $attributeMapping->getTarget('tax_class_id')->willReturn('tax_class_id');

        $this->normalize($attribute, 'MagentoArray', $this->baseContext)->shouldReturn(array(
            'tax_class_id',
            array_merge(
                $this->baseNormalizedAttribute,
                array(
                    'is_configurable' => '1',
                    'frontend_label'  => array(array('store_id' => 0, 'label' => 'tax_class_id')),
                )
            )
        ));
    }

    function it_should_lowercase_an_attribute_code_if_it_isnt($attribute)
    {
        $attribute->getAttributeType()->willReturn('pim_catalog_text');
        $attribute->getDefaultValue()->willReturn(null);
        $attribute->getCode()->willReturn('Attribute_code');

        $this->normalize($attribute, 'MagentoArray', $this->baseContext)->shouldReturn(array_merge(
            array(
                'attribute_code'                => 'attribute_code_mapped',
                'frontend_input'                => 'text',
            ),
            $this->baseNormalizedAttribute
        ));
    }

    function it_throws_an_exception_if_attribute_code_is_note_valid_type_changed($attribute, $attributeMapping, $storeViewMapping)
    {
        $this->baseContext = array_merge(
            $this->baseContext,
            array(
                'magentoAttributes' => array('attribute_code_mapped' => array('type' => 'text')),
            )
        );

        $attribute->getAttributeType()->willReturn('pim_catalog_simpleselect');
        $attribute->getDefaultValue()->willReturn(null);

        $attribute->getCode()->willReturn('2ttribute_code');
        $this->shouldThrow('Pim\Bundle\MagentoConnectorBundle\Normalizer\Exception\InvalidAttributeNameException')->during('normalize', array($attribute, 'MagentoArray', $this->baseContext));
    }

    function it_normalizes_all_attribute_labels($attribute, $attributeMapping, $storeViewMapping, AttributeTranslation $translation)
    {
        $this->baseContext = array_merge(
            $this->baseContext,
            array(
                'magentoAttributes' => array('attribute_code_mapped' => array('type' => 'text')),
                'magentoStoreViews' => array(array('store_id' => 1, 'code' => 'fr_fr'), array('store_id' => 2, 'code' => 'test')),
                'create'            => false
            )
        );

        $attribute->getAttributeType()->willReturn('pim_catalog_text');
        $attribute->getDefaultValue()->willReturn(null);
        $attribute->getCode()->willReturn('attribute_code');
        $attribute->getTranslations()->willReturn(array($translation));

        $translation->getLocale()->willReturn('de_DE');
        $translation->getLabel()->willReturn('Attribut kod !');

        $storeViewMapping->getSource('fr_fr')->willReturn('fr_FR');
        $storeViewMapping->getSource('test')->willReturn('de_DE');

        $this->normalize($attribute, 'MagentoArray', $this->baseContext)->shouldReturn(array(
            'attribute_code_mapped',
            array_merge(
                $this->baseNormalizedAttribute,
                array(
                    'frontend_label'                => array(
                        array('store_id' => 0, 'label' => 'attribute_code_mapped'),
                        array('store_id' => 1, 'label' => 'attribute_code'),
                        array('store_id' => 2, 'label' => 'Attribut kod !'),
                    )
                )
            )
        ));
    }

    function it_normalize_a_new_attribute_with_default_value($attribute, $attributeMapping, $storeViewMapping, $productValueNormalizer, ProductValueInterface $productValue)
    {
        $attribute->getAttributeType()->willReturn('pim_catalog_text');
        $attribute->getDefaultValue()->willReturn($productValue);
        $attribute->getCode()->willReturn('attribute_code');

        $productValueNormalizer->normalize(Argument::cetera())->willReturn(array('test' => 'defaultValue'));

        $this->normalize($attribute, 'MagentoArray', $this->baseContext)->shouldReturn(array_merge(
            array(
                'attribute_code' => 'attribute_code_mapped',
                'frontend_input' => 'text'
            ),
            $this->baseNormalizedAttribute,
            array(
                'default_value' => ''
            )
        ));
    }
}
