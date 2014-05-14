<?php

namespace Pim\Bundle\MagentoConnectorBundle\Cleaner;

use Pim\Bundle\MagentoConnectorBundle\Entity\MagentoGroupMapping;
use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\MagentoConnectorBundle\Manager\AttributeGroupMappingManager;
use Pim\Bundle\MagentoConnectorBundle\Manager\AttributeMappingManager;
use Pim\Bundle\MagentoConnectorBundle\Manager\FamilyMappingManager;
use Pim\Bundle\MagentoConnectorBundle\Validator\Constraints\HasValidCredentials;

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
     * @var AttributeMappingManager
     */
    protected $attributeMappingManager;

    /**
     * @var FamilyMappingManager
     */
    protected $familyMappingManager;

    /**
     * @param WebserviceGuesser            $webserviceGuesser
     * @param AttributeGroupMappingManager $attributeGroupMappingManager
     * @param EntityManager                $entityManager
     * @param $attributeGroupClass
     * @param AttributeMappingManager      $attributeMappingManager
     * @param FamilyMappingManager         $familyMappingManager
     */
    public function __construct(
        WebserviceGuesser            $webserviceGuesser,
        AttributeGroupMappingManager $attributeGroupMappingManager,
        EntityManager                $entityManager,
        $attributeGroupClass,
        AttributeMappingManager      $attributeMappingManager,
        FamilyMappingManager         $familyMappingManager
    ) {
        parent::__construct($webserviceGuesser);

        $this->attributeGroupMappingManager = $attributeGroupMappingManager;
        $this->entityManager                = $entityManager;
        $this->attributeGroupClass          = $attributeGroupClass;
        $this->attributeMappingManager      = $attributeMappingManager;
        $this->familyMappingManager         = $familyMappingManager;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        parent::beforeExecute();

        $mappingAttributeGroups = $this->attributeGroupMappingManager->getAllMappings();

        foreach ($mappingAttributeGroups as $groupMapping) {
            $attributeGroup = $this->entityManager->getRepository($this->attributeGroupClass)
                ->findOneBy(array('code' => $groupMapping->getPimGroupCode()));

            if ($attributeGroup == null) {
                $this->handleGroupNotInPimAnymore($groupMapping);
            }
        }
    }

    /**
     * Handle deletion of groups that are not in PIM anymore
     *
     * @param MagentoGroupMapping $groupMapping
     *
     * @throws \Exception
     */
    protected function handleGroupNotInPimAnymore(MagentoGroupMapping $groupMapping)
    {
        if (!in_array($groupMapping->getPimGroupCode(), $this->getIgnoredCleaners())) {
            try {
                $attributeGroup = $this->entityManager->getRepository($this->attributeGroupClass)
                    ->findOneByCode($groupMapping->getPimGroupCode());

                var_dump($groupMapping->getPimGroupCode());
                $attributes = $attributeGroup->getAttributes();

                $magentoFamilyId = $this->entityManager->createQuery(
                    'SELECT PimMagentoConnectorBundle:MagentoFamilyMapping m
                     JOIN PimCatalogBundle:Family f
                     WHERE f.code = :code'
                )->setParameter('code', $groupMapping->getPimGroupCode());

                foreach ($attributes as $attribute) {
                    $magentoAttributeId = $this->attributeMappingManager
                        ->getIdFromAttribute($attribute, $this->getSoapUrl());

                    var_dump($this->webservice->removeAttributeFromAttributeSet($magentoAttributeId, $magentoFamilyId));
                }

                $this->webservice->removeAttributeGroupFromAttributeSet($groupMapping->getMagentoGroupId());
                $this->$attributeGroupMappingManager->removeMapping($groupMapping);
                $this->stepExecution->incrementSummaryInfo(self::GROUP_DELETED);
            } catch (\Exception $e) {
                throw new \Exception($e);
            }
        }
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
