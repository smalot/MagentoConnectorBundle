<?php

namespace Pim\Bundle\MagentoConnectorBundle\Processor;

use Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Akeneo\Bundle\BatchBundle\Item\InvalidItemException;
use Akeneo\Bundle\BatchBundle\Item\ItemProcessorInterface;
use Pim\Bundle\CatalogBundle\Entity\Family;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\Dictionary\AttributeLabelDictionary;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\Dictionary\FamilyLabelDictionary;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\Dictionary\ProductLabelDictionary;

/**
 * Add mandatory attribute to attribute sets
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddMandatoryAttributeToSetsProcessor extends AbstractConfigurableStepElement implements ItemProcessorInterface
{
    /**
     * {@inheritdoc}
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

        $processedAttributes = [];
        foreach ($this->getMandatoryAttributes() as $key => $attribute) {
            $processedAttributes[] = [
                FamilyLabelDictionary::ATTRIBUTE_SET_ID_HEADER   =>
                    $this->getFamilyLabel($item['family'], $context['defaultLocale']),
                AttributeLabelDictionary::ID_HEADER              => $attribute,
                FamilyLabelDictionary::ATTRIBUTE_GROUP_ID_HEADER => FamilyLabelDictionary::ATTRIBUTE_GROUP_GENERAL,
                AttributeLabelDictionary::SORT_ORDER_HEADER      => $key
            ];
        }

        return $processedAttributes;
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
     * Gives mandatory attributes.
     * Those attributes are not sent by the job which associates attributes to sets
     * because they are in the Magento configuration and not in product attributes. (enabled and visibility)
     *
     * @return array
     */
    protected function getMandatoryAttributes()
    {
        // TODO : see MC-118 attribute reader have to send identifier then remove sku from here
        return [
            ProductLabelDictionary::SKU_HEADER,
            ProductLabelDictionary::VISIBILITY_HEADER,
            ProductLabelDictionary::STATUS_HEADER
        ];
    }
}
