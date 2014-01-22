<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Webservice;

use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClient;
use Pim\Bundle\MagentoConnectorBundle\Webservice\Webservice;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;

use PhpSpec\ObjectBehavior;

class WebserviceSpec extends ObjectBehavior
{
    public function let(MagentoSoapClient $magentoSoapClient)
    {
        $this->beConstructedWith($magentoSoapClient);
    }

    public function it_calls_soap_client_to_send_new_category($magentoSoapClient)
    {
        $magentoSoapClient->call(
            Webservice::SOAP_ACTION_CATEGORY_CREATE,
            array('foo')
        )->willReturn(12);

        $this->sendNewCategory(array('foo'))->shouldReturn(12);
    }

    public function it_calls_soap_client_to_send_category_update($magentoSoapClient)
    {
        $magentoSoapClient->call(
            Webservice::SOAP_ACTION_CATEGORY_UPDATE,
            array('foo')
        )->shouldBeCalled();

        $this->sendUpdateCategory(array('foo'));
    }

    public function it_calls_soap_client_to_send_category_move($magentoSoapClient)
    {
        $magentoSoapClient->call(
            Webservice::SOAP_ACTION_CATEGORY_MOVE,
            array('foo')
        )->shouldBeCalled();

        $this->sendMoveCategory(array('foo'));
    }

    public function it_calls_soap_client_to_get_categories_status($magentoSoapClient)
    {
        $tree = array(
            'category_id' => 1,
            'children' => array(
                array(
                    'category_id' => 3,
                    'children' => array()
                )
            )
        );

        $flattenTree = array(
            1 => array(
                'category_id' => 1,
                'children' => array(
                    array(
                        'category_id' => 3,
                        'children' => array()
                    )
                )
            ),
            3 => array(
                'category_id' => 3,
                'children' => array()
            )
        );

        $magentoSoapClient->call(
            Webservice::SOAP_ACTION_CATEGORY_TREE
        )->willReturn($tree);

        $this->getCategoriesStatus()->shouldReturn($flattenTree);
    }

    public function it_gets_association_status_for_a_given_product($magentoSoapClient, ProductInterface $product)
    {
        $magentoSoapClient->call('catalog_product_link.list', array('up_sell', 'sku-012'))->willReturn('up_sell');
        $magentoSoapClient->call('catalog_product_link.list', array('cross_sell', 'sku-012'))->willReturn('cross_sell');
        $magentoSoapClient->call('catalog_product_link.list', array('related', 'sku-012'))->willReturn('related');

        $product->getIdentifier()->willReturn('sku-012');

        $this->getAssociationsStatus($product)->shouldReturn(
            array(
                'up_sell'    => 'up_sell',
                'cross_sell' => 'cross_sell',
                'related'    => 'related'
            )
        );
    }

    public function it_send_remove_call($magentoSoapClient)
    {
        $magentoSoapClient->call('catalog_product_link.remove', array('foo'))->shouldBeCalled();

        $this->removeProductAssociation(array('foo'));
    }

    public function it_send_create_call($magentoSoapClient)
    {
        $magentoSoapClient->call('catalog_product_link.assign', array('bar'))->shouldBeCalled();

        $this->createProductAssociation(array('bar'));
    }
}
