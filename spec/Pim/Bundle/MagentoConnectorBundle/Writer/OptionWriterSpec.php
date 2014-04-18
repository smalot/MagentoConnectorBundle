<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Writer;

use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesserFactory;
use Pim\Bundle\MagentoConnectorBundle\Webservice\OptionWebservice;
use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class OptionWriterSpec extends ObjectBehavior
{
    function let(
        WebserviceGuesserFactory $webserviceGuesserFactory,
        OptionWebservice $webservice,
        StepExecution $stepExecution
    ) {
        $webserviceGuesserFactory->getWebservice('option', Argument::any())->willReturn($webservice);

        $this->beConstructedWith($webserviceGuesserFactory);
        $this->setStepExecution($stepExecution);
    }

    function it_calls_soap_client_to_create_options($webservice)
    {
        $webservice->createOption(array('foo'))->shouldBeCalled();
        $webservice->createOption(array('bar'))->shouldBeCalled();

        $this->write(array(array(array('foo'), array('bar'))));
    }
}
