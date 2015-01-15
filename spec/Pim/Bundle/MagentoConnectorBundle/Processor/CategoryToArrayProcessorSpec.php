<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Processor;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\Category;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class CategoryToArrayProcessorSpec extends ObjectBehavior
{
    public function let(NormalizerInterface $normalizer)
    {
        $this->beConstructedWith($normalizer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('\Pim\Bundle\MagentoConnectorBundle\Processor\CategoryToArrayProcessor');
    }

    function it_returns_configuration_fields()
    {
        $this->getConfigurationFields()->shouldReturn([]);
    }

    function it_processes_a_category(Category $category, $normalizer)
    {
        $context = [
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

        $normalizer->normalize($category, 'api_import_category', $context)->willReturn(['normalized category']);

        $this->process($category)->shouldReturn(['normalized category']);
    }
}
