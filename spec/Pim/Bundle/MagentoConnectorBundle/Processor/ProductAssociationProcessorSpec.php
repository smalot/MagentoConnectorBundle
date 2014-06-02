<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Processor;

use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\MagentoConnectorBundle\Manager\LocaleManager;
use Pim\Bundle\MagentoConnectorBundle\Merger\MagentoMappingMerger;
use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\MagentoConnectorBundle\Guesser\NormalizerGuesser;
use Pim\Bundle\MagentoConnectorBundle\Manager\AssociationTypeManager;
use Pim\Bundle\MagentoConnectorBundle\Webservice\Webservice;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientParameters;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientParametersRegistry;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\Association;
use Pim\Bundle\CatalogBundle\Entity\AssociationType;
use Akeneo\Bundle\BatchBundle\Entity\StepExecution;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ProductAssociationProcessorSpec extends ObjectBehavior
{
    function let(
        ChannelManager $channelManager,
        LocaleManager $localeManager,
        MagentoMappingMerger $storeViewMappingMerger,
        WebserviceGuesser $webserviceGuesser,
        NormalizerGuesser $normalizerGuesser,
        AssociationTypeManager $associationTypeManager,
        Webservice $webservice,
        StepExecution $stepExecution,
        MagentoSoapClientParametersRegistry $clientParametersRegistry,
        MagentoSoapClientParameters $clientParameters
    ) {
        $this->beConstructedWith(
            $webserviceGuesser,
            $normalizerGuesser,
            $localeManager,
            $storeViewMappingMerger,
            $associationTypeManager,
            $clientParametersRegistry
        );
        $this->setStepExecution($stepExecution);

        $clientParametersRegistry->getInstance(null, null, null, '/api/soap/?wsdl', 'default', null, null)->willReturn($clientParameters);
        $webserviceGuesser->getWebservice($clientParameters)->willReturn($webservice);

        $this->setPimUpSell('UPSELL');
    }

    function it_generated_association_calls_for_given_products(
        $webservice,
        ProductInterface $product,
        ProductInterface $associatedProduct,
        Association $association,
        AssociationType $associationType
    ) {
        $webservice->getAssociationsStatus($product)->willReturn(array('up_sell' => array(), 'cross_sell' => array(array('sku' => 'sku-011')), 'related' => array()));

        $product->getIdentifier()->willReturn('sku-012');
        $product->getAssociations()->willReturn(array($association));

        $association->getAssociationType()->willReturn($associationType);
        $association->getProducts()->willReturn(array($associatedProduct));

        $associatedProduct->getIdentifier()->willReturn('sku-011');

        $associationType->getCode()->willReturn('UPSELL');

        $this->process(array($product))->shouldReturn(
            array(
                'remove' => array(
                    array(
                        'type'          => 'cross_sell',
                        'product'       => 'sku-012',
                        'linkedProduct' => 'sku-011',
                        'identifierType' => 'sku'
                    )
                ),
                'create' => array(
                    array(
                        'type'          => 'up_sell',
                        'product'       => 'sku-012',
                        'linkedProduct' => 'sku-011',
                        'identifierType' => 'sku'
                    )
                )
            )
        );
    }

    function it_throws_an_exception_if_something_went_wrong_with_soap_call(
        $webservice,
        ProductInterface $product
    ) {
        $webservice->getAssociationsStatus($product)->willThrow('\Pim\Bundle\MagentoConnectorBundle\Webservice\SoapCallException');

        $this->shouldThrow('\Akeneo\Bundle\BatchBundle\Item\InvalidItemException')->duringProcess(array($product));
    }

    function it_is_configurable()
    {
        $this->setPimUpSell('foo');
        $this->setPimCrossSell('bar');
        $this->setPimRelated('fooo');
        $this->setPimGrouped('baar');

        $this->getPimUpSell()->shouldReturn('foo');
        $this->getPimCrossSell()->shouldReturn('bar');
        $this->getPimRelated()->shouldReturn('fooo');
        $this->getPimGrouped()->shouldReturn('baar');
    }
}
