<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Writer;

use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\MagentoConnectorBundle\Webservice\Webservice;
use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Pim\Bundle\MagentoConnectorBundle\Webservice\SoapCallException;
use Akeneo\Bundle\BatchBundle\Item\InvalidItemException;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class OptionWriterSpec extends ObjectBehavior
{
    function let(
        WebserviceGuesser $webserviceGuesser,
        Webservice $webservice,
        StepExecution $stepExecution
    ) {
        $webserviceGuesser->getWebservice(Argument::any())->willReturn($webservice);

        $this->beConstructedWith($webserviceGuesser);
        $this->setStepExecution($stepExecution);
    }

    function it_calls_soap_client_to_create_options($webservice)
    {
        $webservice->createOption(array('foo'))->shouldBeCalled();
        $webservice->createOption(array('bar'))->shouldBeCalled();

        $this->write(array(array(array('foo'), array('bar'))));
    }

    function it_fails_if_something_went_wrong_during_create_option_call($webservice, $stepExecution)
    {
        $webservice->createOption(array('foo'))->willThrow('\Pim\Bundle\MagentoConnectorBundle\Webservice\SoapCallException');
        $stepExecution->incrementSummaryInfo(Argument::any())->shouldNotBeCalled();

        $this->shouldThrow('\Akeneo\Bundle\BatchBundle\Item\InvalidItemException')->duringWrite(array(array(array('foo'), array('bar'))));
    }
}
