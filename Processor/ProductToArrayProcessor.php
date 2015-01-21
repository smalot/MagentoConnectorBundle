<?php

namespace Pim\Bundle\MagentoConnectorBundle\Processor;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Akeneo\Bundle\BatchBundle\Item\ItemProcessorInterface;
use Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Product to array processor
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductToArrayProcessor extends AbstractConfigurableStepElement implements
    ItemProcessorInterface,
    StepExecutionAwareInterface
{
    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var StepExecution */
    protected $stepExecution;

    /** @var ChannelManager */
    protected $channelManager;

    /** @var string */
    protected $channel;

    /**
     * @param NormalizerInterface $normalizer
     * @param ChannelManager      $channelManager
     */
    public function __construct(NormalizerInterface $normalizer, ChannelManager $channelManager)
    {
        $this->normalizer     = $normalizer;
        $this->channelManager = $channelManager;
    }

    /**
     * Process item
     *
     * @param \Pim\Bundle\CatalogBundle\Model\ProductInterface $item
     *
     * @return array $product
     */
    public function process($item)
    {
        // Temporary for the need of POC
        $context = [
            'channel' => $this->channelManager->getChannelByCode($this->getChannel()),
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

        return $this->normalizer->normalize($item, 'api_import', $context);
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return [
            'channel' => [
                'type'    => 'choice',
                'options' => [
                    'choices'  => $this->channelManager->getChannelChoices(),
                    'required' => true,
                    'select2'  => true,
                    'label'    => 'pim_base_connector.export.channel.label',
                    'help'     => 'pim_base_connector.export.channel.help'
                ]
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * @return string
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * @param string $channel
     */
    public function setChannel($channel)
    {
        $this->channel = $channel;
    }
}
