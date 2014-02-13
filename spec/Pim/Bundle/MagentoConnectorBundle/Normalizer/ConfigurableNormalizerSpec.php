<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Normalizer;

use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\MagentoConnectorBundle\Manager\PriceMappingManager;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\ProductNormalizer;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ConfigurableNormalizerSpec extends ObjectBehavior
{
    function let(
        ChannelManager $channelManager,
        ProductNormalizer $productNormalizer,
        PriceMappingManager $priceMappingManager
    ) {
        $this->beConstructedWith($channelManager, $productNormalizer, $priceMappingManager);
    }

    function it_normalize_a_configurable_product()
    {

    }
}
