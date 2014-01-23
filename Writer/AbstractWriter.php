<?php

namespace Pim\Bundle\MagentoConnectorBundle\Writer;

use Symfony\Component\Validator\Constraints as Assert;
use Oro\Bundle\BatchBundle\Item\ItemWriterInterface;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;

use Pim\Bundle\MagentoConnectorBundle\Item\MagentoItemStep;
use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientParameters;
use Pim\Bundle\MagentoConnectorBundle\Validator\Constraints\HasValidCredentials;
use Pim\Bundle\MagentoConnectorBundle\Validator\Constraints\MagentoUrl;

/**
 * Magento product writer
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @HasValidCredentials()
 */
abstract class AbstractWriter extends MagentoItemStep implements ItemWriterInterface
{
    /**
     * @var ChannelManager
     */
    protected $channelManager;

    /**
     * @var WebserviceGuesser
     */
    protected $webserviceGuesser;

    /**
     * @Assert\NotBlank(groups={"Execution"})
     */
    protected $channel;

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
     * @return AbstractWriter
     */
    public function setChannel($channel)
    {
        $this->channel = $channel;

        return $this;
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
