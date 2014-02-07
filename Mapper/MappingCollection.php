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

            $value['target']    = $value['target'] ? $value['target'] : $oldValue['target'];
            $value['deletable'] = $value['deletable'] === false ? $value['deletable'] : $oldValue['deletable'];
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
}
