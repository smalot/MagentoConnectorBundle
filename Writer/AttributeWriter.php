<?php

namespace Pim\Bundle\MagentoConnectorBundle\Writer;

use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\MagentoConnectorBundle\Manager\AttributeMappingManager;
use Pim\Bundle\MagentoConnectorBundle\Manager\FamilyMappingManager;
use Pim\Bundle\MagentoConnectorBundle\Manager\AttributeGroupMappingManager;
use Akeneo\Bundle\BatchBundle\Item\InvalidItemException;
use Pim\Bundle\MagentoConnectorBundle\Merger\MagentoMappingMerger;
use Pim\Bundle\MagentoConnectorBundle\Webservice\SoapCallException;
use Pim\Bundle\CatalogBundle\Entity\Family;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientParametersRegistry;

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

    /**
     * @var AttributeMappingManager
     */
    protected $attributeMappingManager;

    /**
     * @var AbstractAttribute
     */
    protected $attribute;

    /**
     * @var FamilyMappingManager
     */
    protected $familyMappingManager;

    /**
     * @var AttributeGroupMappingManager
     */
    protected $attributeGroupMappingManager;

    /**
     * Constructor
     *
     * @param WebserviceGuesser                   $webserviceGuesser
     * @param FamilyMappingManager                $familyMappingManager
     * @param AttributeMappingManager             $attributeMappingManager
     * @param AttributeGroupMappingManager        $attributeGroupMappingManager
     * @param MagentoSoapClientParametersRegistry $clientParametersRegistry
     */
    public function __construct(
        WebserviceGuesser $webserviceGuesser,
        FamilyMappingManager $familyMappingManager,
        AttributeMappingManager $attributeMappingManager,
        AttributeGroupMappingManager $attributeGroupMappingManager,
        MagentoSoapClientParametersRegistry $clientParametersRegistry
    ) {
        parent::__construct($webserviceGuesser, $clientParametersRegistry);

        $this->attributeMappingManager      = $attributeMappingManager;
        $this->familyMappingManager         = $familyMappingManager;
        $this->attributeGroupMappingManager = $attributeGroupMappingManager;

    }

    /**
     * {@inheritdoc}
     */
    public function write(array $attributes)
    {
        $this->beforeExecute();

        foreach ($attributes as $attribute) {
            try {
                $pimAttribute = $attribute[0];
                $this->addGroupToAttributeSet($pimAttribute);
                $this->handleAttribute($attribute[1], $pimAttribute);
            } catch (SoapCallException $e) {
                throw new InvalidItemException($e->getMessage(), [$pimAttribute]);
            }
        }
    }

    /**
     * Handle attribute creation and update
     *
     * @param array             $attribute
     * @param AbstractAttribute $pimAttribute
     *
     * @throws InvalidItemException
     */
    protected function handleAttribute(array $attribute, $pimAttribute)
    {
        $magentoUrl = $this->getSoapUrl();

        if (count($attribute) === self::ATTRIBUTE_UPDATE_SIZE) {
            $this->webservice->updateAttribute($attribute);
            $magentoAttributeId = $this->attributeMappingManager->getIdFromAttribute($pimAttribute, $magentoUrl);
            $this->manageAttributeSet($magentoAttributeId, $pimAttribute);

            $this->stepExecution->incrementSummaryInfo(self::ATTRIBUTE_UPDATED);
        } else {
            $magentoAttributeId = $this->webservice->createAttribute($attribute);

            $this->manageAttributeSet($magentoAttributeId, $pimAttribute);

            $this->stepExecution->incrementSummaryInfo(self::ATTRIBUTE_CREATED);

            $this->attributeMappingManager->registerAttributeMapping(
                $pimAttribute,
                $magentoAttributeId,
                $magentoUrl
            );
        }
    }

    /**
     * Verify if the magento attribute id is null else add the attribute to the attribute set
     *
     * @param integer $magentoAttributeId
     * @param array   $pimAttribute
     */
    protected function manageAttributeSet($magentoAttributeId, $pimAttribute)
    {
        if (null !== $magentoAttributeId) {
            $this->addAttributeToAttributeSet($magentoAttributeId, $pimAttribute);
        }
    }

    /**
     * Get the magento group id
     *
     * @param AbstractAttribute $pimAttribute
     * @param Family            $pimFamily
     *
     * @return int|null
     */
    protected function getGroupId(AbstractAttribute $pimAttribute, Family $pimFamily)
    {
        $pimGroup = $pimAttribute->getGroup();

        if ($pimGroup !== null) {
            $magentoGroupId = $this->attributeGroupMappingManager
                ->getIdFromGroup($pimGroup, $pimFamily, $this->getSoapUrl());
        } else {
            $magentoGroupId = null;
        }

        return $magentoGroupId;
    }

    /**
     * Add attribute to corresponding attribute sets
     *
     * @param integer $magentoAttributeId ID of magento attribute
     * @param         $pimAttribute
     *
     * @throws \Exception
     * @throws \SoapCallException
     *
     * @return void
     */
    protected function addAttributeToAttributeSet($magentoAttributeId, $pimAttribute)
    {
        $families = $pimAttribute->getFamilies();

        foreach ($families as $family) {
            $magentoGroupId  = $this->getGroupId($pimAttribute, $family);
            $magentoSetId = $this->familyMappingManager->getIdFromFamily($family, $this->getSoapUrl());
            try {
                if (null !== $magentoGroupId && null !== $magentoSetId) {
                    $this->webservice->addAttributeToAttributeSet(
                        $magentoAttributeId,
                        $magentoSetId,
                        $magentoGroupId
                    );
                }
            } catch (SoapCallException $e) {
                if (!strpos($e->getMessage(), 'already') !== false) {
                    throw $e;
                }
            }
        }
    }

    /**
     * Create a group in an attribute set
     *
     * @param AbstractAttribute $pimAttribute
     *
     * @throws \Exception
     * @throws \SoapCallException
     *
     * @return void
     */
    protected function addGroupToAttributeSet($pimAttribute)
    {
        $families = $pimAttribute->getFamilies();
        $group = $pimAttribute->getGroup();

        if (isset($group)) {
            $groupName = $group->getCode();

            foreach ($families as $family) {
                $magentoSetId = $this->familyMappingManager->getIdFromFamily($family, $this->getSoapUrl());
                if (null === $magentoSetId) {
                    $magentoAttributeSets = $this->webservice->getAttributeSetList();
                    if (array_key_exists($family->getCode(), $magentoAttributeSets)) {
                        $magentoSetId = $magentoAttributeSets[$family->getCode()];
                    }
                }
                if ($magentoSetId === null) {
                    throw new InvalidItemException(
                        sprintf(
                            "Attribute set [%s] not known in Magento. ".
                            "Did you launch the family export before launching this attribute export ?",
                            $family->getCode()
                        ),
                        ['code' => $pimAttribute->getCode()]
                    );
                }

                try {
                    $magentoGroupId = $this->webservice->addAttributeGroupToAttributeSet($magentoSetId, $groupName);
                    $this->attributeGroupMappingManager->registerGroupMapping(
                        $group,
                        $family,
                        $magentoGroupId,
                        $this->getSoapUrl()
                    );
                } catch (SoapCallException $e) {
                    if (!strpos($e->getMessage(), 'already') !== false) {
                        throw $e;
                    }
                }
            }
        }
    }
}
