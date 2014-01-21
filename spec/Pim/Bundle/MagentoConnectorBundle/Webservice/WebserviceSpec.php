<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Webservice;

use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClient;
use Pim\Bundle\MagentoConnectorBundle\Webservice\Webservice;

use PhpSpec\ObjectBehavior;

class WebserviceSpec extends ObjectBehavior
{
    public function let(MagentoSoapClient $magentoSoapClient)
    {
        $this->beConstructedWith($magentoSoapClient);
    }

    public function it_calls_soap_client_to_send_new_category(
        MagentoSoapClient $magentoSoapClient
    ) {
        $magentoSoapClient->call(
            Webservice::SOAP_ACTION_CATEGORY_CREATE,
            array('foo')
        )->willReturn(12);

        $this->sendNewCategory(array('foo'))->shouldReturn(12);
    }

    public function it_calls_soap_client_to_send_category_update(
        MagentoSoapClient $magentoSoapClient
    ) {
        $magentoSoapClient->call(
            Webservice::SOAP_ACTION_CATEGORY_UPDATE,
            array('foo')
        )->shouldBeCalled();

        $this->sendUpdateCategory(array('foo'));
    }

    public function it_calls_soap_client_to_send_category_move(
        MagentoSoapClient $magentoSoapClient
    ) {
        $magentoSoapClient->call(
            Webservice::SOAP_ACTION_CATEGORY_MOVE,
            array('foo')
        )->shouldBeCalled();

        $this->sendMoveCategory(array('foo'));
    }

    public function it_calls_soap_client_to_get_categories_status(
        MagentoSoapClient $magentoSoapClient
    ) {
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
}
