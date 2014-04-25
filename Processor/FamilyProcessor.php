<?php

namespace Pim\Bundle\MagentoConnectorBundle\Processor;

use Akeneo\Bundle\BatchBundle\Item\InvalidItemException;
use Akeneo\Bundle\BatchBundle\Item\ItemProcessorInterface;
use Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Pim\Bundle\CatalogBundle\Entity\Family;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\AbstractNormalizer;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\Exception\NormalizeException;
use Pim\Bundle\TransformBundle\Normalizer\FamilyNormalizer;

/**
 * Magento family processor
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyProcessor extends AbstractProcessor
{
    /**
     * @var FamilyNormalizer
     */
    protected $familyNormalizer;

    /**
     * @var array
     */
    protected $globalContext;

    /**
     * {@inheritdoc}
     */
    protected function beforeExecute()
    {
        parent::beforeExecute();

        $magentoStoreViews = $this->webservice->getStoreViewsList();

        $this->familyNormalizer = $this->normalizerGuesser
            ->getFamilyNormalizer($this->getClientParameters());
        $this->globalContext['magentoFamilies'] = $this->webservice->getAttributeSetList();
        $this->globalContext['magentoStoreViews']        = $magentoStoreViews;
    }

    /**
     * {@inheritdoc}
     */
    public function process($family)
    {
        $this->beforeExecute();
        $result = array();

        $magentoAttributesSet  = $this->webservice->getAttributeSetList();

        $result['family'] = $family;
        $result['attributes'] = $family->getAttributes();
        // AttributeSet
        if (!$this->magentoAttributeSetExists($family, $magentoAttributesSet)) {
            $result['create'] = $this->normalizeFamily($family, $this->globalContext);
        }

        return $result;
    }

    /**
     * Test if an attribute set exist on magento
     * @param Family $family               Family of attribute
     * @param array  $magentoAttributesSet Attribute sets from magento
     *
     * @return boolean Return true if the family exist on magento
     */
    protected function magentoAttributeSetExists(Family $family, array $magentoAttributesSet)
    {
        return array_key_exists($family->getCode(), $magentoAttributesSet);
    }

    /**
     * Normalize the given family
     * @param Family $family        Family of attribute
     * @param array  $context
     *
     * @throws InvalidItemException If a problem occurred with the normalizer
     * @return array
     */
    protected function normalizeFamily(Family $family, array $context)
    {
        try {
            $processedItem = $this->familyNormalizer->normalize(
                $family,
                AbstractNormalizer::MAGENTO_FORMAT,
                $context
            );
        } catch (NormalizeException $e) {
            throw new InvalidItemException($e->getMessage(), array($family));
        }

        return $processedItem;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return parent::getConfigurationFields();
    }
}
