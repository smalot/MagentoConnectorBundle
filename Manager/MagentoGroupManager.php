<?php

namespace Pim\Bundle\MagentoConnectorBundle\Manager;

use Pim\Bundle\MagentoConnectorBundle\Entity\MagentoGroup;
use Pim\Bundle\CatalogBundle\Manager\GroupManager as BaseGroupManager;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * magento group manager
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MagentoGroupManager extends BaseGroupManager
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
     * Get magento group from id and Magento url
     * @param integer $id
     * @param string  $magentoUrl
     *
     * @return MagentoGroup|null
     */
    public function getMagentoGroupFromId($id, $magentoUrl)
    {
        $magentoGroup = $this->getEntityRepository()->findOneBy(
            array(
                'magentoGroupId'  => $id,
                'magentoUrl'      => $magentoUrl
            )
        );

        return $magentoGroup ? $magentoGroup : null;
    }

    /**
     * Register a new magento group
     * @param int    $magentoGroupId
     * @param string $magentoUrl
     */
    public function registerMagentoGroup(
        $magentoGroupId,
        $magentoUrl
    ) {
        $magentoGroup = $this->getEntityRepository()->findOneBy(
            array(
                'magentoGroupId' => $magentoGroupId,
                'magentoUrl' => $magentoUrl
            )
        );

        if (!isset($magentoGroup)) {
            $magentoGroup = new MagentoGroup();
        }

        $magentoGroup->setMagentoGroupId($magentoGroupId);
        $magentoGroup->setMagentoUrl($magentoUrl);

        $this->objectManager->persist($magentoGroup);
        $this->objectManager->flush();
    }

    /**
     * Does the given attribute group exist in magento ?
     * @param string $familyId
     * @param string $magentoUrl
     *
     * @return boolean
     */
    public function magentoFamilyExists($familyId, $magentoUrl)
    {
        return null !== $this->getMagentoGroupFromId($familyId, $magentoUrl);
    }

    /**
     * Gives all the magento groups
     *
     * @return array
     */
    public function getAllMagentoGroups()
    {
        return $this->getEntityRepository()->findAll();
    }

    /**
     * remove the magento group from Akeneo db
     * @param int    $magentoGroupId
     * @param string $magentoUrl
     *
     * @return boolean
     */
    public function removeMagentoGroup($magentoGroupId, $magentoUrl)
    {
        $magentoGroup = $this->getEntityRepository()->findOneBy(
            array(
                'magentoGroupId' => $magentoGroupId,
                'magentoUrl' => $magentoUrl
            )
        );

        $this->objectManager->remove($magentoGroup);
        $this->objectManager->flush();
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
