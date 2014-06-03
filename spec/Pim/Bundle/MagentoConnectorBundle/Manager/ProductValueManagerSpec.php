<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadataFactory;
use Doctrine\ORM\Mapping\ClassMetadata;
use Pim\Bundle\CatalogBundle\Entity\Attribute;
use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeRepository;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ProductValueManagerSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('Pim\Bundle\CatalogBundle\Model\ProductValue');
    }

    function it_creates_default_product_for_default_option(Attribute $attribute)
    {
        $attribute->getDefaultValue()->shouldBeCalled()->willReturn(null);
        $this->createProductValueForDefaultOption($attribute)->shouldReturnAnInstanceOf('Pim\Bundle\CatalogBundle\Model\ProductValue');
    }
}
