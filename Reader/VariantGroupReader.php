<?php

namespace Pim\Bundle\MagentoConnectorBundle\Reader;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Pim\Bundle\BaseConnectorBundle\Reader\ProductReaderInterface;

/**
 * Variant group reader
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VariantGroupReader extends AbstractConfigurableStepElement implements ProductReaderInterface
{
    /** @var ProductReaderInterface */
    protected $productReader;

    /** @var array */
    protected $sentVariantGroups;

    /** @var \ArrayIterator */
    protected $variantGroupsToSend;

    /**
     * @param ProductReaderInterface $productReader
     */
    public function __construct(ProductReaderInterface $productReader)
    {
        $this->productReader       = $productReader;
        $this->variantGroupsToSend = new \ArrayIterator();
        $this->sentVariantGroups   = [];
    }

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        $variantGroup = null;

        if (!$this->variantGroupsToSend->valid()) {
            $this->variantGroupsToSend = new \ArrayIterator();

            $nextGroups = $this->getNextVariantGroups();
            if (null !== $nextGroups) {
                while ($nextGroups->valid()) {
                    if (!in_array($nextGroups->current()->getId(), $this->sentVariantGroups)) {
                        $this->variantGroupsToSend->append($nextGroups->current());
                    }

                    $nextGroups->next();
                }
            } else {
                $this->variantGroupsToSend = null;
            }
        }

        if (null !== $this->variantGroupsToSend) {
            $variantGroup = $this->variantGroupsToSend->current();
            $this->sentVariantGroups[] = $this->variantGroupsToSend->current()->getId();

            $this->variantGroupsToSend->next();
        }

        return $variantGroup;
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
     * Get the next bunch of variant groups
     *
     * @return null|\ArrayIterator
     */
    protected function getNextVariantGroups()
    {
        $nextVariantGroups = new \ArrayIterator();

        while (0 === $nextVariantGroups->count() && $product = $this->productReader->read()) {
            $variantGroups = [];

            foreach ($product->getGroups() as $group) {
                if ($group->getType()->isVariant()) {
                    $variantGroups[] = $group;
                }
            }

            if (!empty($variantGroups)) {
                foreach ($variantGroups as $variantGroup) {
                    if (!in_array($variantGroup->getId(), $this->sentVariantGroups)) {
                        $nextVariantGroups->append($variantGroup);
                    }
                }
            }
        }

        return 0 === $nextVariantGroups->count() ? null : $nextVariantGroups;
    }
}
