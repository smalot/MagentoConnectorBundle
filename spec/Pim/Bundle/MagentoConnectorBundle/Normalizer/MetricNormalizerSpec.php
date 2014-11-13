<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Normalizer;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\Metric;

class MetricNormalizerSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType('\Pim\Bundle\MagentoConnectorBundle\Normalizer\MetricNormalizer');
    }

    public function it_returns_true_if_the_normalizer_can_support_given_data(Metric $metric)
    {
        $this->supportsNormalization($metric, 'api_import')->shouldReturn(true);
    }

    public function it_returns_false_if_the_normalizer_can_not_support_given_data($object)
    {
        $this->supportsNormalization($object, 'api_import')->shouldReturn(false);
    }

    public function it_returns_false_if_the_normalizer_can_not_support_given_data_because_of_the_format(
        Metric $metric
    ) {
        $this->supportsNormalization($metric, 'foo_bar')->shouldReturn(false);
    }

    public function it_normalizes_a_metric(Metric $metric)
    {
        $metric->getData()->willReturn((double) 1234);
        $this->normalize($metric, 'api_import', [])->shouldReturn((double) 1234);
    }
}
