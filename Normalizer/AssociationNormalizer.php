<?php

namespace Pim\Bundle\MagentoConnectorBundle\Normalizer;

use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\CatalogBundle\Model\AbstractAssociation;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\MagentoConnectorBundle\Helper\MagentoAttributesHelper;
use Pim\Bundle\MagentoConnectorBundle\Helper\ValidProductHelper;
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
    /** @var MagentoAttributesHelper */
    protected $attributeHelper;

    /** @var ValidProductHelper */
    protected $validProductHelper;

    /**
     * Constructor
     *
     * @param MagentoAttributesHelper $attributeHelper
     * @param ValidProductHelper      $validProductHelper
     */
    public function __construct(MagentoAttributesHelper $attributeHelper, ValidProductHelper $validProductHelper)
    {
        $this->attributeHelper    = $attributeHelper;
        $this->validProductHelper = $validProductHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $associations       = [];
        $channel            = $context['channel'];
        $validProducts      = $this->validProductHelper->getValidProducts($channel, $object->getProducts());
        $associationMapping = $context['associationMapping'];
        $typeCode           = $associationMapping[$object->getAssociationType()->getCode()];

        if (!empty($validProducts) && !empty($typeCode)) {
            $header = $this->attributeHelper->getAssociationTypeHeader($typeCode);
            $associations[] = array_merge(
                $this->getBaseProduct(
                    $object->getOwner(),
                    $context['attributeMapping'],
                    $this->attributeHelper->getMandatoryAttributeCodesForAssociations(),
                    $context['defaultLocale'],
                    $channel->getCode()
                ),
                [$this->attributeHelper->getHeaderStatus() => $context['enabled']]
            );

            foreach ($validProducts as $associatedProduct) {
                $associations[][$header] = (string) $associatedProduct->getIdentifier();
            }
        }

        return empty($associations) ? null : $associations;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof AbstractAssociation && ProductNormalizer::API_IMPORT_FORMAT === $format;
    }

    /**
     * Create the first line of a simple product to be able to update it with next rows
     *
     * @param ProductInterface $product
     * @param array            $attributeMapping
     * @param array            $mandatoryAttributes
     * @param string           $locale
     * @param string           $channelCode
     *
     * @throws MandatoryAttributeNotFoundException
     *
     * @return array
     */
    protected function getBaseProduct(
        ProductInterface $product,
        array $attributeMapping,
        array $mandatoryAttributes,
        $locale,
        $channelCode
    ) {
        $baseProduct = [];

        foreach ($mandatoryAttributes as $mandatoryAttribute) {
            $mandatoryAttributeValue = $product->getValue(
                $attributeMapping[$mandatoryAttribute],
                $locale,
                $channelCode
            );
            if (null === $mandatoryAttributeValue) {
                throw new MandatoryAttributeNotFoundException(
                    sprintf(
                        'Mandatory attribute with code "%s" not found in product "%s" during association creation.',
                        $attributeMapping[$mandatoryAttribute],
                        (string) $product->getIdentifier()
                    )
                );
            }

            $baseProduct[$mandatoryAttribute] = $mandatoryAttributeValue->getData();
        }

        return $baseProduct;
    }
}
