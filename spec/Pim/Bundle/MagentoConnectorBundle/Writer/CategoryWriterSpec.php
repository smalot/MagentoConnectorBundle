<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Writer;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\MagentoConnectorBundle\Entity\MagentoConfiguration;
use Pim\Bundle\MagentoConnectorBundle\Manager\MagentoConfigurationManager;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClient;
use Pim\Bundle\MagentoConnectorBundle\Writer\ErrorHelper;

class CategoryWriterSpec extends ObjectBehavior
{
    function let(MagentoConfigurationManager $configurationManager, ErrorHelper $errorHelper)
    {
        $this->beConstructedWith($configurationManager, $errorHelper);
    }

    function it_has_good_type()
    {
        $this->shouldHaveType('\Pim\Bundle\MagentoConnectorBundle\Writer\CategoryWriter');
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

    function it_associates_attributes_to_sets(MagentoSoapClient $client)
    {
        $this->setConfigurationCode('config_1');
        $this->setClient($client);
        $category1 = [
            [
                '_root'             => 'Default Category',
                'name'              => 'Parent Category',
                '_category'         => 'Parent Category',
                'is_active'         => 'yes',
                'position'          => 1,
                'include_in_menu'   => 'yes',
                'available_sort_by' => 'position',
                'default_sort_by'   => 'position'
            ],
            [
                'name'              => 'Categorie parent',
                '_store'            => 'fr_fr',
                '_root'             => 'Default Category'
            ]
        ];
        $category2 = [
            [
                '_root'             => 'Default Category',
                'name'              => 'My category 1',
                '_category'         => 'Parent Category/My category 1',
                'is_active'         => 'yes',
                'position'          => 2,
                'include_in_menu'   => 'yes',
                'available_sort_by' => 'position',
                'default_sort_by'   => 'position'
            ],
            [
                'name'              => 'Ma categorie 1',
                '_store'            => 'fr_fr',
                '_root'             => 'Default Category'
            ]
        ];
        $category3 = [
            [
                '_root'             => 'Default Category',
                'name'              => 'My category',
                '_category'         => 'Parent Category/My category 2',
                'is_active'         => 'yes',
                'position'          => 3,
                'include_in_menu'   => 'yes',
                'available_sort_by' => 'position',
                'default_sort_by'   => 'position'
            ],
            [
                'name'              => 'Ma categorie 3',
                '_store'            => 'fr_fr',
                '_root'             => 'Default Category'
            ]
        ];
        $items = [$category1, $category2, $category3];
        $flattenedCategories = array_merge($category1, $category2, $category3);

        $client->exportCategories($flattenedCategories)->shouldBeCalled();

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

        $category1 = [
            [
                '_root'             => 'Default Category',
                'name'              => 'Parent Category',
                '_category'         => 'Parent Category',
                'is_active'         => 'yes',
                'position'          => 1,
                'include_in_menu'   => 'yes',
                'available_sort_by' => 'position',
                'default_sort_by'   => 'position'
            ],
            [
                'name'              => 'Categorie parent',
                '_store'            => 'fr_fr',
                '_root'             => 'Default Category'
            ]
        ];
        $category2 = [
            [
                '_root'             => 'Default Category',
                'name'              => 'My category 1',
                '_category'         => 'Parent Category/My category 1',
                'is_active'         => 'yes',
                'position'          => 2,
                'include_in_menu'   => 'yes',
                'available_sort_by' => 'position',
                'default_sort_by'   => 'position'
            ],
            [
                'name'              => 'Ma categorie 1',
                '_store'            => 'fr_fr',
                '_root'             => 'Default Category'
            ]
        ];
        $category3 = [
            [
                '_root'             => 'Default Category',
                'name'              => 'My category',
                '_category'         => 'Parent Category/My category 2',
                'is_active'         => 'yes',
                'position'          => 3,
                'include_in_menu'   => 'yes',
                'available_sort_by' => 'position',
                'default_sort_by'   => 'position'
            ],
            [
                'name'              => 'Ma categorie 3',
                '_store'            => 'fr_fr',
                '_root'             => 'Default Category'
            ]
        ];
        $items = [$category1, $category2, $category3];
        $flattenedCategories = array_merge($category1, $category2, $category3);

        $error = new \SoapFault('1', 'import_failed');
        $client->exportCategories($flattenedCategories)->shouldBeCalled()->willThrow($error);

        $errorHelper
            ->manageErrors(
                $stepExecution,
                $error,
                $flattenedCategories,
                'name',
                'category_writer'
            )
            ->shouldBeCalled();

        $this->write($items)->shouldReturn(null);
    }
}
