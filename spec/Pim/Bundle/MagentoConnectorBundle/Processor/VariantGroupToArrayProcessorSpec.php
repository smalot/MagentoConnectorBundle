<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Processor;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\CatalogBundle\Entity\Group;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class VariantGroupToArrayProcessorSpec extends ObjectBehavior
{
    public function let(NormalizerInterface $normalizer, ChannelManager $channelManager)
    {
        $this->beConstructedWith($normalizer, $channelManager);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType('\Pim\Bundle\MagentoConnectorBundle\Processor\VariantGroupToArrayProcessor');
    }

    public function it_returns_null_if_group_can_not_be_normalize(
        Channel $channel,
        Group $group,
        $normalizer,
        $channelManager
    ) {
        $this->setChannel('foo');
        $channelManager->getChannelByCode('foo')->willReturn($channel);

        $context = [
            'channel'             => $channel,
            'defaultStoreView'    => 'Default',
            'defaultLocale'       => 'en_US',
            'website'             => 'base',
            'defaultCurrency'     => 'USD',
            'visibility'          => '4',
            'enabled'             => '1',
            'storeViewMapping'    => ['fr_FR' => 'fr_fr'],
            'userCategoryMapping' => ['Master catalog' => 'Default Category']
        ];

        $normalizer->normalize($group, 'api_import', $context)->willReturn([]);

        $this->process($group)->shouldReturn(null);
    }

    public function it_processes_a_variant_group_to_array(
        Channel $channel,
        Group $group,
        $normalizer,
        $channelManager
    ) {
        $this->setChannel('foo');
        $channelManager->getChannelByCode('foo')->willReturn($channel);

        $context = [
            'channel'             => $channel,
            'defaultStoreView'    => 'Default',
            'defaultLocale'       => 'en_US',
            'website'             => 'base',
            'defaultCurrency'     => 'USD',
            'visibility'          => '4',
            'enabled'             => '1',
            'storeViewMapping'    => ['fr_FR' => 'fr_fr'],
            'userCategoryMapping' => ['Master catalog' => 'Default Category']
        ];

        $normalizer->normalize($group, 'api_import', $context)->willReturn(['bar']);

        $this->process($group)->shouldReturn(['bar']);
    }

    public function it_gives_configuration_fields($channelManager)
    {
        $channelManager->getChannelChoices()->willReturn(['foo', 'bar']);
        $this->getConfigurationFields()->shouldReturn(
            [
                'channel' => [
                    'type'    => 'choice',
                    'options' => [
                        'choices'  => ['foo', 'bar'],
                        'required' => true,
                        'select2'  => true,
                        'label'    => 'pim_base_connector.export.channel.label',
                        'help'     => 'pim_base_connector.export.channel.help'
                    ]
                ]
            ]
        );
    }

    public function it_set_and_get_channel(Channel $channel)
    {
        $this->getChannel()->shouldReturn(null);
        $this->setChannel($channel);
        $this->getChannel()->shouldReturn($channel);
    }
} 