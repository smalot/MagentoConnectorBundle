<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Writer;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\MagentoConnectorBundle\Entity\MagentoConfiguration;
use Pim\Bundle\MagentoConnectorBundle\Manager\MagentoConfigurationManager;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClient;
use Pim\Bundle\MagentoConnectorBundle\Writer\ErrorHelper;

class AddAttributeToSetsWriterSpec extends ObjectBehavior
{
    function let(MagentoConfigurationManager $configurationManager, ErrorHelper $errorHelper)
    {
        $this->beConstructedWith($configurationManager, $errorHelper);
    }

    function it_has_good_type()
    {
        $this->shouldHaveType('\Pim\Bundle\MagentoConnectorBundle\Writer\AddAttributeToSetsWriter');
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
        $association1 = [
            [
                'attribute_set_id'   => 'Family Label',
                'attribute_id'       => 'attribute_code_1',
                'attribute_group_id' => 'Group Label',
                'sort_order'         => '1'
            ],
            [
                'attribute_set_id'   => 'Family 2 Label',
                'attribute_id'       => 'attribute_code_1',
                'attribute_group_id' => 'Group Label',
                'sort_order'         => '1'
            ]
        ];
        $association2 = [
            [
                'attribute_set_id'   => 'Family Label',
                'attribute_id'       => 'attribute_code_2',
                'attribute_group_id' => 'Group Label',
                'sort_order'         => '2'
            ],
            [
                'attribute_set_id'   => 'Family 2 Label',
                'attribute_id'       => 'attribute_code_2',
                'attribute_group_id' => 'Group Label',
                'sort_order'         => '2'
            ]
        ];
        $association3 = [
            [
                'attribute_set_id'   => 'Family 3 Label',
                'attribute_id'       => 'attribute_code_3',
                'attribute_group_id' => 'Group 2 Label',
                'sort_order'         => '1'
            ]
        ];
        $items = [$association1, $association2, $association3];
        $flattenedAssociations = array_merge($association1, $association2, $association3);

        $client->addAttributeToSets($flattenedAssociations)->shouldBeCalled();

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

        $association1 = [
            [
                'attribute_set_id'   => 'Family Label',
                'attribute_id'       => 'attribute_code_1',
                'attribute_group_id' => 'Group Label',
                'sort_order'         => '1'
            ],
            [
                'attribute_set_id'   => 'Family 2 Label',
                'attribute_id'       => 'attribute_code_1',
                'attribute_group_id' => 'Group Label',
                'sort_order'         => '1'
            ]
        ];
        $association2 = [
            [
                'attribute_set_id'   => 'Family Label',
                'attribute_id'       => 'attribute_code_2',
                'attribute_group_id' => 'Group Label',
                'sort_order'         => '2'
            ],
            [
                'attribute_set_id'   => 'Family 2 Label',
                'attribute_id'       => 'attribute_code_2',
                'attribute_group_id' => 'Group Label',
                'sort_order'         => '2'
            ]
        ];
        $association3 = [
            [
                'attribute_set_id'   => 'Family 3 Label',
                'attribute_id'       => 'attribute_code_3',
                'attribute_group_id' => 'Group 2 Label',
                'sort_order'         => '1'
            ]
        ];
        $items = [$association1, $association2, $association3];
        $flattenedAssociations = array_merge($association1, $association2, $association3);

        $error = new \SoapFault('1', 'import_failed');
        $client->addAttributeToSets($flattenedAssociations)->shouldBeCalled()->willThrow($error);

        $errorHelper
            ->manageErrors(
                $stepExecution,
                $error,
                $flattenedAssociations,
                'attribute_set_id',
                'add_attribute_to_sets_writer'
            )
            ->shouldBeCalled();

        $this->write($items)->shouldReturn(null);
    }
}
