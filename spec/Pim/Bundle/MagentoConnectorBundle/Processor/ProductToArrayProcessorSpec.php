<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Processor;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\CatalogBundle\Model\AbstractAssociation;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductToArrayProcessorSpec extends ObjectBehavior
{
    function let(NormalizerInterface $normalizer, ChannelManager $channelManager)
    {
        $this->beConstructedWith($normalizer, $channelManager);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('\Pim\Bundle\MagentoConnectorBundle\Processor\ProductToArrayProcessor');
    }

    function it_processes_a_product_in_array(
        ProductInterface $product,
        Channel $channel,
        AbstractAssociation $associationXSell,
        AbstractAssociation $associationUpSell,
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
            'enabled'             => true,
            'storeViewMapping'    => [
                'fr_FR' => 'fr_fr'
            ],
            'userCategoryMapping' => [
                'Master catalog' => 'Default Category'
            ],
            'associationMapping'  => [
                'UPSELL'  => 'upsell',
                'X_SELL'  => 'crosssell',
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

        $normalizer->normalize($product, 'api_import', $context)->shouldBeCalled()->willReturn([
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
            ]
        ]);
        $normalizer->normalize($associationXSell, 'api_import', $context)->shouldBeCalled()->willReturn([
            [
                '_links_crosssell_sku' => 'sku-001'
            ],
            [
                '_links_crosssell_sku' => 'sku-002'
            ]
        ]);
        $normalizer->normalize($associationUpSell, 'api_import', $context)->shouldBeCalled()->willReturn([
            [
                '_links_upsell_sku' => 'sku-003'
            ],
            [
                '_links_upsell_sku' => 'sku-002'
            ]
        ]);
        $product->getAssociations()->willReturn([$associationUpSell, $associationXSell]);

        $this->process($product)->shouldReturn([
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
                '_links_upsell_sku' => 'sku-003'
            ],
            [
                '_links_upsell_sku' => 'sku-002'
            ],
            [
                '_links_crosssell_sku' => 'sku-001'
            ],
            [
                '_links_crosssell_sku' => 'sku-002'
            ]
        ]);
    }

    function it_sets_and_gets_channel(Channel $channel)
    {
        $this->getChannel()->shouldReturn(null);
        $this->setChannel($channel);
        $this->getChannel()->shouldReturn($channel);
    }

    function it_gives_configuration_fields($channelManager)
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
