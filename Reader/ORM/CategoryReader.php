<?php

namespace Pim\Bundle\MagentoConnectorBundle\Reader\ORM;

use Pim\Bundle\ImportExportBundle\Reader\ORM\EntityReader;
use Pim\Bundle\MagentoConnectorBundle\Entity\Repository\CategoryRepository;

/**
 * ORM reader for categories
 *
 * @author    Julien SAnchez <julien@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryReader extends EntityReader
{
    /**
     * {@inheritdoc}
     */
    protected function getQuery()
    {
        if (!$this->query) {
            $this->query = $this->getRepository()->findOrderedCategories()->getQuery();
        }

        return $this->query;
    }

    /**
     * Get the custom category repository
     * @return CategoryRepository
     */
    protected function getRepository()
    {
        $classMetadata = $this->em->getMetadataFactory()->getMetadataFor($this->className);

        return new CategoryRepository($this->em, $classMetadata);
    }
}
