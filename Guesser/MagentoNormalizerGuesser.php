<?php

namespace Pim\Bundle\MagentoConnectorBundle\Guesser;

use Pim\Bundle\MagentoConnectorBundle\Normalizer\ProductNormalizer;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\ProductNormalizer16;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\ConfigurableNormalizer;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientParameters;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClient;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\CatalogBundle\Manager\MediaManager;
use Pim\Bundle\MagentoConnectorBundle\Manager\PriceMappingManager;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\ProductNormalizerInterface;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\ValueNormalizer;

/**
 * A magento guesser to get the proper normalizer
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MagentoNormalizerGuesser extends MagentoGuesser
{
    /**
     * @var ChannelManager
     */
    protected $channelManager;

    /**
     * @var MediaManager
     */
    protected $mediaManager;

    /**
     * @var ValueNormalizer
     */
    protected $valueNormalizer;

    /**
     * Constructor
     * @param ChannelManager  $channelManager
     * @param MediaManager    $mediaManager
     * @param ValueNormalizer $valueNormalizer
     */
    public function __construct(
        ChannelManager $channelManager,
        MediaManager $mediaManager,
        ValueNormalizer $valueNormalizer
    ) {
        $this->channelManager  = $channelManager;
        $this->mediaManager    = $mediaManager;
        $this->valueNormalizer = $valueNormalizer;
    }

    /**
     * Get the MagentoWebservice corresponding to the given Magento parameters
     * @param MagentoSoapClientParameters $clientParameters
     * @param bool                        $enabled
     * @param bool                        $visibility
     * @param string                      $currency
     *
     * @throws NotSupportedVersionException If the magento version is not supported
     * @return MagentoWebservice
     */
    public function getProductNormalizer(
        MagentoSoapClientParameters $clientParameters,
        $enabled,
        $visibility,
        $currency
    ) {
        $client         = new MagentoSoapClient($clientParameters);
        $magentoVersion = $this->getMagentoVersion($client);

        switch ($magentoVersion) {
            case MagentoGuesser::MAGENTO_VERSION_1_8:
            case MagentoGuesser::MAGENTO_VERSION_1_7:
                return new ProductNormalizer(
                    $this->channelManager,
                    $this->mediaManager,
                    $this->valueNormalizer,
                    $enabled,
                    $visibility,
                    $currency
                );
            case MagentoGuesser::MAGENTO_VERSION_1_6:
                return ProductNormalizer16(
                    $this->channelManager,
                    $this->mediaManager,
                    $this->valueNormalizer,
                    $enabled,
                    $visibility,
                    $currency
                );
            default:
                throw new NotSupportedVersionException('Your Magento version is not supported yet.');
        }
    }

    /**
     * Get the MagentoWebservice corresponding to the given Magento parameters
     * @param MagentoSoapClientParameters $clientParameters
     * @param ProductNormalizerInterface  $productNormalizer
     * @param PriceMappingManager         $priceMappingManager
     *
     * @return MagentoWebservice
     */
    public function getConfigurableNormalizer(
        MagentoSoapClientParameters $clientParameters,
        ProductNormalizerInterface $productNormalizer,
        PriceMappingManager $priceMappingManager
    ) {
        $client = new MagentoSoapClient($clientParameters);

        $magentoVersion = $this->getMagentoVersion($client);

        switch ($magentoVersion) {
            case MagentoGuesser::MAGENTO_VERSION_1_8:
            case MagentoGuesser::MAGENTO_VERSION_1_7:
            case MagentoGuesser::MAGENTO_VERSION_1_6:
                return new ConfigurableNormalizer(
                    $this->channelManager,
                    $productNormalizer,
                    $priceMappingManager
                );
            default:
                throw new NotSupportedVersionException('Your Magento version is not supported yet.');
        }
    }
}
