<?php

namespace Pim\Bundle\MagentoConnectorBundle\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;

/**
 * A normalizer to transform a product entity into an array
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractNormalizer implements NormalizerInterface
{
    const MAGENTO_SIMPLE_PRODUCT_KEY       = 'simple';
    const MAGENTO_CONFIGURABLE_PRODUCT_KEY = 'configurable';
    const DATE_FORMAT                      = 'Y-m-d H:i:s';

    /**
     * @var array
     */
    protected $supportedFormats = array('MagentoArray');

    /**
     * @var ChannelManager
     */
    protected $channelManager;

    /**
     * Constructor
     * @param ChannelManager $channelManager
     */
    public function __construct(ChannelManager $channelManager)
    {
        $this->channelManager = $channelManager;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ProductInterface && in_array($format, $this->supportedFormats);
    }
}
