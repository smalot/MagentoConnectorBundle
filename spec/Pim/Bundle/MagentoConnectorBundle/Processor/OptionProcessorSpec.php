<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Processor;

use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\MagentoConnectorBundle\Manager\LocaleManager;
use Pim\Bundle\MagentoConnectorBundle\Merger\MappingMerger;
use Pim\Bundle\MagentoConnectorBundle\Mapper\MappingCollection;
use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\MagentoConnectorBundle\Guesser\NormalizerGuesser;
use Pim\Bundle\MagentoConnectorBundle\Webservice\Webservice;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\OptionNormalizer;
use Pim\Bundle\CatalogBundle\Entity\Attribute;
use Pim\Bundle\CatalogBundle\Entity\AttributeOption;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class OptionProcessorSpec extends ObjectBehavior
{
    function let(
        ChannelManager $channelManager,
        LocaleManager $localeManager,
        MappingMerger $storeViewMappingMerger,
        MappingMerger $attributeMappingMerger,
        MappingCollection $attributeMapping,
        WebserviceGuesser $webserviceGuesser,
        NormalizerGuesser $normalizerGuesser,
        Webservice $webservice,
        OptionNormalizer $optionNormalizer
    ) {
        $this->beConstructedWith(
            $webserviceGuesser,
            $normalizerGuesser,
            $localeManager,
            $storeViewMappingMerger,
            $attributeMappingMerger
        );

        $attributeMappingMerger->getMapping()->willReturn($attributeMapping);
        $attributeMapping->getTarget('size')->willReturn('size');
        $webserviceGuesser->getWebservice(Argument::any())->willReturn($webservice);
        $normalizerGuesser->getOptionNormalizer(Argument::cetera())->willReturn($optionNormalizer);
    }

    function it_normalizes_given_grouped_options(
        AttributeOption $optionRed,
        AttributeOption $optionBlue,
        Attribute $attribute,
        $optionNormalizer,
        $webservice
    ) {
        $optionRed->getAttribute()->willReturn($attribute);
        $attribute->getCode()->willReturn('size');

        $optionRed->getCode()->willReturn('red');
        $optionBlue->getCode()->willReturn('blue');

        $webservice->getStoreViewsList()->shouldBeCalled();
        $webservice->getAttributeOptions('size')->willReturn(array('red'));

        $optionNormalizer->normalize($optionRed, Argument::cetera())->willReturn(array('foo'));
        $optionNormalizer->normalize($optionBlue, Argument::cetera())->willReturn(array('bar'));

        $this->process(array(
            $optionRed,
            $optionBlue
        ))->shouldReturn(array(array('foo'), array('bar')));
    }
}
