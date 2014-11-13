<?php

namespace Pim\Bundle\MagentoConnectorBundle\Normalizer;

use Pim\Bundle\CatalogBundle\Manager\MediaManager;
use Pim\Bundle\CatalogBundle\Model\AbstractProductMedia;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Product media normalizer
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductMediaNormalizer implements NormalizerInterface
{
    /** @var MediaManager */
    protected $mediaManager;

    /**
     * @param MediaManager $mediaManager
     */
    public function __construct(MediaManager $mediaManager)
    {
        $this->mediaManager = $mediaManager;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $attributeCode = $object->getValue()->getAttribute()->getCode();

        return [
            [
                $attributeCode              => $object->getFileName(),
                $attributeCode . '_content' => $this->mediaManager->getBase64($object)
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof AbstractProductMedia && ProductNormalizer::API_IMPORT_FORMAT === $format;
    }
}
