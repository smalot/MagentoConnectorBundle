<?php

namespace Pim\Bundle\MagentoConnectorBundle\Manager;

use Pim\Bundle\CatalogBundle\Manager\GroupManager as BaseGroupManager;
use Pim\Bundle\MagentoConnectorBundle\Entity\Repository\GroupRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

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
     * @var string
     */
    protected $groupClass;

    /**
     * Constructor
     *
     * @param RegistryInterface $doctrine
     * @param string            $productClass
     * @param string            $attributeClass
     * @param string            $groupClass
     */
    public function __construct(RegistryInterface $doctrine, $productClass, $attributeClass, $groupClass)
    {
        parent::__construct($doctrine, $productClass, $attributeClass);

        $this->groupClass = $groupClass;
    }

    /**
     * Returns the entity repository
     *
     * @return \Doctrine\ORM\EntityRepository
     */
    public function getRepository()
    {
        $em = $this->doctrine->getEntityManager();
        $classMetadata = $em->getMetadataFactory()->getMetadataFor($this->groupClass);

        return new GroupRepository($em, $classMetadata);
    }
}
