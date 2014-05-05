<?php

namespace Pim\Bundle\MagentoConnectorBundle\Writer;

use Pim\Bundle\CatalogBundle\Entity\Attribute;
use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\MagentoConnectorBundle\Manager\AttributeMappingManager;
use Pim\Bundle\MagentoConnectorBundle\Manager\FamilyMappingManager;
use Pim\Bundle\MagentoConnectorBundle\Manager\AttributeGroupMappingManager;
use Akeneo\Bundle\BatchBundle\Item\InvalidItemException;
use Pim\Bundle\MagentoConnectorBundle\Manager\MagentoGroupManager;
use Pim\Bundle\MagentoConnectorBundle\Webservice\SoapCallException;
use Pim\Bundle\CatalogBundle\Entity\Family;
use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;

/**
 * Magento attribute writer. Add attributes to groups and attribute sets on magento side
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeWriter extends AbstractWriter
{
    const ATTRIBUTE_UPDATE_SIZE = 2;
    const ATTRIBUTE_UPDATED     = 'Attributes updated';
    const ATTRIBUTE_CREATED     = 'Attributes created';
    const ATTRIBUTE_EXISTS      = 'Attribute already in magento';
    const GROUP_EXISTS          = 'Group was already in attribute set on magento';

    /**
     * @var AttributeMappingManager
     */
    protected $attributeMappingManager;

    /**
     * @var Attribute
     */
    protected $attribute;

    /**
     * @var FamilyMappingManager
     */
    protected $familyMappingManager;

    /**
     * @var MagentoGroupManager
     */
    protected $magentoGroupManager;

    /**
     * Constructor
     *
     * @param WebserviceGuesser            $webserviceGuesser
     * @param FamilyMappingManager         $familyMappingManager
     * @param AttributeMappingManager      $attributeMappingManager
     * @param AttributeGroupMappingManager $attributeGroupMappingManager
     * @param MagentoGroupManager          $magentoGroupManager
     */
    public function __construct(
        WebserviceGuesser            $webserviceGuesser,
        FamilyMappingManager         $familyMappingManager,
        AttributeMappingManager      $attributeMappingManager,
        AttributeGroupMappingManager $attributeGroupMappingManager,
        MagentoGroupManager          $magentoGroupManager
    ) {
        parent::__construct($webserviceGuesser);

        $this->attributeMappingManager = $attributeMappingManager;
        $this->familyMappingManager    = $familyMappingManager;
        $this->groupMappingManager     = $attributeGroupMappingManager;
        $this->magentoGroupManager     = $magentoGroupManager;
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $attributes)
    {
        $this->beforeExecute();

        foreach ($attributes as $attribute) {
            try {
                $this->attribute = $attribute[0];
                $this->addGroupToAttributeSet();
                $this->handleAttribute($attribute[1]);
            } catch (SoapCallException $e) {
                $this->stepExecution->incrementSummaryInfo(self::ATTRIBUTE_EXISTS);
            }
        }
    }

    /**
     * Handle attribute creation and update
     * @param array $attribute
     * @throws InvalidItemException
     */
    protected function handleAttribute(array $attribute)
    {
        if (count($attribute) === self::ATTRIBUTE_UPDATE_SIZE) {
            $this->webservice->updateAttribute($attribute);
            $magentoAttributeId = $this->attributeMappingManager
                ->getIdFromAttribute($this->attribute, $this->getSoapUrl());
            $magentoGroupId = $this->getGroupId();
            if (!empty($magentoGroupId)) {
                $this->magentoGroupManager->registerMagentoGroup($magentoGroupId, $this->getSoapUrl());
            }
            $this->addAttributeToAttributeSet($magentoAttributeId, $magentoGroupId);
            $this->stepExecution->incrementSummaryInfo(self::ATTRIBUTE_UPDATED);
        } else {
            $magentoAttributeId = $this->webservice->createAttribute($attribute);
            $magentoGroupId = $this->getGroupId($attribute);
            $this->addAttributeToAttributeSet($magentoAttributeId, $magentoGroupId);
            $this->stepExecution->incrementSummaryInfo(self::ATTRIBUTE_CREATED);
            $magentoUrl = $this->getSoapUrl();

            $this->attributeMappingManager->registerAttributeMapping(
                $this->attribute,
                $magentoAttributeId,
                $magentoUrl
            );
        }
    }

    /**
     * Get the magento group id
     *
     * @return int|null
     */
    protected function getGroupId()
    {
        $group = $this->attribute->getGroup();
        if ($group !== null) {
            $magentoGroupId = $this->groupMappingManager->getIdFromGroup($group, $this->getSoapUrl());
        } else {
            $magentoGroupId = null;
        }

        return $magentoGroupId;
    }

    /**
     * Add attribute to corresponding attribute sets
     * @param integer $magentoAttributeId ID of magento attribute
     * @param integer $groupId
     *
     * @return void
     */
    protected function addAttributeToAttributeSet($magentoAttributeId, $groupId)
    {
        $families = $this->attribute->getFamilies();
        foreach ($families as $family) {
            $familyMagentoId = $this->familyMappingManager->getIdFromFamily($family, $this->getSoapUrl());
            try {
                $this->webservice->addAttributeToAttributeSet($magentoAttributeId, $familyMagentoId, $groupId);
            } catch (SoapCallException $e) {
                if (strpos($e->getMessage(), 'already') !== false) {
                    $this->stepExecution->incrementSummaryInfo(self::ATTRIBUTE_EXISTS);
                }
            }
        }
    }

    /**
     * Create a group in an attribute set
     *
     * @return void
     */
    protected function addGroupToAttributeSet()
    {
        $families = $this->attribute->getFamilies();

        $group = $this->attribute->getGroup();
        if (isset($group)) {
            $groupName = $group->getCode();

            foreach ($families as $family) {
                $familyMagentoId = $this->familyMappingManager->getIdFromFamily($family, $this->getSoapUrl());

                try {
                    $magentoGroupId = $this->webservice->addAttributeGroupToAttributeSet($familyMagentoId, $groupName);
                    $this->groupMappingManager->registerGroupMapping(
                        $group,
                        $magentoGroupId,
                        $this->getSoapUrl()
                    );
                    $this->magentoGroupManager->registerMagentoGroup($magentoGroupId, $this->getSoapUrl());
                } catch (SoapCallException $e) {
                    $this->stepExecution->incrementSummaryInfo(self::GROUP_EXISTS);
                }

            }
        }
    }
}
