<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Processor;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AttributeProcessorSpec extends ObjectBehavior
{
    public function let(NormalizerInterface $normalizer)
    {
        $this->beConstructedWith($normalizer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('\Pim\Bundle\MagentoConnectorBundle\Processor\AttributeProcessor');
    }

    function it_returns_configuration_fields()
    {
        $this->getConfigurationFields()->shouldReturn([]);
    }

    function it_processes_an_attribute(AbstractAttribute $attribute, $normalizer)
    {
        $context = [
            'defaultLocale'    => 'en_US',
            'defaultStoreView' => 'Default',
            'visibility'       => true,
            'storeViewMapping' => [
                'fr_FR' => 'fr_fr'
            ],
        ];

        $normalizer->normalize($attribute, 'api_import', $context)->willReturn(['attribute']);

        $this->process($attribute)->shouldReturn(['attribute']);
    }
}
