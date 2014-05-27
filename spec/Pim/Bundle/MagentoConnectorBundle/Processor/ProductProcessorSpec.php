<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Processor;

use Prophecy\Argument;
use PhpSpec\ObjectBehavior;

use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\CatalogBundle\Entity\Family;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\CatalogBundle\Model\Product;
use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\MagentoConnectorBundle\Guesser\NormalizerGuesser;
use Pim\Bundle\MagentoConnectorBundle\Manager\AssociationTypeManager;
use Pim\Bundle\MagentoConnectorBundle\Manager\CurrencyManager;
use Pim\Bundle\MagentoConnectorBundle\Manager\LocaleManager;
use Pim\Bundle\MagentoConnectorBundle\Merger\MagentoMappingMerger;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\ProductNormalizer;
use Pim\Bundle\MagentoConnectorBundle\Webservice\Webservice;
use Pim\Bundle\ConnectorMappingBundle\Mapper\MappingCollection;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientParametersRegistry;
use Pim\Bundle\TransformBundle\Converter\MetricConverter;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductProcessorSpec extends ObjectBehavior
{
    function let(
        WebserviceGuesser      $webserviceGuesser,
        NormalizerGuesser      $normalizerGuesser,
        LocaleManager          $localeManager,
        MagentoMappingMerger   $storeViewMappingMerger,
        CurrencyManager        $currencyManager,
        ChannelManager         $channelManager,
        MagentoMappingMerger   $categoryMappingMerger,
        MagentoMappingMerger   $attributeMappingMerger,
        MetricConverter        $metricConverter,
        AssociationTypeManager $associationTypeManager,
        Webservice             $webservice,
        MappingCollection      $mappingCollection,
        NormalizerGuesser      $normalizerGuesser,
        ProductNormalizer      $productNormalizer,
        Product                $product,
        Channel                $channel
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

        $clientParameters = MagentoSoapClientParametersRegistry::getInstance(null, null, null, '/api/soap/?wsdl', 'default');
        $webserviceGuesser->getWebservice($clientParameters)->willReturn($webservice);
        $storeViewMappingMerger->getMapping()->willReturn($mappingCollection);

        $webservice->getStoreViewsList()->willReturn(
            array(
                array(
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
            array(
                'name' => array(
                    'attribute_id' => '71',
                    'code' => 'name',
                    'type' => 'text',
                    'required' => '1',
                    'scope' => 'store'
                )
            )
        );

        $normalizerGuesser->getProductNormalizer(
            $clientParameters,
            null,
            4,
            null
        )
        ->willReturn($productNormalizer);

        $webservice->getAllAttributesOptions()->willReturn(array());
        $webservice->getProductsStatus(array($product))->willReturn(
            array(
                array(
                    'product_id' => '1',
                    'sku' => 'sku-000',
                    'name' => 'Product example',
                    'set' => '4',
                    'type' => 'simple',
                    'category_ids' => array('207'),
                    'website_ids' => array('1')
                )
            )
        );

        $channelManager->getChannelByCode(null)->willReturn($channel);
    }

    function it_is_configurable(
        $categoryMappingMerger,
        $attributeMappingMerger,
        $mappingCollection
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

    function it_processes_new_products(
        $webservice,
        $attributeMappingMerger,
        $categoryMappingMerger,
        $productNormalizer,
        $mappingCollection,
        Product         $product,
        Channel         $channel,
        Family          $family,
        MetricConverter $metricConverter
    ) {
        $categoryMappingMerger->getMapping()->willReturn($mappingCollection);
        $attributeMappingMerger->getMapping()->willReturn($mappingCollection);

        $product->getFamily()->shouldBeCalled()->willReturn($family);
        $family->getCode()->shouldBeCalled()->willReturn('family_code');

        $webservice->getAttributeSetId('family_code')->shouldBeCalled()->willReturn('4');

        $product->getIdentifier()->shouldBeCalled()->willReturn('sku-001');

        $metricConverter->convert($product, $channel)->shouldBeCalled();

        $productNormalizer->normalize(Argument::type('\Pim\Bundle\CatalogBundle\Model\Product'), 'MagentoArray', Argument::type('array'))->shouldBeCalled();

        $this->process($product);
    }

    function it_processes_already_created_products(
        $webservice,
        $attributeMappingMerger,
        $categoryMappingMerger,
        $productNormalizer,
        $mappingCollection,
        Product         $product,
        Channel         $channel,
        Family          $family,
        MetricConverter $metricConverter
    ) {
        $categoryMappingMerger->getMapping()->willReturn($mappingCollection);
        $attributeMappingMerger->getMapping()->willReturn($mappingCollection);

        $product->getFamily()->shouldBeCalled()->willReturn($family);
        $family->getCode()->shouldBeCalled()->willReturn('family_code');

        $webservice->getAttributeSetId('family_code')->shouldBeCalled()->willReturn('4');

        $product->getIdentifier()->shouldBeCalled()->willReturn('sku-000');

        $metricConverter->convert($product, $channel)->shouldBeCalled();

        $productNormalizer->normalize(Argument::type('\Pim\Bundle\CatalogBundle\Model\Product'), 'MagentoArray', Argument::type('array'))->shouldBeCalled();

        $this->process($product);
    }

    function it_throws_an_exception_if_family_has_changed_of_the_product(
        $webservice,
        $attributeMappingMerger,
        $categoryMappingMerger,
        $productNormalizer,
        $mappingCollection,
        Product         $product,
        Family          $family,
        MetricConverter $metricConverter
    ) {
        $categoryMappingMerger->getMapping()->willReturn($mappingCollection);
        $attributeMappingMerger->getMapping()->willReturn($mappingCollection);

        $product->getFamily()->shouldBeCalled()->willReturn($family);
        $family->getCode()->shouldBeCalled()->willReturn('family_code');

        $webservice->getAttributeSetId('family_code')->shouldBeCalled()->willReturn('5');

        $product->getIdentifier()->shouldBeCalled()->willReturn('sku-000');

        $metricConverter->convert(Argument::cetera())->shouldNotBeCalled();

        $productNormalizer->normalize(Argument::cetera())->shouldNotBeCalled();

        $this->shouldThrow('\Akeneo\Bundle\BatchBundle\Item\InvalidItemException')->duringProcess($product);
    }

    function it_throws_an_exception_if_something_went_wrong_during_normalization(
        $webservice,
        $attributeMappingMerger,
        $categoryMappingMerger,
        $productNormalizer,
        $mappingCollection,
        Product         $product,
        Channel         $channel,
        Family          $family,
        MetricConverter $metricConverter
    ) {
        $categoryMappingMerger->getMapping()->willReturn($mappingCollection);
        $attributeMappingMerger->getMapping()->willReturn($mappingCollection);

        $product->getFamily()->shouldBeCalled()->willReturn($family);
        $family->getCode()->shouldBeCalled()->willReturn('family_code');

        $webservice->getAttributeSetId('family_code')->shouldBeCalled()->willReturn('4');

        $product->getIdentifier()->shouldBeCalled()->willReturn('sku-001');

        $metricConverter->convert($product, $channel)->shouldBeCalled();

        $productNormalizer->normalize(
            Argument::type('\Pim\Bundle\CatalogBundle\Model\Product'),
            'MagentoArray',
            Argument::type('array')
        )
        ->shouldBeCalled()
        ->willThrow('\Pim\Bundle\MagentoConnectorBundle\Normalizer\Exception\NormalizeException');

        $this->shouldThrow('\Akeneo\Bundle\BatchBundle\Item\InvalidItemException')->duringProcess($product);
    }
}
