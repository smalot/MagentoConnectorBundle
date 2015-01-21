<?php

namespace Pim\Bundle\MagentoConnectorBundle\Writer;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Event\EventInterface;
use Akeneo\Bundle\BatchBundle\Event\InvalidItemEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Helper to manage API Import writer errors
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * TODO : Move the addWarning method in BatchBundle
 */
class ErrorHelper
{
    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /**
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Sort, add a warning and dispatch an invalid item event for each error
     *
     * @param StepExecution $stepExecution
     * @param \SoapFault    $errors
     * @param array         $entities
     * @param string        $header
     * @param string        $writerClassName
     */
    public function manageErrors(
        StepExecution $stepExecution,
        \SoapFault $error,
        array $entities,
        $header,
        $writerClassName
    ) {
        $errors = json_decode($error->getMessage(), true);

        if (null !== $errors) {
            $indexedEntities = $this->getIndexedEntities($entities, $header);
            $failedEntities = $this->getSortedFailedEntities($errors, $indexedEntities);
            $this->manageFailedEntities($stepExecution, $failedEntities, $writerClassName);
        } else {
            $this->addWarning($stepExecution, $writerClassName, $error->getMessage());
        }
    }

    /**
     * Gives lines mapped to entity name or id
     * Each error returned by API Import is associate to the index of the line in the sent array.
     * This method provide a way to know to which entity is linked this index.
     * Returns ['line' => 'name/id', ...]
     *
     * @param array  $entities Entities to index
     * @param string $header   Header to index by
     *
     * @return array
     */
    protected function getIndexedEntities(array $entities, $header)
    {
        $indexedEntities = [];
        $previous        = '';
        foreach ($entities as $line => $entity) {
            if (!empty($entity[$header])) {
                $indexedEntities[$line] = $entity[$header];
                $previous               = $entity[$header];
            } else {
                $indexedEntities[$line] = $previous;
            }
        }

        return $indexedEntities;
    }

    /**
     * Get failed entities with their id or name associated to errors
     * Returns [id/name => ['error 1', 'error 2', ...]]
     *
     * @param array $errors          Error output from API Import must be ['error' => ['id1', 'id2', ...], ...]
     * @param array $indexedEntities Entities indexed ['line/index' => 'name/id', ...]
     *
     * @return array
     */
    protected function getSortedFailedEntities(array $errors, array $indexedEntities)
    {
        $failedEntities = [];
        foreach ($errors as $error => $failedRows) {
            foreach ($failedRows as $row) {
                $failedEntities[$indexedEntities[$row]][] = $error;
            }
        }

        return $failedEntities;
    }

    /**
     * Add a warning for each failed entity
     *
     * @param StepExecution $stepExecution
     * @param array         $failedEntities
     * @param string        $writerClassName
     */
    protected function manageFailedEntities(StepExecution $stepExecution, array $failedEntities, $writerClassName)
    {
        foreach ($failedEntities as $index => $errors) {
            foreach ($errors as $error) {
                $this->addWarning($stepExecution, $writerClassName, $error, [], [$index]);
            }
        }
    }

    /**
     * Add a warning based on the stepExecution.
     *
     * @param StepExecution $stepExecution
     * @param string        $writerClassName
     * @param string        $message
     * @param array         $messageParameters
     * @param array         $item
     */
    protected function addWarning(
        StepExecution $stepExecution,
        $writerClassName,
        $message,
        array $messageParameters = [],
        array $item = []
    ) {
        $stepExecution->addWarning(
            $writerClassName,
            $message,
            $messageParameters,
            $item
        );

        if (!is_array($item)) {
            $item = [];
        }

        $event = new InvalidItemEvent($writerClassName, $message, $messageParameters, $item);
        $this->eventDispatcher->dispatch(EventInterface::INVALID_ITEM, $event);
    }
}
