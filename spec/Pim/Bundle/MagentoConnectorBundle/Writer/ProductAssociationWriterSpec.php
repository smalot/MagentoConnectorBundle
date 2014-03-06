<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Writer;

use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\MagentoConnectorBundle\Webservice\Webservice;
use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ProductAssociationWriterSpec extends ObjectBehavior
{
    function let(WebserviceGuesser $webserviceGuesser, Webservice $webservice, StepExecution $stepExecution)
    {
        $webserviceGuesser->getWebservice(Argument::cetera())->willReturn($webservice);

        $this->beConstructedWith($webserviceGuesser);
        $this->setStepExecution($stepExecution);
    }

    function it_sends_remove_and_create_calls_to_the_webservice($webservice)
    {
        $webservice->removeProductAssociation(array('foo'))->shouldBeCalled();
        $webservice->createProductAssociation(array('bar'))->shouldBeCalled();

        $this->write(
            array(
                array(
                    'remove' => array(array('foo')),
                    'create' => array(array('bar'))
                )
            )
        );
    }
}
