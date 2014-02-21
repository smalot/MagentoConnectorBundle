<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Processor;

use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\MagentoConnectorBundle\Manager\LocaleManager;
use Pim\Bundle\MagentoConnectorBundle\Merger\MappingMerger;
use Pim\Bundle\MagentoConnectorBundle\Mapper\MappingCollection;
use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\MagentoConnectorBundle\Guesser\NormalizerGuesser;
use Pim\Bundle\MagentoConnectorBundle\Webservice\Webservice;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\AttributeNormalizer;
use Oro\Bundle\BatchBundle\Entity\StepExecution;
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
        WebserviceGuesser $webserviceGuesser,
        NormalizerGuesser $normalizerGuesser,
        Webservice $webservice,
        AttributeNormalizer $attributeNormalizer,
        StepExecution $stepExecution
    ) {
        $this->beConstructedWith(
            $webserviceGuesser,
            $normalizerGuesser,
            $localeManager,
            $storeViewMappingMerger,
            $attributeMappingMerger
        );
        $this->setStepExecution($stepExecution);

        $webserviceGuesser->getWebservice(Argument::any())->willReturn($webservice);

        $normalizerGuesser->getAttributeNormalizer(Argument::any(), Argument::any())->willReturn($attributeNormalizer);

        $this->globalContext = array(

        );
    }

    function it_process_an_attribute(AbstractAttribute $attribute)
    {
        $this->process($attribute)->shouldReturn(array());
    }
}
