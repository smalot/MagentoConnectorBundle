<?php

namespace Pim\Bundle\MagentoConnectorBundle\Normalizer;

use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\CatalogBundle\Model\AbstractAssociation;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\MagentoConnectorBundle\Helper\ValidProductHelper;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\Dictionary\ProductLabelDictionary;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\Exception\MandatoryAttributeNotFoundException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Association normalizer
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssociationNormalizer implements NormalizerInterface
{
    /** @var ValidProductHelper */
    protected $validProductHelper;

    /**
     * Constructor
     *
     * @param ValidProductHelper $validProductHelper
     */
    public function __construct(ValidProductHelper $validProductHelper)
    {
        $this->validProductHelper = $validProductHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($association, $format = null, array $context = [])
    {
        $associations       = [];
        $channel            = $context['channel'];
        $validProducts      = $this->validProductHelper->getValidProducts($channel, $association->getProducts());
        $associationMapping = $context['associationMapping'];
        $typeCode           = $associationMapping[$association->getAssociationType()->getCode()];

        if (!empty($validProducts) && !empty($typeCode)) {
            $header = ProductLabelDictionary::getAssociationTypeHeader($typeCode);

            foreach ($validProducts as $associatedProduct) {
                $associations[][$header] = (string) $associatedProduct->getIdentifier();
            }
        }

        return $associations;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof AbstractAssociation && 'api_import' === $format;
    }
}
