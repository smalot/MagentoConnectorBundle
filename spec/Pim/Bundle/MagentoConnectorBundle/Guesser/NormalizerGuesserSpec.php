<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Guesser;

use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\CatalogBundle\Manager\MediaManager;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\ProductValueNormalizer;
use Pim\Bundle\MagentoConnectorBundle\Manager\CategoryMappingManager;
use Pim\Bundle\MagentoConnectorBundle\Manager\AssociationTypeManager;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class NormalizerGuesserSpec extends ObjectBehavior
{
    function let(
        ChannelManager $channelManager,
        MediaManager $mediaManager,
        ProductValueNormalizer $productValueNormalizer,
        CategoryMappingManager $categoryMappingManager,
        AssociationTypeManager $associationTypeManager,
        MagentoSoapClientParameters $clientParameters
    ) {
        $this->beConstructedWith($channelManager, $mediaManager, $productValueNormalizer, $categoryMappingManager, $associationTypeManager);

        $clientParameters->getSoapUrl()->willReturn('soap_url');
        $clientParameters->getSoapUsername()->willReturn('soap_username');
        $clientParameters->getSoapApiKey()->willReturn('soap_api_key');
    }

    it_shoulds_guess_the_product_normalizer_for_parameters($clientParameters)
    {

    }
}
