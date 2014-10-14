<?php

namespace Pim\Bundle\MagentoConnectorBundle\Normalizer;

use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\scalar;

/**
 * Product value normalizer
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductValueNormalizer implements NormalizerInterface
{
    /** @var string[] $supportedFormats */
    protected $supportedFormats = ['api_import'];

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = array())
    {
        $defaultStoreView = $context['defaultStoreView'];
        $locale           = $object->getLocale();
        $attribute        = $object->getAttribute();
        $code             = $attribute->getCode();
        $values           = [];

        switch ($attribute->getAttributeType()) {
            case 'pim_catalog_identifier':
                $values[$defaultStoreView][ProductNormalizer::HEADER_SKU] = $object->__toString();
            break;

            case 'pim_catalog_text':
            case 'pim_catalog_textarea':
                $value = $object->getData();
            break;

            case 'pim_catalog_number':
            case 'pim_catalog_boolean':
                $value = floatval($object->getData());
            break;

            case 'pim_catalog_price_collection':
                $price = $object->getPrice($context['defaultCurrency']);

                if (null !== $price) {
                    $value = $price->getData();
                }
            break;

            case 'pim_catalog_metric':
                $value = $object->getMetric()->getData();
            break;

            case 'pim_catalog_simpleselect':
                $value = $object->getOption()->getCode();
            break;

            case 'pim_catalog_multiselect':
                foreach ($object->getOptions() as $option) {
                    $values[] = [ProductNormalizer::HEADER_STORE => '', $code => $option->getCode()];
                }
            break;

            case 'pim_catalog_date':
                $value = $object->getDate()->format(ProductNormalizer::DATE_FORMAT);
            break;

            // TODO : implement image and file attribute types
            default:
                $value = $object->__toString();
            break;
        }

        if (isset($value)) {
            if (null === $locale || $locale === $context['defaultLocale']) {
                $values[$defaultStoreView][$code] = $value;
            } else {
                $values[$context['storeViewMapping'][$locale]][$code] = $value;
            }
        }

        return $values;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ProductValueInterface && in_array($format, $this->supportedFormats);
    }
}
