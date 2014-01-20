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
use Pim\Bundle\MagentoConnectorBundle\Normalizer\ProductValueNormalizer;
use Pim\Bundle\MagentoConnectorBundle\Manager\CategoryMappingManager;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\CategoryNormalizer;

/**
 * A magento guesser to get the proper normalizer
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NormalizerGuesser extends Guesser
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
     * @var ProductValueNormalizer
     */
    protected $productValueNormalizer;

    /**
     * Constructor
     * @param ChannelManager         $channelManager
     * @param MediaManager           $mediaManager
     * @param ProductValueNormalizer $productValueNormalizer
     */
    public function __construct(
        ChannelManager $channelManager,
        MediaManager $mediaManager,
        ProductValueNormalizer $productValueNormalizer
    ) {
        $this->channelManager         = $channelManager;
        $this->mediaManager           = $mediaManager;
        $this->productValueNormalizer = $productValueNormalizer;
    }

    /**
     * Get the Webservice corresponding to the given Magento parameters
     * @param MagentoSoapClientParameters $clientParameters
     * @param bool                        $enabled
     * @param bool                        $visibility
     * @param string                      $currency
     *
     * @throws NotSupportedVersionException If the magento version is not supported
     * @return AbstractNormalizer
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
            case Guesser::MAGENTO_VERSION_1_8:
            case Guesser::MAGENTO_VERSION_1_7:
                return new ProductNormalizer(
                    $this->channelManager,
                    $this->mediaManager,
                    $this->productValueNormalizer,
                    $enabled,
                    $visibility,
                    $currency
                );
            case Guesser::MAGENTO_VERSION_1_6:
                return new ProductNormalizer16(
                    $this->channelManager,
                    $this->mediaManager,
                    $this->productValueNormalizer,
                    $enabled,
                    $visibility,
                    $currency
                );
            default:
                throw new NotSupportedVersionException(Guesser::MAGENTO_VERSION_NOT_SUPPORTED_MESSAGE);
        }
    }

    /**
     * Get the Webservice corresponding to the given Magento parameters
     * @param MagentoSoapClientParameters $clientParameters
     * @param ProductNormalizerInterface  $productNormalizer
     * @param PriceMappingManager         $priceMappingManager
     *
     * @return AbstractNormalizer
     */
    public function getConfigurableNormalizer(
        MagentoSoapClientParameters $clientParameters,
        ProductNormalizerInterface $productNormalizer,
        PriceMappingManager $priceMappingManager
    ) {
        $client = new MagentoSoapClient($clientParameters);

        $magentoVersion = $this->getMagentoVersion($client);

        switch ($magentoVersion) {
            case Guesser::MAGENTO_VERSION_1_8:
            case Guesser::MAGENTO_VERSION_1_7:
            case Guesser::MAGENTO_VERSION_1_6:
                return new ConfigurableNormalizer(
                    $this->channelManager,
                    $productNormalizer,
                    $priceMappingManager
                );
            default:
                throw new NotSupportedVersionException(Guesser::MAGENTO_VERSION_NOT_SUPPORTED_MESSAGE);
        }
    }

    /**
     * Get the Webservice corresponding to the given Magento parameters
     * @param MagentoSoapClientParameters $clientParameters
     * @param ProductNormalizerInterface  $productNormalizer
     * @param PriceMappingManager         $priceMappingManager
     *
     * @return AbstractNormalizer
     */
    public function getCategoryNormalizer(
        MagentoSoapClientParameters $clientParameters,
        CategoryMappingManager $categoryMappingManager
    ) {
        $client = new MagentoSoapClient($clientParameters);

        $magentoVersion = $this->getMagentoVersion($client);

        switch ($magentoVersion) {
            case Guesser::MAGENTO_VERSION_1_8:
            case Guesser::MAGENTO_VERSION_1_7:
            case Guesser::MAGENTO_VERSION_1_6:
                return new CategoryNormalizer(
                    $this->channelManager,
                    $categoryMappingManager
                );
            default:
                throw new NotSupportedVersionException(Guesser::MAGENTO_VERSION_NOT_SUPPORTED_MESSAGE);
        }
    }
}
