<?php

namespace Pim\Bundle\MagentoConnectorBundle\Normalizer;

use Pim\Bundle\CatalogBundle\Model\AbstractProductPrice;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Product price normalizer
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductPriceNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize($productPrice, $format = null, array $context = [])
    {
        return ($productPrice->getCurrency() === $context['defaultCurrency']) ? $productPrice->getData() : null;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof AbstractProductPrice && 'api_import' === $format;
    }
}
