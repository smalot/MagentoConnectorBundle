<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Validator\Constraints;

use Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Symfony\Component\Validator\ExecutionContextInterface;
use Symfony\Component\Validator\Constraint;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class HasValidDefaultLocaleSpec extends ObjectBehavior
{
    function let() {}

    function it_return_correct_targets_const() {
        $this->getTargets()->shouldReturn('class');
    }

    function it_return_correct_message() {
        $this->validatedBy()->shouldReturn('has_valid_default_locale');
    }
}
