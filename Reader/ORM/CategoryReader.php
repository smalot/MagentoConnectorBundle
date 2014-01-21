<?php

namespace Pim\Bundle\MagentoConnectorBundle\Reader\ORM;

use Pim\Bundle\ImportExportBundle\Reader\ORM\EntityReader;
use Pim\Bundle\MagentoConnectorBundle\Entity\Repository\CategoryRepository;
use Doctrine\ORM\EntityManager;

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
     * @var CategoryRepository
     */
    protected $repository;

    /**
     * @param EntityManager      $em         The entity manager
     * @param string             $className  The entity class name used
     * @param CategoryRepository $repository The entity repository
     */
    public function __construct(EntityManager $em, $className, CategoryRepository $repository)
    {
        parent::__construct($em, $className);

        $this->repository = $repository;
    }

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
        return $this->repository;
    }
}
