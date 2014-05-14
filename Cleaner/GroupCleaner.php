<?php

namespace Pim\Bundle\MagentoConnectorBundle\Cleaner;

use Akeneo\Bundle\BatchBundle\Item\InvalidItemException;
use Pim\Bundle\MagentoConnectorBundle\Entity\MagentoGroupMapping;
use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\MagentoConnectorBundle\Manager\AttributeGroupMappingManager;
use Pim\Bundle\MagentoConnectorBundle\Manager\MagentoGroupManager;
use Pim\Bundle\MagentoConnectorBundle\Validator\Constraints\HasValidCredentials;
use Pim\Bundle\MagentoConnectorBundle\Webservice\SoapCallException;
use Doctrine\ORM\EntityManager;

/**
 * Magento group cleaner
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @HasValidCredentials()
 */
class GroupCleaner extends Cleaner
{
    const GROUP_DELETED = 'Group deleted';

    /**
     * @var AttributeGroupMappingManager
     */
    protected $attributeGroupMappingManager;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @param WebserviceGuesser            $webserviceGuesser
     * @param AttributeGroupMappingManager $attributeGroupMappingManager
     * @param EntityManager                $entityManager
     */
    public function __construct(
        WebserviceGuesser            $webserviceGuesser,
        AttributeGroupMappingManager $attributeGroupMappingManager,
        EntityManager                $entityManager,
        $attributeGroupClass,
        $magentoGroupMappingClass
    ) {
        parent::__construct($webserviceGuesser);

        $this->attributeGroupMappingManager = $attributeGroupMappingManager;
        $this->entityManager                = $entityManager;
        $this->attributeGroupClass          = $attributeGroupClass;
        $this->magentoGroupMappingClass     = $magentoGroupMappingClass;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        parent::beforeExecute();

        // $attributeGroupRepository = $this->entityManager->getRepository($this->attributeGroupClass);
        // $attributeGroupMappingRepository = $this->entityManager->getRepository($this->magentoGroupMappingClass);

        // $attributeGroupMappings = $attributeGroupMappingRepository->findAll();

        // foreach ($attributeGroupMappings as $attributeGroupMapping) {
        //     $attributeGroupRepository->findOneByCode($attributeGroupMapping->getPimGroupCode())

        //     $attributeGroupMapping = $attributeGroupMappingRepository->findBy(array(
        //         'pimGroupCode' => $attributeGroup->getCode()
        //     ));

        // }

        // $magentoGroupMappings = $this->attributeGroupMappingManager->getAllGroups();

        // foreach ($magentoGroupMappings as $groupMapping) {
        //     try {
        //         $this->handleGroupNotInPimAnymore($groupMapping);
        //     } catch (SoapCallException $e) {
        //         throw new InvalidItemException($e->getMessage(), array(json_encode($groupMapping)));
        //     }
        // }
    }

    /**
     * Handle deletion of groups that are not in PIM anymore
     * @param int $groupId
     */
    protected function handleGroupNotInPimAnymore(MagentoGroupMapping $groupMapping)
    {
        // if (!$this->attributeGroupMappingManager->magentoGroupExists($groupId, $this->getSoapUrl())
        //     //&& !in_array($groupName, $this->getIgnoredCleaners())
        // ) {
        //     $this->webservice->removeAttributeGroupFromAttributeSet($groupId);
        //     $this->magentoGroupManager->removeMagentoGroup($groupId, $this->getSoapUrl());
        //     $this->stepExecution->incrementSummaryInfo(self::GROUP_DELETED);
        // }
    }

    /**
     * Get all ignored cleaners
     *
     * @return array
     */
    protected function getIgnoredCleaners()
    {
        return array(
            'Default',
        );
    }
}
