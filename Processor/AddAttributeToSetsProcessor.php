<?php

namespace Pim\Bundle\MagentoConnectorBundle\Processor;

use Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Akeneo\Bundle\BatchBundle\Item\ItemProcessorInterface;
use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;
use Pim\Bundle\CatalogBundle\Entity\Family;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\Dictionary\AttributeLabelDictionary;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\Dictionary\FamilyLabelDictionary;

/**
 * Associates attributes to attribute sets and groups
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddAttributeToSetsProcessor extends AbstractConfigurableStepElement implements ItemProcessorInterface
{
    /**
     * {@inheritdoc}
     *
     */
    public function getConfigurationFields()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        // TODO remove hard coded context and use MagentoConfiguration
        $context = [
            'defaultLocale'    => 'en_US',
            'defaultStoreView' => 'Default',
            'visibility'       => true,
            'storeViewMapping' => [
                'fr_FR' => 'fr_fr'
            ],
        ];

        $normalized     = [];
        $families       = $item->getFamilies();
        $attributeGroup = $item->getGroup();
        $attrGroupLabel = $this->getAttributeGroupLabel($attributeGroup, $context['defaultLocale']);
        $attributeCode  = $item->getCode();
        $sortOrder      = $item->getSortOrder();

        foreach ($families as $key => $family) {
            $normalized[$key] = $this->normalize($context, $family, $attrGroupLabel, $attributeCode, $sortOrder);
        }

        return $normalized;
    }

    /**
     * Normalizes data to an association
     *
     * @param array  $context
     * @param Family $family
     * @param string $attrGroupLabel
     * @param string $attributeCode
     * @param int    $sortOrder
     *
     * @return array
     */
    protected function normalize(
        array $context,
        Family $family,
        $attrGroupLabel,
        $attributeCode,
        $sortOrder
    ) {
        return [
            FamilyLabelDictionary::ATTRIBUTE_SET_ID_HEADER   =>
                $this->getFamilyLabel($family, $context['defaultLocale']),
            AttributeLabelDictionary::ID_HEADER              => $attributeCode,
            FamilyLabelDictionary::ATTRIBUTE_GROUP_ID_HEADER => $attrGroupLabel,
            AttributeLabelDictionary::SORT_ORDER_HEADER      => $sortOrder
        ];
    }

    /**
     * Get family label for the given default locale
     *
     * @param Family $family
     * @param string $defaultLocale
     *
     * @return string
     */
    protected function getFamilyLabel(Family $family, $defaultLocale)
    {
        $family->setLocale($defaultLocale);

        return $family->getLabel();
    }

    /**
     * Get attribute group label for the given default locale
     *
     * @param AttributeGroup $attributeGroup
     * @param string         $defaultLocale
     *
     * @return string
     */
    protected function getAttributeGroupLabel(AttributeGroup $attributeGroup, $defaultLocale)
    {
        $attributeGroup->setLocale($defaultLocale);

        return $attributeGroup->getLabel();
    }
}
