<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Normalizer;

use Pim\Bundle\CatalogBundle\Entity\FamilyTranslation;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\CatalogBundle\Entity\Family;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class FamilyNormalizerSpec extends ObjectBehavior
{
    protected $globalContext;

    function let()
    {
        $this->globalContext = [
            'magentoFamilies' => [],
            'magentoUrl'        => 'soap_url',
            'defaultLocale'     => 'default_locale',
            'magentoStoreViews' => []
        ];
    }

    function it_normalize_a_family(Family $family, $globalContext)
    {
        $family->getCode()->willReturn('family_code');
        $this->normalize($family)->shouldReturn(['attributeSetName' => 'family_code']);
    }
}
