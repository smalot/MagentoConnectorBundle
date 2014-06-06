<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Cleaner;

use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\MagentoConnectorBundle\Manager\FamilyMappingManager;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientParametersRegistry;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientParameters;
use Pim\Bundle\MagentoConnectorBundle\Webservice\Webservice;
use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class FamilyCleanerSpec extends ObjectBehavior
{
    function let(
        WebserviceGuesser                   $webserviceGuesser,
        FamilyMappingManager                $familyMappingManager,
        Webservice                          $webservice,
        StepExecution                       $stepExecution,
        MagentoSoapClientParametersRegistry $clientParametersRegistry,
        MagentoSoapClientParameters         $clientParameters
    ) {
        $this->beConstructedWith($webserviceGuesser, $familyMappingManager, $clientParametersRegistry);
        $this->setStepExecution($stepExecution);

        $clientParametersRegistry->getInstance(null, null, null, '/api/soap/?wsdl', 'default', null, null)->willReturn($clientParameters);
        $webserviceGuesser->getWebservice($clientParameters)->willReturn($webservice);
    }

    function it_asks_soap_client_to_delete_families_that_are_not_in_pim_anymore($webservice, $familyMappingManager)
    {
        $this->setNotInPimAnymoreAction('delete');

        $webservice->getAttributeSetList()->willReturn(
            [
                'family set name 1' => 1,
                'family set name 5' => 5,
                'family set name 151' => 151
            ]
        );

        $familyMappingManager->magentoFamilyExists(1, Argument::cetera())->shouldBeCalled()->willReturn(true);
        $familyMappingManager->magentoFamilyExists(5, Argument::cetera())->shouldBeCalled()->willReturn(true);
        $familyMappingManager->magentoFamilyExists(151, Argument::cetera())->shouldBeCalled()->willReturn(false);

        $webservice->removeAttributeSet('151')->shouldBeCalled();

        $this->execute();
    }
}
