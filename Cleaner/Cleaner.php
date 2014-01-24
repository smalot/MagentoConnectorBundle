<?php

namespace Pim\Bundle\MagentoConnectorBundle\Cleaner;

use Symfony\Component\Validator\Constraints as Assert;
use Oro\Bundle\BatchBundle\Step\StepExecutionAwareInterface;
use Oro\Bundle\BatchBundle\Entity\StepExecution;
use Pim\Bundle\MagentoConnectorBundle\Item\MagentoItemStep;
use Pim\Bundle\MagentoConnectorBundle\Validator\Constraints\HasValidCredentials;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser;

/**
 * Magento item cleaner
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @HasValidCredentials()
 */
abstract class Cleaner extends MagentoItemStep implements StepExecutionAwareInterface
{
    /**
     * @var StepExecution
     */
    protected $stepExecution;

    /**
     * @Assert\NotBlank(groups={"Execution"})
     */
    protected $channel;

    /**
     * get channel
     *
     * @return string channel
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * Set channel
     *
     * @param string $channel channel
     *
     * @return AbstractProcessor
     */
    public function setChannel($channel)
    {
        $this->channel = $channel;

        return $this;
    }

    /**
     * Constructor
     *
     * @param ChannelManager    $channelManager
     * @param WebserviceGuesser $webserviceGuesser
     */
    public function __construct(
        ChannelManager $channelManager,
        WebserviceGuesser $webserviceGuesser
    ) {
        parent::__construct($webserviceGuesser);

        $this->channelManager = $channelManager;
    }

    /**
     * {@inheritdoc}
     */
    abstract public function execute();

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return array_merge(
            parent::getConfigurationFields(),
            array(
                'channel'      => array(
                    'type'    => 'choice',
                    'options' => array(
                        'choices'  => $this->channelManager->getChannelChoices(),
                        'required' => true
                    )
                )
            )
        );
    }
}
