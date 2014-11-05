<?php

namespace Pim\Bundle\MagentoConnectorBundle\Normalizer;

use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\CatalogBundle\Model\AbstractAssociation;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\MagentoConnectorBundle\Helper\MagentoAttributesHelper;
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

    /**
     * Constructor
     */
    public function __construct(MagentoAttributesHelper $attributeHelper)
    {
        $this->attributeHelper = $attributeHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $associations       = [];
        $channel            = $context['channel'];
        $validProducts      = $this->getValidProducts($object, $channel);
        $associationMapping = $context['associationMapping'];
        $typeCode           = $associationMapping[$object->getAssociationType()->getCode()];

        if (!empty($validProducts) && !empty($typeCode)) {
            $header         = $this->attributeHelper->getAssociationTypeHeader($typeCode);
            $associations[] = array_merge(
                $this->getBaseProduct(
                    $object->getOwner(),
                    $context['attributeMapping'],
                    $this->attributeHelper->getMandatoryAttributeCodesForAssociations(),
                    $context['defaultLocale'],
                    $channel->getCode()
                ),
                [MagentoAttributesHelper::HEADER_STATUS => $context['enabled']]
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

    /**
     * Exactly the same as in VariantGroupHelper, just update $variantGroup to $association
     * Return products from the association which are completes and in the good channel
     *
     * @param AbstractAssociation $association
     * @param Channel             $channel
     *
     * @return ProductInterface[]
     */
    protected function getValidProducts(AbstractAssociation $association, Channel $channel)
    {
        $validProducts  = [];
        $rootCategoryId = $channel->getCategory()->getId();

        foreach ($association->getProducts() as $product) {
            $isComplete = true;
            $completenesses = $product->getCompletenesses()->getIterator();
            while ((list($key, $completeness) = each($completenesses)) && $isComplete) {
                if ($completeness->getChannel()->getId() === $channel->getId() &&
                    $completeness->getRatio() < 100
                ) {
                    $isComplete = false;
                }
            }

            $productCategories = $product->getCategories()->getIterator();
            if ($isComplete && false !== $productCategories) {
                $isInChannel = false;
                while ((list($key, $category) = each($productCategories)) && !$isInChannel) {
                    if ($category->getRoot() === $rootCategoryId) {
                        $isInChannel = true;
                        $validProducts[] = $product;
                    }
                }
            }
        }

        return $validProducts;
    }
}
