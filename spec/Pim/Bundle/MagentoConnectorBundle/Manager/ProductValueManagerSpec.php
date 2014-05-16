<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Manager;

use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Entity\AttributeOption;

use PhpSpec\ObjectBehavior;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductValueManagerSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('\Pim\Bundle\CatalogBundle\Model\ProductValue');
    }

    function it_creates_product_value_for_default_option(AbstractAttribute $attribute, AttributeOption $option)
    {
        $attribute->getDefaultValue()->willReturn($option);
        $value = $this->createProductValueForDefaultOption($attribute);

        $value->shouldBeAnInstanceOf('\Pim\Bundle\CatalogBundle\Model\ProductValue');
        $value->getAttribute()->shouldReturn($attribute);
        $value->getOption()->shouldReturn($option);
    }
}
