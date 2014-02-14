<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Normalizer;

use Pim\Bundle\MagentoConnectorBundle\Manager\CategoryMappingManager;
use Pim\Bundle\MagentoConnectorBundle\Mapper\MappingCollection;
use Pim\Bundle\CatalogBundle\Model\ProductValue;
use Pim\Bundle\CatalogBundle\Entity\Attribute;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ProductValueNormalizerSpec extends ObjectBehavior
{
    protected $globalContext;

    function let(ProductValue $value, MappingCollection $attributeMapping)
    {
        $this->globalContext = array(
            'identifier'               => 'identifier',
            'scopeCode'                => 'scope_code',
            'localeCode'               => 'locale_code',
            'onlyLocalized'            => false,
            'magentoAttributes'        => array('attribute_code' => array('code' => 'attribute_ode')),
            'magentoAttributesOptions' => array(),
            'currencyCode'             => 'currency_code',
            'attributeMapping'         => $attributeMapping
        );
    }

    function it_normalizes_a_scopable_value($value, Attribute $attribute)
    {
        $value->getData()->willReturn('hello');
        $value->getAttribute()->willReturn($attribute);

        $attribute->isScopable()->willReturn(true);
        $value->getScope()->willReturn('scope_code');

        $attribute->isLocalizable()->willReturn(false);

        $attribute->getCode()->willReturn('attribute_code');

        $this->normalize($value, 'MagentoArray', $this->globalContext)->shouldReturn(array());
    }
}
