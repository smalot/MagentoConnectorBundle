<?php

namespace Pim\Bundle\MagentoConnectorBundle\Manager;

use Pim\Bundle\CatalogBundle\Entity\Family;
use Pim\Bundle\CatalogBundle\Manager\FamilyManager as BaseFamilyManager;
use Pim\Bundle\CatalogBundle\Entity\Repository\FamilyRepository;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Custom family manager
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyManager extends BaseFamilyManager
{

    /**
     * Constructor
     * @param ObjectManager $objectManager
     * @param string        $className
    */
    public function __construct(ObjectManager $objectManager, $className)
    {
        $this->objectManager = $objectManager;
        $this->className     = $className;
    }

    /**
     * Get families
     * @param array $criteria
     *
     * @return array
     */
    public function getFamilies(array $criteria = array())
    {
        return $this->getRepository()->findBy($criteria);
    }

    /**
     * Returns the entity repository
     *
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getRepository()
    {
        $classMetadata = $this->objectManager->getMetadataFactory()->getMetadataFor($this->className);

        return new FamilyRepository($this->objectManager, $classMetadata);
    }
}
