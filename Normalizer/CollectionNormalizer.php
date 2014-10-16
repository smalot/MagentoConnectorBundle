<?php

namespace Pim\Bundle\MagentoConnectorBundle\Normalizer;

use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Date time normalizer
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CollectionNormalizer implements NormalizerInterface, SerializerAwareInterface
{
    /** @var string[] $supportedFormats */
    protected $supportedFormats;

    /** @var  */
    protected $serializer;
    /**
     * @param array $formats
     */
    public function __construct(array $formats)
    {
        $this->supportedFormats = $formats;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = array())
    {
        $normalized = [];
        foreach ($object as $item) {
            $normalized[] = $this->serializer->normalize($item, $format, $context);
        }

        return (count($normalized) > 0) ? $normalized : null;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Collection && in_array($format, $this->supportedFormats);
    }

    /**
     * Sets the owning Serializer object
     *
     * @param SerializerInterface $serializer
     */
    public function setSerializer(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }
}
