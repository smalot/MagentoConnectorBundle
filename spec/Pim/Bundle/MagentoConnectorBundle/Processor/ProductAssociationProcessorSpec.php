<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Processor;

use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\MagentoConnectorBundle\Guesser\NormalizerGuesser;
use Pim\Bundle\MagentoConnectorBundle\Manager\AssociationTypeManager;
use Pim\Bundle\MagentoConnectorBundle\Webservice\Webservice;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\Association;
use Pim\Bundle\CatalogBundle\Entity\AssociationType;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ProductAssociationProcessorSpec extends ObjectBehavior
{
    function let(
        ChannelManager $channelManager,
        WebserviceGuesser $webserviceGuesser,
        NormalizerGuesser $normalizerGuesser,
        AssociationTypeManager $associationTypeManager,
        Webservice $webservice
    ) {
        $this->beConstructedWith($channelManager, $webserviceGuesser, $normalizerGuesser, $associationTypeManager);

        $webserviceGuesser->getWebservice(Argument::cetera())->willReturn($webservice);
        $this->setPimUpSell('UPSELL');
    }

    function it_generated_association_calls_for_given_products(ProductInterface $product, ProductInterface $associatedProduct, Association $association, AssociationType $associationType, $webservice)
    {
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
                        'linkedProduct' => 'sku-011'
                    )
                ),
                'create' => array(
                    array(
                        'type'          => 'up_sell',
                        'product'       => 'sku-012',
                        'linkedProduct' => 'sku-011'
                    )
                )
            )
        );
    }
}
