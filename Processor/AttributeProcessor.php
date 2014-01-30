<?php

namespace Pim\Bundle\MagentoConnectorBundle\Processor;

use Oro\Bundle\BatchBundle\Item\InvalidItemException;
use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\MagentoConnectorBundle\Guesser\NormalizerGuesser;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\AbstractNormalizer;
use Pim\Bundle\MagentoConnectorBundle\Webservice\SoapCallException;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\Exception\NormalizeException;
use Pim\Bundle\CatalogBundle\Entity\Attribute;

/**
 * Magento attributes processor
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeProcessor extends AbstractProcessor
{
    /**
     * {@inheritdoc}
     */
    protected function beforeExecute()
    {
        parent::beforeExecute();

        $this->attributeNormalizer = $this->normalizerGuesser->getAttributeNormalizer($this->getClientParameters());
        $this->globalContext['magentoStoreViews'] = $this->webservice->getStoreViewsList();
        $this->globalContext['defaultLocale']     = $this->defaultLocale;
    }

    /**
     * {@inheritdoc}
     */
    public function process($attribute)
    {
        $this->beforeExecute();

        $magentoAttributes = $this->webservice->getAllAttributes();

        if ($this->magentoAttributeExists($attribute, $magentoAttributes)) {
            $this->globalContext['create'] = false;
        } else {
            $this->globalContext['create'] = true;
        }

        return $this->normalizeAttribute($attribute, $this->globalContext);
    }

    /**
     * Test if an attribute exist on magento
     * @param Attribute $attribute
     * @param array     $magentoAttributes
     *
     * @return boolean
     */
    protected function magentoAttributeExists(Attribute $attribute, array $magentoAttributes)
    {
        return array_key_exists($attribute->getCode(), $magentoAttributes);
    }

    /**
     * Normalize the given attribute
     * @param Attribute $attribute
     * @param array     $context
     *
     * @return array
     */
    protected function normalizeAttribute(Attribute $attribute, array $context)
    {
        try {
            $processedItem = $this->attributeNormalizer->normalize(
                $attribute,
                AbstractNormalizer::MAGENTO_FORMAT,
                $context
            );
        } catch (NormalizeException $e) {
            throw new InvalidItemException($e->getMessage(), array($attribute));
        } catch (SoapCallException $e) {
            throw new InvalidItemException($e->getMessage(), array($attribute));
        }

        return $processedItem;
    }
}
