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
    ItemWriterInterface,
    StepExecutionAwareInterface
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

    /** @var ErrorHelper */
    protected $errorHelper;

    /**
     * @param MagentoConfigurationManager $configurationManager
     * @param ErrorHelper                 $errorHelper
     */
    public function __construct(MagentoConfigurationManager $configurationManager, ErrorHelper $errorHelper)
    {
        $this->configurationManager = $configurationManager;
        $this->errorHelper          = $errorHelper;
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
     * Flatten items by concatenating entity parts into one array
     * $items = [entity1, e2, e3, ...]
     * entity = [part1, part2, p3, ...]
     * Returns [entity1 part1, e1p2, e2p1, e2p2, e3p1, ...]
     *
     * @param array $entities Items received from ItemStep
     *
     * @return array
     */
    protected function getFlattenedItems(array $items)
    {
        $flattenedItems = [];
        foreach ($items as $entity) {
            $flattenedItems = array_merge($flattenedItems, $entity);
        }

        return $flattenedItems;
    }

    /**
     * Add a warning for each failed entity
     *
     * @param array $failedEntities
     */
    protected function manageFailedEntities(array $failedEntities)
    {
        foreach ($failedEntities as $index => $errors) {
            foreach ($errors as $error) {
                $this->addWarning($error, [], [$index]);
            }
        }
    }

    /**
     * Add a warning based on the stepExecution.
     *
     * @param string $message
     * @param array  $messageParameters
     * @param mixed  $item
     */
    protected function addWarning($message, array $messageParameters = [], $item = [])
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
