<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Processor;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\CatalogBundle\Model\Association;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AssociatedProductToArrayProcessorSpec extends ObjectBehavior
{
    public function let(NormalizerInterface $normalizer, ChannelManager $channelManager)
    {
        $this->beConstructedWith($normalizer, $channelManager);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType('\Pim\Bundle\MagentoConnectorBundle\Processor\AssociatedProductToArrayProcessor');
    }

    public function it_processes_an_association_in_array(
        Channel $channel,
        Association $association,
        $normalizer,
        $channelManager
    ) {
        $this->setChannel('foo');
        $channelManager->getChannelByCode('foo')->willReturn($channel);
        $context = [
            'channel' => $channel,
            'defaultStoreView'    => 'Default',
            'defaultLocale'       => 'en_US',
            'website'             => 'base',
            'defaultCurrency'     => 'USD',
            'visibility'          => '4',
            'enabled'             => '1',
            'storeViewMapping'    => [
                'fr_FR' => 'fr_fr'
            ],
            'userCategoryMapping' => [
                'Master catalog' => 'Default Category'
            ],
            'associationMapping'  => [
                'UPSELL'  => 'upsell',
                'X_SELL'  => 'crosssel',
                'RELATED' => 'related',
                'PACK'    => ''
            ],
            'attributeMapping'    => [
                'sku'               => 'sku',
                'name'              => 'name',
                'description'       => 'description',
                'short_description' => 'short_description',
                'status'            => 'enabled'
            ]
        ];

        $normalizer->normalize($association, 'api_import', $context)->shouldBeCalled();

        $this->process($association);
    }

    public function it_set_and_get_channel(Channel $channel)
    {
        $this->getChannel()->shouldReturn(null);
        $this->setChannel($channel);
        $this->getChannel()->shouldReturn($channel);
    }

    public function it_gives_configuration_fields(ChannelManager $channelManager)
    {
        $channelManager->getChannelChoices()->willReturn(['foo', 'bar']);

        $this->getConfigurationFields()->shouldReturn([
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
        ]);
    }
}
