<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Processor;

use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\CatalogBundle\Entity\Attribute;
use Pim\Bundle\MagentoConnectorBundle\Manager\LocaleManager;
use Pim\Bundle\MagentoConnectorBundle\Merger\MappingMerger;
use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesserFactory;
use Pim\Bundle\MagentoConnectorBundle\Guesser\NormalizerGuesser;
use Pim\Bundle\MagentoConnectorBundle\Webservice\AttributeWebservice;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\AttributeNormalizer;
use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class AttributeProcessorSpec extends ObjectBehavior
{
    protected $globalContext = array();

    function let(
        ChannelManager $channelManager,
        LocaleManager $localeManager,
        MappingMerger $storeViewMappingMerger,
        MappingMerger $attributeMappingMerger,
        WebserviceGuesserFactory $webserviceGuesserFactory,
        NormalizerGuesser $normalizerGuesser,
        AttributeWebservice $attributeWebservice,
        AttributeNormalizer $attributeNormalizer,
        StepExecution $stepExecution
    ) {
        $this->beConstructedWith(
            $webserviceGuesserFactory,
            $normalizerGuesser,
            $localeManager,
            $storeViewMappingMerger,
            $attributeMappingMerger
        );
        $this->setStepExecution($stepExecution);

        $webserviceGuesserFactory->getWebservice('attribute'. Argument::any())->willReturn($attributeWebservice);

        $normalizerGuesser->getAttributeNormalizer(Argument::any(), Argument::any())->willReturn($attributeNormalizer);

        $this->globalContext = array(

        );
    }
}
