<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Processor;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\ConnectorMappingBundle\Mapper\MappingCollection;
use Pim\Bundle\MagentoConnectorBundle\Guesser\NormalizerGuesser;
use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\MagentoConnectorBundle\Manager\AssociationTypeManager;
use Pim\Bundle\MagentoConnectorBundle\Manager\CurrencyManager;
use Pim\Bundle\MagentoConnectorBundle\Manager\LocaleManager;
use Pim\Bundle\MagentoConnectorBundle\Merger\MagentoMappingMerger;
use Pim\Bundle\TransformBundle\Converter\MetricConverter;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductProcessorSpec extends ObjectBehavior
{
    function let(
        WebserviceGuesser $webserviceGuesser,
        NormalizerGuesser $normalizerGuesser,
        LocaleManager $localeManager,
        MagentoMappingMerger $storeViewMappingMerger,
        CurrencyManager $currencyManager,
        ChannelManager $channelManager,
        MagentoMappingMerger $categoryMappingMerger,
        MagentoMappingMerger $attributeMappingMerger,
        MetricConverter $metricConverter,
        AssociationTypeManager $associationTypeManager
    ) {
        $this->beConstructedWith(
            $webserviceGuesser,
            $normalizerGuesser,
            $localeManager,
            $storeViewMappingMerger,
            $currencyManager,
            $channelManager,
            $categoryMappingMerger,
            $attributeMappingMerger,
            $metricConverter,
            $associationTypeManager
        );
    }

    function it_is_configurable(
        $categoryMappingMerger,
        $attributeMappingMerger,
        MappingCollection $mappingCollection
    ) {
        $this->setChannel('channel');
        $this->setCurrency('EUR');
        $this->setEnabled('true');
        $this->setVisibility('4');
        $this->setCategoryMapping('{"categoryMapping" : "category"}');
        $this->setAttributeMapping('{"attributeMapping" : "attribute"}');
        $this->setPimGrouped('group');


        $categoryMappingMerger->setMapping(array('categoryMapping' => 'category'))->shouldBeCalled();
        $categoryMappingMerger->getMapping()->shouldBeCalled()->willReturn($mappingCollection);
        $this->getCategoryMapping();

        $attributeMappingMerger->setMapping(array('attributeMapping' => 'attribute'))->shouldBeCalled();
        $attributeMappingMerger->getMapping()->shouldBeCalled()->willReturn($mappingCollection);
        $this->getAttributeMapping();

        $this->getChannel()->shouldReturn('channel');
        $this->getCurrency()->shouldReturn('EUR');
        $this->getEnabled()->shouldReturn('true');
        $this->getVisibility()->shouldReturn('4');
        $this->getPimGrouped()->shouldReturn('group');
    }

    function it_does_something()
    {
        
    }
}
