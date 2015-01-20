<?php

namespace Pim\Bundle\MagentoConnectorBundle\Processor;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Akeneo\Bundle\BatchBundle\Item\ItemProcessorInterface;
use Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Process variant group in array of Magento configurable products
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VariantGroupToArrayProcessor extends AbstractConfigurableStepElement implements
    ItemProcessorInterface,
    StepExecutionAwareInterface
{
    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var StepExecution */
    protected $stepExecution;

    /** @var string */
    protected $channel;

    /** @var ChannelManager */
    protected $channelManager;

    /**
     * Constructor
     *
     * @param NormalizerInterface $normalizer
     * @param ChannelManager      $channelManager
     */
    public function __construct(
        NormalizerInterface $normalizer,
        ChannelManager $channelManager
    ) {
        $this->normalizer     = $normalizer;
        $this->channelManager = $channelManager;
    }

    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        $context = [
            'channel' => $this->channelManager->getChannelByCode($this->getChannel()),
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
            ]
        ];

        $normalized = $this->normalizer->normalize($item, 'api_import', $context);

        return empty($normalized) ? null : $normalized;
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
