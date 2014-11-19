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

    /** @var array $sentVariantGroups Buffer to keep variant groups which are already sent */
    protected $sentVariantGroups;

    /** @var \ArrayIterator $variantGroupsToSend Buffer to keep variant groups to send */
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
     * This reader send variant groups one by one to prevent to keep all of them in memory.
     * It use a double buffer to know which variant groups have been already send and which are ones to send.
     *
     * {@inheritdoc}
     */
    public function read()
    {
        $variantGroup = null;

        if (!$this->variantGroupsToSend->valid()) {
            $this->variantGroupsToSend = new \ArrayIterator();

            $nextGroups = $this->getNextVariantGroupsToSend($this->sentVariantGroups);
            if (null !== $nextGroups) {
                $this->variantGroupsToSend = $nextGroups;
                $this->variantGroupsToSend->rewind();
            } else {
                $this->variantGroupsToSend = null;
            }
        }

        if (null !== $this->variantGroupsToSend) {
            $variantGroup = $this->variantGroupsToSend->current();
            $this->sentVariantGroups[] = $variantGroup->getId();

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
     * {@inheritdoc}
     */
    public function initialize()
    {
        $this->productReader->initialize();
        $this->variantGroupsToSend = new \ArrayIterator();
        $this->sentVariantGroups   = [];
    }

    /**
     * Get the next bunch of variant groups
     * If there is no more variant groups to send, we continue to verify in
     * next products if there are new variant groups.
     * At the moment we have found new variant groups, we send them to avoid to keep all of them in memory.
     *
     * @param array $sentVariantGroups
     *
     * @return null|\ArrayIterator
     */
    protected function getNextVariantGroupsToSend(array $sentVariantGroups = [])
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
                    if (!in_array($variantGroup->getId(), $sentVariantGroups)) {
                        $nextVariantGroups->append($variantGroup);
                    }
                }
            }
        }

        return 0 === $nextVariantGroups->count() ? null : $nextVariantGroups;
    }
}
