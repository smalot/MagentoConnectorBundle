<?php

namespace Pim\Bundle\MagentoConnectorBundle\Normalizer;

use Pim\Bundle\CatalogBundle\Entity\Group;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Normalize for Group class
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupNormalizer implements NormalizerInterface, SerializerAwareInterface
{
    /** @var VariantGroupHelper */
    protected $variantGroupHelper;

    /** @var NormalizerInterface */
    protected $normalizer;

    /**
     * Constructor
     *
     * @param VariantGroupHelper $variantGroupHelper
     */
    public function __construct(VariantGroupHelper $variantGroupHelper)
    {
        $this->variantGroupHelper = $variantGroupHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = array())
    {
        $normalized = [];
        if ($object->getType()->isVariant()) {
            $this->variantGroupHelper->setSerializer($this->normalizer);
            $normalized = $this->variantGroupHelper->normalize($object, $format, $context);
        }

        return $normalized;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Group && ProductNormalizer::API_IMPORT_FORMAT === $format;
    }

    /**
     * {@inheritdoc}
     */
    public function setSerializer(SerializerInterface $serializer)
    {
        if (!$serializer instanceof NormalizerInterface) {
            throw new \LogicException('Serializer must be a normalizer');
        }

        $this->normalizer = $serializer;
    }
}
