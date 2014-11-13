<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Normalizer;

use PhpSpec\ObjectBehavior;

class DateTimeNormalizerSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType('\Pim\Bundle\MagentoConnectorBundle\Normalizer\DateTimeNormalizer');
    }

    public function it_returns_true_if_the_normalizer_can_support_given_data(\DateTime $datetime)
    {
        $this->supportsNormalization($datetime, 'api_import')->shouldReturn(true);
    }

    public function it_returns_false_if_the_normalizer_can_not_support_given_data($object)
    {
        $this->supportsNormalization($object, 'api_import')->shouldReturn(false);
    }

    public function it_returns_false_if_the_normalizer_can_not_support_given_data_because_of_the_format(
        \DateTime $datetime
    ) {
        $this->supportsNormalization($datetime, 'foo_bar')->shouldReturn(false);
    }

    public function it_normalizes_datetime_to_the_api_import_format(\DateTime $datetime)
    {
        $datetime->format('Y-m-d H:i:s')->willReturn('2014-11-12 16:34:00');
        $this->normalize($datetime, 'api_import', [])->shouldReturn('2014-11-12 16:34:00');
    }
}
