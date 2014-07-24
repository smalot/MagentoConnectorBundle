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
class GroupManager
{
    /** @var string */
    protected $groupClass;

    /** @var RegistryInterface */
    protected $doctrine;

    /** @var BaseGroupManager $baseGroupManager */
    protected $baseGroupManager;

    /**
     * Constructor
     *
     * @param BaseGroupManager  $baseGroupManager
     * @param RegistryInterface $doctrine
     * @param string            $groupClass
     */
    public function __construct(
        BaseGroupManager $baseGroupManager,
        RegistryInterface $doctrine,
        $groupClass
    ) {
        $this->baseGroupManager = $baseGroupManager;
        $this->doctrine         = $doctrine;
        $this->groupClass       = $groupClass;
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

    /**
     * Get axis as choice list
     *
     * @return array
     */
    public function getAvailableAxisChoices()
    {
        return $this->baseGroupManager->getAvailableAxisChoices();
    }

    /**
     * Get choices
     *
     * @return array
     */
    public function getChoices()
    {
        return $this->baseGroupManager->getChoices();
    }

    /**
     * Get axis as choice list
     *
     * @param boolean $isVariant
     *
     * @return array
     */
    public function getTypeChoices($isVariant)
    {
        return $this->baseGroupManager->getTypeChoices($isVariant);
    }

    /**
     * Returns the group type repository
     *
     * @return \Doctrine\ORM\EntityRepository
     */
    public function getGroupTypeRepository()
    {
        return $this->doctrine->getRepository($this->groupTypeClass);
    }

    /**
     * Removes a group
     *
     * @param Group $group
     */
    public function remove(Group $group)
    {
        $this->baseGroupManager->remove($group);
    }

    /**
     * Returns an array containing a limited number of product groups, and the total number of products
     *
     * @param Group   $group
     * @param integer $maxResults
     *
     * @return array
     */
    public function getProductList(Group $group, $maxResults)
    {
        return $this->baseGroupManager->getProductList($group, $maxResults);
    }
}
