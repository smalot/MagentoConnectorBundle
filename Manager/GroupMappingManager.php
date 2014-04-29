<?php

namespace Pim\Bundle\MagentoConnectorBundle\Manager;

use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;
use Pim\Bundle\CatalogBundle\Entity\Repository\GroupRepository;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Group mapping manager
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupMappingManager
{
    /**
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    protected $objectManager;

    /**
     * @var string
     */
    protected $className;

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
     * Get group from id and Magento url
     * @param integer $id
     * @param string  $magentoUrl
     *
     * @return AttributeGroup|null
     */
    public function getGroupFromId($id, $magentoUrl)
    {
        $magentoGroupMapping = $this->getEntityRepository()->findOneBy(
            array(
                'magentoGroupId' => $id,
                'magentoUrl'     => $magentoUrl
            )
        );

        return $magentoGroupMapping ? $magentoGroupMapping->getGroup() : null;
    }

    /**
     * Get id from group and Magento url
     * @param AttributeGroup  $group
     * @param string $magentoUrl
     *
     * @return integer
     */
    public function getIdFromGroup(AttributeGroup $group, $magentoUrl)
    {
        $groupMapping = $this->getEntityRepository()->findOneBy(
            array(
                'group'      => $group,
                'magentoUrl' => $magentoUrl
            )
        );

        return $groupMapping ? $groupMapping->getMagentoGroupId() : null;
    }

    /**
     * Register a new group mapping
     * @param AttributeGroup   $pimGroup
     * @param integer          $magentoGroupId
     * @param string           $magentoUrl
     */
    public function registerGroupMapping(
        AttributeGroup $pimGroup,
        $magentoGroupId,
        $magentoUrl
    ) {
        $groupMapping = $this->getEntityRepository()->findOneByGroup($pimGroup->getId());
        $magentoGroupMapping = new $this->className();

        if ($groupMapping) {
            $magentoGroupMapping = $groupMapping;
        }

        $magentoGroupMapping->setGroup($pimGroup);
        $magentoGroupMapping->setMagentoGroupId($magentoGroupId);
        $magentoGroupMapping->setMagentoUrl($magentoUrl);

        $this->objectManager->persist($magentoGroupMapping);
        $this->objectManager->flush();
    }

    /**
     * Does the given magento group exist in pim ?
     * @param string $groupId
     * @param string $magentoUrl
     *
     * @return boolean
     */
    public function magentoGroupExists($groupId, $magentoUrl)
    {
        return null !== $this->getGroupFromId($groupId, $magentoUrl);
    }

    /**
     * Get the entity manager
     * @return EntityRepository
     */
    protected function getEntityRepository()
    {
        return $this->objectManager->getRepository($this->className);
    }
}
