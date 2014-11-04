<?php

namespace Pim\Bundle\MagentoConnectorBundle\Reader;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Pim\Bundle\BaseConnectorBundle\Reader\ProductReaderInterface;

/**
 * Product association reader
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductAssociationReader extends AbstractConfigurableStepElement implements ProductReaderInterface
{
    /** @var ProductReaderInterface */
    protected $productReader;

    /** @var \ArrayIterator */
    protected $associationsToSend;

    /**
     * @param ProductReaderInterface $productReader
     */
    public function __construct(ProductReaderInterface $productReader)
    {
        $this->productReader      = $productReader;
        $this->associationsToSend = new \ArrayIterator();
    }

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        $association = null;

        if (!$this->associationsToSend->valid()) {
            $this->associationsToSend = new \ArrayIterator();

            $nextAssociations = $this->getNextAssociationsToSend();
            if (null !== $nextAssociations) {
                $this->associationsToSend = $nextAssociations;
                $this->associationsToSend->rewind();
            } else {
                $this->associationsToSend = null;
            }
        }

        if (null !== $this->associationsToSend) {
            $association = $this->associationsToSend->current();

            $this->associationsToSend->next();
        }

        return $association;
    }

    /**
     * Get the step element configuration (based on getters)
     *
     * @return array
     */
    public function getConfiguration()
    {
        $result = [];
        foreach (array_keys($this->getConfigurationFields()) as $field) {
            $getField = 'get' . ucfirst($field);
            $result[$field] = $this->$getField();
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return $this->productReader->getConfigurationFields();
    }

    /**
     * {@inheritdoc}
     */
    public function setChannel($channel)
    {
        $this->productReader->setChannel($channel);
    }

    /**
     * {@inheritdoc}
     */
    public function getChannel()
    {
        return $this->productReader->getChannel();
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->productReader->setStepExecution($stepExecution);
    }

    /**
     * {@inheritdoc}
     */
    public function initialize()
    {
        $this->productReader->initialize();
        $this->variantGroupsToSend = new \ArrayIterator();
        $this->sentVariantGroups   = [];
    }

    /**
     * Get the next bunch of associations
     *
     * @return null|\ArrayIterator
     */
    protected function getNextAssociationsToSend()
    {
        $nextAssociations = new \ArrayIterator();

        while (0 === $nextAssociations->count() && $product = $this->productReader->read()) {
            foreach ($product->getAssociations() as $association) {
                if (!$association->getProducts()->isEmpty()) {
                    $nextAssociations->append($association);
                }
            }
        }

        return 0 === $nextAssociations->count() ? null : $nextAssociations;
    }
}
