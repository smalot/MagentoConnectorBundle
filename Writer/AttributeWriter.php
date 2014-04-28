<?php

namespace Pim\Bundle\MagentoConnectorBundle\Writer;

use Pim\Bundle\CatalogBundle\Entity\Attribute;
use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\MagentoConnectorBundle\Manager\AttributeMappingManager;
use Pim\Bundle\MagentoConnectorBundle\Manager\FamilyMappingManager;
use Pim\Bundle\MagentoConnectorBundle\Manager\GroupMappingManager;
use Akeneo\Bundle\BatchBundle\Item\InvalidItemException;
use Pim\Bundle\MagentoConnectorBundle\Webservice\SoapCallException;
use Pim\Bundle\CatalogBundle\Entity\Family;
use Pim\Bundle\CatalogBundle\Entity\Group;

/**
 * Magento attribute writer
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
    const ATTRIBUTE_ALREADY     = 'Attribute already in magento';
    const GROUP_ALREADY         = 'Group was already in attribute set on magento';

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
     * Constructor
     *
     * @param WebserviceGuesser       $webserviceGuesser
     * @param FamilyMappingManager    $familyMappingManager
     * @param AttributeMappingManager $attributeMappingManager
     * @param GroupMappingManager     $groupMappingManager
     */
    public function __construct(
        WebserviceGuesser $webserviceGuesser,
        FamilyMappingManager $familyMappingManager,
        AttributeMappingManager $attributeMappingManager,
        GroupMappingManager $groupMappingManager
    ) {
        parent::__construct($webserviceGuesser);

        $this->attributeMappingManager = $attributeMappingManager;
        $this->familyMappingManager    = $familyMappingManager;
        $this->groupMappingManager     = $groupMappingManager;
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
                throw new InvalidItemException($e->getMessage(), array(json_encode($attribute)));
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
            $group = $this->attribute->getGroup();
            if ($group !== null) {
                $magentoGroupId = $this->groupMappingManager->getIdFromGroup($group, $this->getSoapUrl());
            } else {
                $magentoGroupId = null;
            }
            $this->addAttributeToAttributeSet($magentoAttributeId, $magentoGroupId);
            $this->stepExecution->incrementSummaryInfo(self::ATTRIBUTE_UPDATED);
        } else {
            $magentoAttributeId = $this->webservice->createAttribute($attribute);
            $group = $this->attribute->getGroup();
            if ($group !== null) {
                $magentoGroupId = $this->groupMappingManager->getIdFromGroup($group, $this->getSoapUrl());
            } else {
                $magentoGroupId = null;
            }
            $this->addAttributeToAttributeSet($magentoAttributeId, $magentoGroupId);
            $this->stepExecution->incrementSummaryInfo(self::ATTRIBUTE_CREATED);
            $magentoUrl = $this->soapUrl;

            $this->attributeMappingManager->registerAttributeMapping(
                $this->attribute,
                $magentoAttributeId,
                $magentoUrl
            );
        }
    }

    /**
     * Add attribute to corresponding attribute sets
     * @param int $magentoAttributeId ID of magento attribute
     *
     * @return void
     */
    protected function addAttributeToAttributeSet($magentoAttributeId)
    {
        $families = $this->attribute->getFamilies();
        foreach ($families as $family) {
            $familyMagentoId = $this->familyMappingManager->getIdFromFamily($family, $this->soapUrl);
            try {
                $this->webservice->addAttributeToAttributeSet($magentoAttributeId, $familyMagentoId);
            } catch (SoapCallException $e) {
                $this->stepExecution->incrementSummaryInfo(self::ATTRIBUTE_ALREADY);
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
                $familyMagentoId = $this->familyMappingManager->getIdFromFamily($family, $this->soapUrl);
                try {
                    $magentoGroupId = $this->webservice->addAttributeGroupToAttributeSet($familyMagentoId, $groupName);
                    $this->groupMappingManager->registerGroupMapping(
                        $group,
                        $magentoGroupId,
                        $this->soapUrl
                    );
                } catch (SoapCallException $e) {
                    $this->stepExecution->incrementSummaryInfo(self::GROUP_ALREADY);
                }
            }
        }
    }

    /**
     * Create a group in an attribute set
     *
     * @return void
     */
    protected function addAttributeToGroup()
    {
        $families = $this->attribute->getFamilies();
        $group = $this->attribute->getGroup();
        if (isset($group)) {
            $groupName = $group->getCode();
            var_dump($groupName);
            foreach ($families as $family) {
                $familyMagentoId = $this->familyMappingManager->getIdFromFamily($family, $this->soapUrl);
                try {
                    $magentoGroupId = $this->webservice->addAttributeGroupToAttributeSet($familyMagentoId, $groupName);
                    $this->groupMappingManager->registerGroupMapping(
                        $group,
                        $magentoGroupId,
                        $this->soapUrl
                    );
                } catch (SoapCallException $e) {
                    $this->stepExecution->incrementSummaryInfo(self::GROUP_ALREADY);
                }
            }
        }
    }
}
