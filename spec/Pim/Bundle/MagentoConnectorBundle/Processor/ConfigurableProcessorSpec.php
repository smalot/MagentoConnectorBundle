<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Processor;

use Prophecy\Argument;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Akeneo\Bundle\BatchBundle\Item\InvalidItemException;
use Pim\Bundle\MagentoConnectorBundle\Webservice\Webservice;
use Pim\Bundle\MagentoConnectorBundle\Manager\PriceMappingManager;
use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\MagentoConnectorBundle\Guesser\NormalizerGuesser;
use Pim\Bundle\MagentoConnectorBundle\Manager\GroupManager;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\Exception\NormalizeException;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\AbstractNormalizer;
use Pim\Bundle\MagentoConnectorBundle\Manager\LocaleManager;
use Pim\Bundle\MagentoConnectorBundle\Merger\MagentoMappingMerger;
use Pim\Bundle\MagentoConnectorBundle\Manager\CurrencyManager;
use Pim\Bundle\MagentoConnectorBundle\Webservice\SoapCallException;
use Pim\Bundle\ConnectorMappingBundle\Mapper\MappingCollection;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\ProductNormalizer;
use Pim\Bundle\MagentoConnectorBundle\Entity\Repository\GroupRepository;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientParameters;
use Pim\Bundle\CatalogBundle\Model\Product;
use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\ConfigurableNormalizer;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConfigurableProcessorSpec extends ObjectBehavior
{
    function let(
        WebserviceGuesser           $webserviceGuesser,
        NormalizerGuesser           $normalizerGuesser,
        LocaleManager               $localeManager,
        MagentoMappingMerger        $storeViewMappingMerger,
        CurrencyManager             $currencyManager,
        ChannelManager              $channelManager,
        MagentoMappingMerger        $categoryMappingMerger,
        MagentoMappingMerger        $attributeMappingMerger,
        GroupManager                $groupManager,
        Webservice                  $webservice,
        MappingCollection           $mappingCollection,
        ProductNormalizer           $productNormalizer,
        GroupRepository             $groupRepository,
        ConfigurableNormalizer      $configurableNormalizer
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
            $groupManager
        );
        $webserviceGuesser->getWebservice(Argument::type('\Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientParameters'))->willReturn($webservice);
        $storeViewMappingMerger->getMapping()->willReturn($mappingCollection);

        $normalizerGuesser->getProductNormalizer(
            Argument::type('\Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientParameters'),
            null,
            4,
            null
        )->willReturn($productNormalizer);

        $webservice->getStoreViewsList()->willReturn(
            array(
                array (
                    'store_id' => '1',
                    'code' => 'default',
                    'website_id' => '1',
                    'group_id' => '1',
                    'name' => 'Default Store View',
                    'sort_order' => '0',
                    'is_active' => '1'
                )
            )
        );

        $webservice->getAllAttributes()->willReturn(
            array (
                'name' =>
                    array (
                        'attribute_id' => '71',
                        'code' => 'name',
                        'type' => 'text',
                        'required' => '1',
                        'scope' => 'store'
                    )
            )
        );

        $webservice->getAllAttributesOptions()->willReturn(Argument::type('array'));

        $categoryMappingMerger->getMapping()->willReturn(Argument::type('\Pim\Bundle\ConnectorMappingBundle\Mapper\MappingCollection'));
        $attributeMappingMerger->getMapping()->willReturn(Argument::type('\Pim\Bundle\ConnectorMappingBundle\Mapper\MappingCollection'));

        $normalizerGuesser->getConfigurableNormalizer(
            Argument::type('\Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientParameters'),
            $productNormalizer,
            Argument::type('\Pim\Bundle\MagentoConnectorBundle\Manager\PriceMappingManager')
        )->willReturn($configurableNormalizer);
        $groupManager->getRepository()->willReturn($groupRepository);
    }

    function it_throws_an_exception_if_the_variant_group_is_not_associated_to_any_product($groupRepository, $webservice, Product $product)
    {
        $groupRepository->getVariantGroupIds()->willReturn(array());
        $product->getGroups()->willReturn(Argument::type('\Doctrine\Common\Collections\ArrayCollection'));
        $webservice->getConfigurablesStatus(array(array()))->willReturn(array());

        $this->shouldThrow('\Akeneo\Bundle\BatchBundle\Item\InvalidItemException')->duringProcess(array($product));
    }
}
