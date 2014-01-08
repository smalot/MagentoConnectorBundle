<?php

namespace Pim\Bundle\MagentoConnectorBundle\Manager;

use Pim\Bundle\CatalogBundle\Manager\GroupManager as BaseGroupManager;
use Pim\Bundle\MagentoConnectorBundle\Repository\GroupRepository;

/**
 * Custom group manager
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupManager extends BaseGroupManager
{
    /**
     * Returns the entity repository
     *
     * @return \Doctrine\ORM\EntityRepository
     */
    public function getRepository()
    {
        $em = $this->doctrine->getEntityManager();
        $classMetadata = $em->getMetadataFactory()->getMetadataFor('PimCatalogBundle:Group');

        return new GroupRepository($em, $classMetadata);
    }
}
