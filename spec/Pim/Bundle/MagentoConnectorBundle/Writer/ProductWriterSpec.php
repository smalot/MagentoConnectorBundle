<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Writer;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\MagentoConnectorBundle\Entity\MagentoConfiguration;
use Pim\Bundle\MagentoConnectorBundle\Manager\MagentoConfigurationManager;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClient;
use Pim\Bundle\MagentoConnectorBundle\Writer\ErrorHelper;

class ProductWriterSpec extends ObjectBehavior
{
    function let(MagentoConfigurationManager $configurationManager, ErrorHelper $errorHelper)
    {
        $this->beConstructedWith($configurationManager, $errorHelper);
    }

    function it_has_good_type()
    {
        $this->shouldHaveType('\Pim\Bundle\MagentoConnectorBundle\Writer\ProductWriter');
    }

    function it_is_initializable(
        $configurationManager,
        MagentoConfiguration $magentoConfiguration,
        MagentoSoapClient $client
    ) {
        $this->setConfigurationCode('config_1');

        $configurationManager
            ->getMagentoConfigurationByCode('config_1')
            ->shouldBeCalled()
            ->willReturn($magentoConfiguration);
        $configurationManager->createClient($magentoConfiguration)->shouldBeCalled()->willReturn($client);
        $magentoConfiguration->getSoapUsername()->willReturn('soap_user');
        $magentoConfiguration->getSoapApiKey()->willReturn('api_key');
        $client->login('soap_user', 'api_key')->shouldBeCalled();

        $this->initialize()->shouldReturn(null);
    }

    function it_gets_configuration_fields($configurationManager)
    {
        $configurationManager->getConfigurationChoices()->shouldBeCalled()->willReturn('config_1');

        $this->getConfigurationFields()->shouldReturn([
            'configurationCode' => [
                'type'    => 'choice',
                'options' => [
                    'choices'  => 'config_1',
                    'required' => true,
                    'select2'  => true,
                    'label'    => 'pim_magento_connector.export.configuration.label',
                    'help'     => 'pim_magento_connector.export.configuration.help'
                ]
            ]
        ]);
    }

    function it_sets_and_gets_configuration_code()
    {
        $this->getConfigurationCode()->shouldReturn(null);
        $this->setConfigurationCode('config_1')->shouldReturn($this);
        $this->getConfigurationCode()->shouldReturn('config_1');
    }

    function it_sets_and_gets_client(MagentoSoapClient $client)
    {
        $this->getClient()->shouldReturn(null);
        $this->setClient($client)->shouldReturn($this);
        $this->getClient()->shouldReturn($client);
    }

    function it_sets_a_step_execution(StepExecution $stepExecution)
    {
        $this->setStepExecution($stepExecution)->shouldReturn(null);
    }

    function it_sends_products_to_api_import(MagentoSoapClient $client)
    {
        $this->setConfigurationCode('config_1');
        $this->setClient($client);
        $product000 = [
            [
                'sku'                 => 'sku-000',
                '_type'               => 'simple',
                '_product_websites'   => 'base',
                'status'              => 1,
                'visibility'          => 4,
                '_attribute_set'      => 'my_family',
                'created_at'          => '2042-01-01 13:37:00',
                'updated_at'          => '2042-01-01 13:37:00',
                '_store'              => 'Default'
            ],
            [
                'bar'    => 'foo',
                '_store' => 'fr_fr',
            ],
            [
                '_category'      => 'my_category_2/my_category',
                '_root_category' => 'Default Category'
            ],
            [
                '_links_upsell_sku' => 'sku-001'
            ]
        ];
        $product001 = [
            [
                'sku'                 => 'sku-001',
                '_type'               => 'simple',
                '_product_websites'   => 'base',
                'status'              => 1,
                'visibility'          => 4,
                '_attribute_set'      => 'my_family',
                'created_at'          => '2042-01-01 13:37:00',
                'updated_at'          => '2042-01-01 13:37:00',
                '_store'              => 'Default'
            ],
            [
                'bar'    => 'quz',
                '_store' => 'fr_fr',
            ],
            [
                '_category'      => 'my_category_2/my_category',
                '_root_category' => 'Default Category'
            ],
            [
                '_links_crosssell_sku' => 'sku-002'
            ]
        ];
        $product003 = [
            [
                'sku'                 => 'sku-001',
                '_type'               => 'simple',
                '_product_websites'   => 'base',
                'status'              => 1,
                'visibility'          => 4,
                '_attribute_set'      => 'my_family_2',
                'created_at'          => '2042-01-01 13:37:00',
                'updated_at'          => '2042-01-01 13:37:00',
                '_store'              => 'Default'
            ],
            [
                'bar'    => 'quz',
                '_store' => 'fr_fr',
            ],
            [
                '_category'      => 'my_category_2/my_category_3',
                '_root_category' => 'Default Category'
            ],
            [
                '_links_crosssell_sku' => 'sku-000'
            ]
        ];
        $items = [$product000, $product001, $product003];
        $flattenedProducts = array_merge($product000, $product001, $product003);

        $client->exportProducts($flattenedProducts)->shouldBeCalled();

        $this->write($items)->shouldReturn(null);
    }

    function it_catches_errors_from_api_import_after_export(
        $errorHelper,
        MagentoSoapClient $client,
        StepExecution $stepExecution
    ) {
        $this->setConfigurationCode('config_1');
        $this->setClient($client);
        $this->setStepExecution($stepExecution);

        $product000 = [
            [
                'sku'                 => 'sku-000',
                '_type'               => 'simple',
                '_product_websites'   => 'base',
                'status'              => 1,
                'visibility'          => 4,
                '_attribute_set'      => 'my_family',
                'created_at'          => '2042-01-01 13:37:00',
                'updated_at'          => '2042-01-01 13:37:00',
                '_store'              => 'Default'
            ],
            [
                'bar'    => 'foo',
                '_store' => 'fr_fr',
            ],
            [
                '_category'      => 'my_category_2/my_category',
                '_root_category' => 'Default Category'
            ],
            [
                '_links_upsell_sku' => 'sku-001'
            ]
        ];
        $product001 = [
            [
                'sku'                 => 'sku-001',
                '_type'               => 'simple',
                '_product_websites'   => 'base',
                'status'              => 1,
                'visibility'          => 4,
                '_attribute_set'      => 'my_family',
                'created_at'          => '2042-01-01 13:37:00',
                'updated_at'          => '2042-01-01 13:37:00',
                '_store'              => 'Default'
            ],
            [
                'bar'    => 'quz',
                '_store' => 'fr_fr',
            ],
            [
                '_category'      => 'my_category_2/my_category',
                '_root_category' => 'Default Category'
            ],
            [
                '_links_crosssell_sku' => 'sku-002'
            ]
        ];
        $product003 = [
            [
                'sku'                 => 'sku-001',
                '_type'               => 'simple',
                '_product_websites'   => 'base',
                'status'              => 1,
                'visibility'          => 4,
                '_attribute_set'      => 'my_family_2',
                'created_at'          => '2042-01-01 13:37:00',
                'updated_at'          => '2042-01-01 13:37:00',
                '_store'              => 'Default'
            ],
            [
                'bar'    => 'quz',
                '_store' => 'fr_fr',
            ],
            [
                '_category'      => 'my_category_2/my_category_3',
                '_root_category' => 'Default Category'
            ],
            [
                '_links_crosssell_sku' => 'sku-000'
            ]
        ];
        $items = [$product000, $product001, $product003];
        $flattenedProducts = array_merge($product000, $product001, $product003);

        $error = new \SoapFault('1', 'import_failed');
        $client->exportProducts($flattenedProducts)->shouldBeCalled()->willThrow($error);

        $errorHelper
            ->manageErrors($stepExecution, $error, $flattenedProducts, 'sku', 'product_writer')
            ->shouldBeCalled();

        $this->write($items)->shouldReturn(null);
    }
}
