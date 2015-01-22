<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Writer;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\MagentoConnectorBundle\Entity\MagentoConfiguration;
use Pim\Bundle\MagentoConnectorBundle\Manager\MagentoConfigurationManager;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClient;
use Pim\Bundle\MagentoConnectorBundle\Writer\ErrorHelper;

class AttributeWriterSpec extends ObjectBehavior
{
    function let(MagentoConfigurationManager $configurationManager, ErrorHelper $errorHelper)
    {
        $this->beConstructedWith($configurationManager, $errorHelper);
    }

    function it_has_good_type()
    {
        $this->shouldHaveType('\Pim\Bundle\MagentoConnectorBundle\Writer\AttributeWriter');
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

    function it_sends_attributes_to_api_import(MagentoSoapClient $client)
    {
        $this->setConfigurationCode('config_1');
        $this->setClient($client);
        $attribute1 = [
            'attribute_id'     => 'attribute_code',
            'default'          => 'value',
            'input'            => 'text',
            'type'             => 'text',
            'label'            => 'My attribute',
            'global'           => false,
            'required'         => true,
            'visible_on_front' => true,
            'unique'           => false,
        ];
        $attribute2 = [
            'attribute_id'     => 'attribute_code_2',
            'default'          => 'My option',
            'input'            => 'select',
            'type'             => 'varchar',
            'label'            => 'My attribute 2',
            'global'           => false,
            'required'         => true,
            'visible_on_front' => true,
            'unique'           => false,
            'option'           => [
                'value' => [
                    'option_code' => [0 => 'My option']
                ],
                'order' => [
                    'option_code' => 0
                ]
            ]
        ];
        $attribute3 = [
            'attribute_id'     => 'attribute_code_3',
            'default'          => 'value',
            'input'            => 'text',
            'type'             => 'text',
            'label'            => 'My attribute 3',
            'global'           => false,
            'required'         => true,
            'visible_on_front' => true,
            'unique'           => false,
        ];
        $attributes = [$attribute1, $attribute2, $attribute3];

        $client->exportAttributes($attributes)->shouldBeCalled();

        $this->write($attributes)->shouldReturn(null);
    }

    function it_catches_errors_from_api_import_after_export(
        $errorHelper,
        MagentoSoapClient $client,
        StepExecution $stepExecution
    ) {
        $this->setConfigurationCode('config_1');
        $this->setClient($client);
        $this->setStepExecution($stepExecution);

        $attribute1 = [
            'attribute_id'     => 'attribute_code',
            'default'          => 'value',
            'input'            => 'text',
            'type'             => 'text',
            'label'            => 'My attribute',
            'global'           => false,
            'required'         => true,
            'visible_on_front' => true,
            'unique'           => false,
        ];
        $attribute2 = [
            'attribute_id'     => 'attribute_code_2',
            'default'          => 'My option',
            'input'            => 'select',
            'type'             => 'varchar',
            'label'            => 'My attribute 2',
            'global'           => false,
            'required'         => true,
            'visible_on_front' => true,
            'unique'           => false,
            'option'           => [
                'value' => [
                    'option_code' => [0 => 'My option']
                ],
                'order' => [
                    'option_code' => 0
                ]
            ]
        ];
        $attribute3 = [
            'attribute_id'     => 'attribute_code_3',
            'default'          => 'value',
            'input'            => 'text',
            'type'             => 'text',
            'label'            => 'My attribute 3',
            'global'           => false,
            'required'         => true,
            'visible_on_front' => true,
            'unique'           => false,
        ];
        $attributes = [$attribute1, $attribute2, $attribute3];

        $error = new \SoapFault('1', 'import_failed');
        $client->exportAttributes($attributes)->shouldBeCalled()->willThrow($error);

        $errorHelper
            ->manageErrors(
                $stepExecution,
                $error,
                $attributes,
                'attribute_id',
                'attribute_writer'
            )
            ->shouldBeCalled();

        $this->write($attributes)->shouldReturn(null);
    }
}
