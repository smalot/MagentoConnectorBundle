<?php

namespace Pim\Bundle\MagentoConnectorBundle\Writer;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Event\EventInterface;
use Akeneo\Bundle\BatchBundle\Event\InvalidItemEvent;
use Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Akeneo\Bundle\BatchBundle\Item\ItemWriterInterface;
use Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface;
use Doctrine\Common\Util\ClassUtils;
use Pim\Bundle\MagentoConnectorBundle\Manager\MagentoConfigurationManager;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClient;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Abstract writer
 *
 * @author     Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright  2014 Akeneo SAS (http://www.akeneo.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @deprecated
 *
 * TODO : Move the addWarning method in BatchBundle
 */
abstract class AbstractWriter extends AbstractConfigurableStepElement implements
    ItemWriterInterface, StepExecutionAwareInterface
{
    /** @var MagentoConfigurationManager */
    protected $configurationManager;

    /** @var string */
    protected $configurationCode;

    /** @var StepExecution */
    protected $stepExecution;

    /** @var MagentoSoapClient */
    protected $client;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /**
     * Constructor
     *
     * @param MagentoConfigurationManager $configurationManager
     */
    public function __construct(MagentoConfigurationManager $configurationManager)
    {
        $this->configurationManager = $configurationManager;
    }

    /**
     * Login the client to be able to call API Import method
     * This method is called once before write
     */
    public function initialize()
    {
        $code = $this->getConfigurationCode();
        $configuration = $this->configurationManager->getMagentoConfigurationByCode($code);
        $this->client  = $this->configurationManager->createClient($configuration);

        $this->client->login($configuration->getSoapUsername(), $configuration->getSoapApiKey());
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return [
            'configurationCode' => [
                'type'    => 'choice',
                'options' => [
                    'choices'  => $this->configurationManager->getConfigurationChoices(),
                    'required' => true,
                    'select2'  => true,
                    'label'    => 'pim_magento_connector.export.configuration.label',
                    'help'     => 'pim_magento_connector.export.configuration.help'
                ]
            ]
        ];
    }

    /**
     * Get the Magento configuration code
     *
     * @return string
     */
    public function getConfigurationCode()
    {
        return $this->configurationCode;
    }

    /**
     * Set the Magento configuration code
     *
     * @param string $configurationCode
     *
     * @return ProductWriter
     */
    public function setConfigurationCode($configurationCode)
    {
        $this->configurationCode = $configurationCode;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * Set the event dispatcher
     *
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Add a warning based on the stepExecution.
     *
     * @param string $message
     * @param array  $messageParameters
     * @param mixed  $item
     */
    protected function addWarning($message, array $messageParameters = [], $item = null)
    {
        $this->stepExecution->addWarning(
            $this->getName(),
            $message,
            $messageParameters,
            $item
        );

        if (!is_array($item)) {
            $item = array();
        }

        $event = new InvalidItemEvent(ClassUtils::getClass($this), $message, $messageParameters, $item);
        $this->eventDispatcher->dispatch(EventInterface::INVALID_ITEM, $event);
    }
}
