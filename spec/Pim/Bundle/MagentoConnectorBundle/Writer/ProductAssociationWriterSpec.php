<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Writer;

use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\MagentoConnectorBundle\Webservice\Webservice;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientParameters;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientParametersRegistry;
use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use PhpSpec\ObjectBehavior;

class ProductAssociationWriterSpec extends ObjectBehavior
{
    function let(
        WebserviceGuesser $webserviceGuesser,
        Webservice $webservice,
        StepExecution $stepExecution,
        MagentoSoapClientParametersRegistry $clientParametersRegistry,
        MagentoSoapClientParameters $clientParameters
    ) {
        $clientParametersRegistry->getInstance(null, null, null, '/api/soap/?wsdl', 'default', null, null)->willReturn($clientParameters);
        $webserviceGuesser->getWebservice($clientParameters)->willReturn($webservice);

        $this->beConstructedWith($webserviceGuesser,$clientParametersRegistry);
        $this->setStepExecution($stepExecution);
    }

    function it_sends_remove_and_create_calls_to_the_webservice($webservice)
    {
        $webservice->removeProductAssociation(array('foo'))->shouldBeCalled();
        $webservice->createProductAssociation(array('bar'))->shouldBeCalled();

        $productAssociationCallsBatchs =
            array(
                array(
                    'remove' => array(array('foo')),
                    'create' => array(array('bar'))
                )
            );

        $this->write($productAssociationCallsBatchs);
    }

    function it_fails_if_an_error_occured_with_remove_call($webservice)
    {
        $webservice->removeProductAssociation(array('foo'))->willThrow('\Pim\Bundle\MagentoConnectorBundle\Webservice\SoapCallException');
        $webservice->createProductAssociation(array('bar'))->shouldNotBeCalled();

        $productAssociationCallsBatchs =
            array(
                array(
                    'remove' => array(array('foo')),
                    'create' => array(array('bar'))
                )
            );

        $this->shouldThrow('\Akeneo\Bundle\BatchBundle\Item\InvalidItemException')->duringWrite($productAssociationCallsBatchs);
    }

    function it_fails_if_an_error_occured_with_create_call($webservice)
    {
        $webservice->removeProductAssociation(array('foo'))->shouldBeCalled();
        $webservice->createProductAssociation(array('bar'))->willThrow('\Pim\Bundle\MagentoConnectorBundle\Webservice\SoapCallException');

        $productAssociationCallsBatchs =
            array(
                array(
                    'remove' => array(array('foo')),
                    'create' => array(array('bar'))
                )
            );

        $this->shouldThrow('\Akeneo\Bundle\BatchBundle\Item\InvalidItemException')->duringWrite($productAssociationCallsBatchs);
    }
}
