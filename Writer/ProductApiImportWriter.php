<?php
namespace Pim\Bundle\MagentoConnectorBundle\Writer;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Akeneo\Bundle\BatchBundle\Item\ItemWriterInterface;
use Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface;

/**
 * Writer for products to Api Import
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductApiImportWriter extends AbstractConfigurableStepElement implements
    ItemWriterInterface,
    StepExecutionAwareInterface
{
    /** @var StepExecution */
    protected $stepExecution;

    /**
     * Return an array of fields for the configuration form
     *
     * @return array:array
     *
     */
    public function getConfigurationFields()
    {
        return [];
    }

    /**
     * Process the supplied data element. Will not be called with any null items
     * in normal operation.
     *
     * @param array $items The list of items to write
     *
     * FIXME: array is not maybe the best structure to hold the items. Investigate this point.
     *
     * @throw InvalidItemException if there is a warning, step execution will continue to the
     * next item.
     * @throws \Exception if there are errors. The framework will catch the
     *                    exception and convert or rethrow it as appropriate.
     */
    public function write(array $items)
    {
        die('[STEP] WRITER');
    }

    /**
     * @param StepExecution $stepExecution
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }
}
