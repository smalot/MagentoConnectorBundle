<?php

namespace Pim\Bundle\MagentoConnectorBundle\Mapper;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Mapping collection
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MappingCollection extends ArrayCollection
{
    /**
     * {@inheritDoc}
     */
    public function add($value)
    {
        if ($this->containsKey($value['source'])) {
	    $oldValue = $this->get($value['source']);
            $value['target'] = $value['target'] ? $value['target'] : $oldValue['target'];
	    $value['deletable'] = $value['deletable'] === false ? $value['deletable'] : $oldValue['deletable'];
        } else {
            $iterator     = $this->getIterator();
            $elementFound = false;

            while ($iterator->valid() && !$elementFound) {
                if ($iterator->current()['target'] == $value['target'] &&
                    (!$value['deletable'] || !$oldValue['deletable'])
                ) {
                    $this->remove($iterator->current()['source']);
                    $elementFound = true;
                }

                $iterator->next();
            }
        }

        $this->set($value['source'], $value);

        return true;
    }

    /**
     * Merge the given mapping collection to the current one
     * @param MappingCollection $collectionToMerge
     *
     * @return MappingCollection
     */
    public function merge(MappingCollection $collectionToMerge)
    {
        foreach ($collectionToMerge as $mapping) {
            $this->add($mapping);
        }

        return $this;
    }

    /**
     * Get source for the given target
     * @param string  $target
     * @param boolean $check
     *
     * @return string|null
     */
    public function getSource($target, $check = false)
    {
        $it = $this->getIterator();

        while ($it->valid()) {
            if ($it->current()['target'] == $target) {
                return $it->key();
            }

            $it->next();
        }

        if ($check || $this->getTarget($target, true) == $target) {
            return $target;
        } else {
            return null;
        }
    }

    /**
     * Get target for the given source
     * @param string  $source
     * @param boolean $check
     *
     * @return string|null
     */
    public function getTarget($source, $check = false)
    {
        $target = $this->get($source);

        if ($target) {
            return $target['target'];
        } elseif ($check || $this->getSource($source, true) == $source) {
            return $source;
        } else {
            return null;
        }
    }
}
