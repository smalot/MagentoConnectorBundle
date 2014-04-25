<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Processor;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Pim\Bundle\CatalogBundle\Entity\Family;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\MagentoConnectorBundle\Manager\LocaleManager;
use Pim\Bundle\MagentoConnectorBundle\Merger\MappingMerger;
use Pim\Bundle\MagentoConnectorBundle\Mapper\MappingCollection;
use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\MagentoConnectorBundle\Guesser\NormalizerGuesser;
use Pim\Bundle\MagentoConnectorBundle\Manager\FamilyMappingManager;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\AbstractNormalizer;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\FamilyNormalizer;
use Pim\Bundle\MagentoConnectorBundle\Webservice\Webservice;
use Akeneo\Bundle\BatchBundle\Entity\StepExecution;

class FamilyProcessorSpec extends ObjectBehavior
{
    function let(
        ChannelManager $channelManager,
        LocaleManager $localeManager,
        MappingMerger $storeViewMappingMerger,
        MappingMerger $familyMappingMerger,
        WebserviceGuesser $webserviceGuesser,
        NormalizerGuesser $normalizerGuesser,
        FamilyMappingManager $familyMappingManager,
        Webservice $webservice,
        FamilyNormalizer $familyNormalizer,
        StepExecution $stepExecution
    ) {
        $this->beConstructedWith(
            $webserviceGuesser,
            $normalizerGuesser,
            $localeManager,
            $storeViewMappingMerger,
            $familyMappingMerger,
            $familyMappingManager
        );
        $this->setStepExecution($stepExecution);

        $webserviceGuesser->getWebservice(Argument::any())->willReturn($webservice);

        $normalizerGuesser->getFamilyNormalizer(Argument::any(), Argument::any())->willReturn($familyNormalizer);
    }

    function it_normalizes_families(
        Family $family,
        Webservice $webservice,
        $familyNormalizer
    ) {

        $familyNormalizer->normalize(
            $family,
            AbstractNormalizer::MAGENTO_FORMAT,
            Argument::any()
        )->willReturn(array(
            'attributeSetName' => 'family_code'
        ));
        $webservice->getAttributeSetList()->willReturn(array());
        $webservice->getStoreViewsList()->willReturn(array());
        $this->process($family)->shouldReturn(array(
            'family'     => $family,
            'attributes' => null,
            'create'     => array('attributeSetName' => 'family_code')
        ));
    }
}
