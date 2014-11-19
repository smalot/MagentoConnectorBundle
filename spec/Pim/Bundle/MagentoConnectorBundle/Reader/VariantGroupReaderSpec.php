<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Reader;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\BaseConnectorBundle\Reader\Doctrine\ORMProductReader;
use Pim\Bundle\CatalogBundle\Entity\Group;

class VariantGroupReaderSpec extends ObjectBehavior
{
    public function let(ORMProductReader $productReader)
    {
        $this->beConstructedWith($productReader);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType('\Pim\Bundle\MagentoConnectorBundle\Reader\VariantGroupReader');
    }

    public function it_gives_configuration_fields($productReader)
    {
        $configurationFields = [
            'channel' => [
                'type'    => 'choice',
                'options' => [
                    'choices'  => ['channelCode' => 'channelLabel'],
                    'required' => true,
                    'select2'  => true,
                    'label'    => 'pim_base_connector.export.channel.label',
                    'help'     => 'pim_base_connector.export.channel.help'
                ]
            ]
        ];

        $productReader->getConfigurationFields()->willReturn($configurationFields);

        $this->getConfigurationFields()->shouldReturn($configurationFields);
    }

    public function it_sets_a_channel($productReader)
    {
        $productReader->setChannel('myChannel')->shouldBeCalled();

        $this->setChannel('myChannel')->shouldReturn(null);
    }

    public function it_gives_a_channel($productReader)
    {
        $productReader->getChannel()->willReturn('myChannel');

        $this->getChannel()->shouldReturn('myChannel');
    }

    public function it_gives_configuration($productReader)
    {
        $productReader->getConfigurationFields()->willReturn([
            'channel' => [
                'type'    => 'choice',
                'options' => [
                    'choices'  => ['channelCode' => 'channelLabel'],
                    'required' => true,
                    'select2'  => true,
                    'label'    => 'pim_base_connector.export.channel.label',
                    'help'     => 'pim_base_connector.export.channel.help'
                ]
            ]
        ]);

        $productReader->getChannel()->shouldBeCalled()->willReturn('myChannel');

        $this->getConfiguration()->shouldReturn(['channel' => 'myChannel']);
    }

    public function it_sets_step_execution(StepExecution $stepExecution, $productReader)
    {
        $productReader->setStepExecution($stepExecution)->shouldBeCalled();

        $this->setStepExecution($stepExecution)->shouldReturn(null);
    }

    public function it_initializes_parameters($productReader)
    {
        $productReader->initialize()->shouldBeCalled();

        $this->initialize();
    }
}
