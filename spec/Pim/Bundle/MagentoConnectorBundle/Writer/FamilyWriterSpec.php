<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Writer;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\MagentoConnectorBundle\Entity\MagentoConfiguration;
use Pim\Bundle\MagentoConnectorBundle\Manager\MagentoConfigurationManager;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClient;
use Pim\Bundle\MagentoConnectorBundle\Writer\ErrorHelper;

class FamilyWriterSpec extends ObjectBehavior
{
    function let(MagentoConfigurationManager $configurationManager, ErrorHelper $errorHelper)
    {
        $this->beConstructedWith($configurationManager, $errorHelper);
    }

    function it_has_good_type()
    {
        $this->shouldHaveType('\Pim\Bundle\MagentoConnectorBundle\Writer\FamilyWriter');
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
        $attributeSet1 = [
            'attribute_set_name' => 'My attr set 1',
            'My attr group1' => 1,
            'My attr group2' => 2
        ];
        $attributeSet2 = [
            'attribute_set_name' => 'My attr set 2',
            'My attr group1' => 1,
            'My attr group2' => 2
        ];
        $attributeSet3 = [
            'attribute_set_name' => 'My attr set 3',
            'My attr group1' => 1,
            'My attr group2' => 3
        ];
        $attributeSets = [$attributeSet1, $attributeSet2, $attributeSet3];

        $client->exportAttributeSets($attributeSets)->shouldBeCalled();

        $this->write($attributeSets)->shouldReturn(null);
    }

    function it_catches_errors_from_api_import_after_export(
        $errorHelper,
        MagentoSoapClient $client,
        StepExecution $stepExecution
    ) {
        $this->setConfigurationCode('config_1');
        $this->setClient($client);
        $this->setStepExecution($stepExecution);

        $attributeSet1 = [
            'attribute_set_name' => 'My attr set 1',
            'My attr group1' => 1,
            'My attr group2' => 2
        ];
        $attributeSet2 = [
            'attribute_set_name' => 'My attr set 2',
            'My attr group1' => 1,
            'My attr group2' => 2
        ];
        $attributeSet3 = [
            'attribute_set_name' => 'My attr set 3',
            'My attr group1' => 1,
            'My attr group2' => 3
        ];
        $attributeSets = [$attributeSet1, $attributeSet2, $attributeSet3];

        $error = new \SoapFault('1', 'import_failed');
        $client->exportAttributeSets($attributeSets)->shouldBeCalled()->willThrow($error);


        $errorHelper
            ->manageErrors(
                $stepExecution,
                $error,
                $attributeSets,
                'attribute_set_id',
                'family_writer'
            )
            ->shouldBeCalled();

        $this->write($attributeSets)->shouldReturn(null);
    }
}
