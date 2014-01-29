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

    protected function magentoAttributeExists(Attribute $attribute, array $magentoAttributes)
    {
        return array_key_exists($attribute->getCode(), $magentoAttributes);
    }

    protected function normalizeAttribute(Attribute $attribute, array $context)
    {
        try {
            $processedItem = $this->productNormalizer->normalize(
                $attribute,
                AbstractNormalizer::MAGENTO_FORMAT,
                $context
            );
        } catch (NormalizeException $e) {
            throw new InvalidItemException($e->getMessage(), array($product));
        } catch (SoapCallException $e) {
            throw new InvalidItemException($e->getMessage(), array($product));
        }

        return $processedItem;
    }
}
