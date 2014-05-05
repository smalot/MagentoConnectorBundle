<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Writer;

use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\MagentoConnectorBundle\Manager\AttributeMappingManager;
use Pim\Bundle\MagentoConnectorBundle\Manager\FamilyMappingManager;
use Pim\Bundle\MagentoConnectorBundle\Webservice\Webservice;
use Pim\Bundle\CatalogBundle\Entity\Family;
use Akeneo\Bundle\BatchBundle\Entity\StepExecution;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class AttributeSetWriterSpec extends ObjectBehavior
{
    function let(
        WebserviceGuesser $webserviceGuesser,
        FamilyMappingManager $familyMappingManager,
        AttributeMappingManager $attributeMappingManager,
        Webservice $webservice,
        StepExecution $stepExecution
    ) {
        $webserviceGuesser->getWebservice(Argument::any())->willReturn($webservice);

        $this->beConstructedWith($webserviceGuesser, $familyMappingManager, $attributeMappingManager);
        $this->setStepExecution($stepExecution);
    }

    function it_sends_families_to_create_on_magento_webservice(
        Family $family,
        $webservice,
        FamilyMappingManager $familyMappingManager
    ) {
        $batches = array(
            array(
                'families_to_create' => array(
                    'attributeSetName' => 'family_code'
                ),
                'family_object' => $family,
            )
        );

        $webservice->createAttributeSet('family_code')->willReturn(12);
        $familyMappingManager->registerFamilyMapping($family, 12, 'barfoo')->shouldBeCalled();

        $this->setMagentoUrl('bar');
        $this->setWsdlUrl('foo');

        $this->write($batches);
    }
}
