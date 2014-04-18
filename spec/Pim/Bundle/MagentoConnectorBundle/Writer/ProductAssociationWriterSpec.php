<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Writer;

use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesserFactory;
use Pim\Bundle\MagentoConnectorBundle\Webservice\ProductWebservice;
use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ProductAssociationWriterSpec extends ObjectBehavior
{
    function let(WebserviceGuesserFactory $webserviceGuesserFactory, ProductWebservice $productWebservice, StepExecution $stepExecution)
    {
        $webserviceGuesserFactory->getWebservice('product', Argument::cetera())->willReturn($productWebservice);

        $this->beConstructedWith($webserviceGuesserFactory);
        $this->setStepExecution($stepExecution);
    }

    function it_sends_remove_and_create_calls_to_the_webservice($productWebservice)
    {
        $productWebservice->removeProductAssociation(array('foo'))->shouldBeCalled();
        $productWebservice->createProductAssociation(array('bar'))->shouldBeCalled();

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
