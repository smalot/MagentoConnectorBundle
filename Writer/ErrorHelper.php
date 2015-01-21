<?php

namespace Pim\Bundle\MagentoConnectorBundle\Writer;

/**
 * Helper to manage API Import writer errors
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ErrorHelper
{
    /**
     * Gives lines mapped to entity name or id
     * Each error returned by API Import is associate to the index of the line in the sent array.
     * This method provide a way to know to which entity is linked this index.
     * Returns ['line' => 'name/id', ...]
     *
     * @param array  $entities   Entities to index
     * @param string $entityType Entity type to index
     *
     * @return array
     */
    public function getIndexedEntities(array $entities, $indexKey)
    {
        $indexedEntities = [];
        $previous        = '';
        foreach ($entities as $line => $entity) {
            if (!empty($entity[$indexKey])) {
                $indexedEntities[$line] = $entity[$indexKey];
                $previous               = $entity[$indexKey];
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
    public function getSortedFailedEntities(array $errors, array $indexedEntities)
    {
        $failedEntities = [];
        foreach ($errors as $error => $failedRows) {
            foreach ($failedRows as $row) {
                $failedEntities[$indexedEntities[$row]][] = $error;
            }
        }

        return $failedEntities;
    }
}
