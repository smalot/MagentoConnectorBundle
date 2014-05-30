<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Guesser;

use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\CatalogBundle\Manager\MediaManager;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\ProductValueNormalizer;
use Pim\Bundle\MagentoConnectorBundle\Manager\CategoryMappingManager;
use Pim\Bundle\MagentoConnectorBundle\Manager\AssociationTypeManager;
use Pim\Bundle\MagentoConnectorBundle\Manager\ProductValueManager;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientFactory;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientParametersRegistry;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClient;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientParameters;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\ProductNormalizer;
use Pim\Bundle\MagentoConnectorBundle\Manager\PriceMappingManager;
use PhpSpec\ObjectBehavior;

class NormalizerGuesserSpec extends ObjectBehavior
{
    function let(
        MagentoSoapClientFactory $magentoSoapClientFactory,
        ChannelManager $channelManager,
        MediaManager $mediaManager,
        ProductValueNormalizer $productValueNormalizer,
        CategoryMappingManager $categoryMappingManager,
        AssociationTypeManager $associationTypeManager,
        ProductValueManager $productValueManager,
        MagentoSoapClientParametersRegistry $clientParametersRegistry,
        MagentoSoapClientParameters $clientParameters
    ) {
        $this->beConstructedWith($magentoSoapClientFactory, $channelManager, $mediaManager, $productValueNormalizer, $categoryMappingManager, $associationTypeManager, $productValueManager);

        $clientParametersRegistry->getInstance('soap_username', 'soap_api_key', 'http://magento.url', '/api/soap/?wsdl', 'default', null, null)->willReturn($clientParameters);

        $clientParameters->getSoapUrl()->willReturn('http://magento.url/api/soap/?wsdl');
        $clientParameters->getSoapUsername()->willReturn('soap_username');
        $clientParameters->getSoapApiKey()->willReturn('soap_api_key');
    }

    function it_shoulds_guess_the_product_normalizer_for_parameters($clientParameters, $magentoSoapClientFactory, MagentoSoapClient $magentoSoapClient)
    {
        $magentoSoapClientFactory->getMagentoSoapClient($clientParameters)->willReturn($magentoSoapClient);

        $magentoSoapClient->call('core_magento.info')->willReturn(array('magento_version' => '1.8'));

        $this->getProductNormalizer($clientParameters, true, 4, 'EUR')->shouldBeAnInstanceOf('Pim\Bundle\MagentoConnectorBundle\Normalizer\ProductNormalizer');
    }

    function it_should_return_an_old_version_if_soap_give_an_old_version($clientParameters, $magentoSoapClientFactory, MagentoSoapClient $magentoSoapClient)
    {
        $magentoSoapClientFactory->getMagentoSoapClient($clientParameters)->willReturn($magentoSoapClient);

        $magentoSoapClient->call('core_magento.info')->willReturn(array('magento_version' => '1.6'));

        $this->getProductNormalizer($clientParameters, true, 4, 'EUR')->shouldBeAnInstanceOf('Pim\Bundle\MagentoConnectorBundle\Normalizer\ProductNormalizer16');
    }

    function it_raises_an_exception_if_the_version_number_is_not_well_formed($clientParameters, $magentoSoapClientFactory, MagentoSoapClient $magentoSoapClient, ProductNormalizer $productNormalizer, PriceMappingManager $priceMappingManager)
    {
        $magentoSoapClientFactory->getMagentoSoapClient($clientParameters)->willReturn($magentoSoapClient);

        $magentoSoapClient->call('core_magento.info')->willReturn(array('magento_version' => 'v1.0.4'));

        $this->shouldThrow('Pim\Bundle\MagentoConnectorBundle\Guesser\NotSupportedVersionException')->during('getProductNormalizer', array($clientParameters, true, 4, 'EUR'));
        $this->shouldThrow('Pim\Bundle\MagentoConnectorBundle\Guesser\NotSupportedVersionException')->during('getConfigurableNormalizer', array($clientParameters, $productNormalizer, $priceMappingManager));
        $this->shouldThrow('Pim\Bundle\MagentoConnectorBundle\Guesser\NotSupportedVersionException')->during('getCategoryNormalizer', array($clientParameters));
        $this->shouldThrow('Pim\Bundle\MagentoConnectorBundle\Guesser\NotSupportedVersionException')->during('getOptionNormalizer', array($clientParameters));
        $this->shouldThrow('Pim\Bundle\MagentoConnectorBundle\Guesser\NotSupportedVersionException')->during('getAttributeNormalizer', array($clientParameters));
    }

    function it_shoulds_guess_the_configurable_normalizer_for_parameters($clientParameters, $magentoSoapClientFactory, MagentoSoapClient $magentoSoapClient, ProductNormalizer $productNormalizer, PriceMappingManager $priceMappingManager)
    {
        $magentoSoapClientFactory->getMagentoSoapClient($clientParameters)->willReturn($magentoSoapClient);

        $magentoSoapClient->call('core_magento.info')->willReturn(array('magento_version' => '1.8'));

        $this->getConfigurableNormalizer($clientParameters, $productNormalizer, $priceMappingManager)->shouldBeAnInstanceOf('Pim\Bundle\MagentoConnectorBundle\Normalizer\ConfigurableNormalizer');
    }

    function it_raises_an_exception_if_the_version_is_not_supported($clientParameters, $magentoSoapClientFactory, MagentoSoapClient $magentoSoapClient, ProductNormalizer $productNormalizer, PriceMappingManager $priceMappingManager)
    {
        $magentoSoapClientFactory->getMagentoSoapClient($clientParameters)->willReturn($magentoSoapClient);

        $magentoSoapClient->call('core_magento.info')->willReturn(array('magento_version' => '1.4'));

        $this->shouldThrow('Pim\Bundle\MagentoConnectorBundle\Guesser\NotSupportedVersionException')->during('getConfigurableNormalizer', array($clientParameters, $productNormalizer, $priceMappingManager));
    }

    function it_shoulds_guess_the_category_normalizer_for_parameters($clientParameters, $magentoSoapClientFactory, MagentoSoapClient $magentoSoapClient, ProductNormalizer $productNormalizer, PriceMappingManager $priceMappingManager)
    {
        $magentoSoapClientFactory->getMagentoSoapClient($clientParameters)->willReturn($magentoSoapClient);

        $magentoSoapClient->call('core_magento.info')->willReturn(array('magento_version' => '1.8'));

        $this->getCategoryNormalizer($clientParameters)->shouldBeAnInstanceOf('Pim\Bundle\MagentoConnectorBundle\Normalizer\CategoryNormalizer');
    }

    function it_shoulds_guess_the_option_normalizer_for_parameters($clientParameters, $magentoSoapClientFactory, MagentoSoapClient $magentoSoapClient)
    {
        $magentoSoapClientFactory->getMagentoSoapClient($clientParameters)->willReturn($magentoSoapClient);

        $magentoSoapClient->call('core_magento.info')->willReturn(array('magento_version' => '1.8'));

        $this->getOptionNormalizer($clientParameters)->shouldBeAnInstanceOf('Pim\Bundle\MagentoConnectorBundle\Normalizer\OptionNormalizer');
    }

    function it_shoulds_guess_the_attribute_normalizer_for_parameters($clientParameters, $magentoSoapClientFactory, MagentoSoapClient $magentoSoapClient)
    {
        $magentoSoapClientFactory->getMagentoSoapClient($clientParameters)->willReturn($magentoSoapClient);

        $magentoSoapClient->call('core_magento.info')->willReturn(array('magento_version' => '1.8'));

        $this->getAttributeNormalizer($clientParameters)->shouldBeAnInstanceOf('Pim\Bundle\MagentoConnectorBundle\Normalizer\AttributeNormalizer');
    }

    function it_raises_an_exception_if_the_version_not_initialized($clientParameters, $magentoSoapClientFactory)
    {
        $magentoSoapClientFactory->getMagentoSoapClient($clientParameters)->willReturn(null);

        $this->shouldThrow('Pim\Bundle\MagentoConnectorBundle\Guesser\NotSupportedVersionException')->during('getProductNormalizer', array($clientParameters, true, 4, 'EUR'));
    }
}
