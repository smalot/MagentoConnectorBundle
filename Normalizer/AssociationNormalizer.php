<?php

namespace Pim\Bundle\MagentoConnectorBundle\Normalizer;

use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\CatalogBundle\Model\AbstractAssociation;
use Pim\Bundle\MagentoConnectorBundle\Helper\ExportableProductHelper;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\Dictionary\ProductLabelDictionary;
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
    /** @var ExportableProductHelper */
    protected $exportableProductHelper;

    /**
     * @param ExportableProductHelper $exportableProductHelper
     */
    public function __construct(ExportableProductHelper $exportableProductHelper)
    {
        $this->exportableProductHelper = $exportableProductHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($association, $format = null, array $context = [])
    {
        $associations       = [];
        $channel            = $context['channel'];
        $exportableProducts = $this->exportableProductHelper
            ->getExportableProducts($channel, $association->getProducts());
        $associationMapping = $context['associationMapping'];
        $assocTypeCode      = $associationMapping[$association->getAssociationType()->getCode()];

        if (!empty($exportableProducts) && !empty($assocTypeCode)) {
            $header = ProductLabelDictionary::getAssociationTypeHeader($assocTypeCode);

            foreach ($exportableProducts as $associatedProduct) {
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
