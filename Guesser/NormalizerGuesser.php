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
use Pim\Bundle\MagentoConnectorBundle\Manager\AssociationTypeManager;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\CategoryNormalizer;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\OptionNormalizer;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\AttributeNormalizer;

/**
 * A magento guesser to get the proper normalizer
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NormalizerGuesser extends AbstractGuesser
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
     * @var AssociationTypeManager
     */
    protected $associationTypeManager;

    /**
     * Constructor
     * @param ChannelManager         $channelManager
     * @param MediaManager           $mediaManager
     * @param ProductValueNormalizer $productValueNormalizer
     * @param CategoryMappingManager $categoryMappingManager
     * @param AssociationTypeManager $associationTypeManager
     */
    public function __construct(
        ChannelManager $channelManager,
        MediaManager $mediaManager,
        ProductValueNormalizer $productValueNormalizer,
        CategoryMappingManager $categoryMappingManager,
        AssociationTypeManager $associationTypeManager
    ) {
        $this->channelManager         = $channelManager;
        $this->mediaManager           = $mediaManager;
        $this->productValueNormalizer = $productValueNormalizer;
        $this->categoryMappingManager = $categoryMappingManager;
        $this->associationTypeManager = $associationTypeManager;
    }

    /**
     * Get the product normalizer corresponding to the given Magento parameters
     * @param MagentoSoapClientParameters $clientParameters
     * @param bool                        $enabled
     * @param bool                        $visibility
     * @param string                      $currency
     * @param string                      $magentoUrl
     *
     * @throws NotSupportedVersionException If the magento version is not supported
     * @return AbstractNormalizer
     */
    public function getProductNormalizer(
        MagentoSoapClientParameters $clientParameters,
        $enabled,
        $visibility,
        $currency,
        $magentoUrl
    ) {
        $client         = $this->getMagentoSoapClient($clientParameters);
        $magentoVersion = $this->getMagentoVersion($client);

        switch ($magentoVersion) {
            case AbstractGuesser::MAGENTO_VERSION_1_8:
            case AbstractGuesser::MAGENTO_VERSION_1_7:
                return new ProductNormalizer(
                    $this->channelManager,
                    $this->mediaManager,
                    $this->productValueNormalizer,
                    $this->categoryMappingManager,
                    $this->associationTypeManager,
                    $enabled,
                    $visibility,
                    $currency,
                    $magentoUrl
                );
            case AbstractGuesser::MAGENTO_VERSION_1_6:
                return new ProductNormalizer16(
                    $this->channelManager,
                    $this->mediaManager,
                    $this->productValueNormalizer,
                    $this->categoryMappingManager,
                    $this->associationTypeManager,
                    $enabled,
                    $visibility,
                    $currency,
                    $magentoUrl
                );
            default:
                throw new NotSupportedVersionException(AbstractGuesser::MAGENTO_VERSION_NOT_SUPPORTED_MESSAGE);
        }
    }

    /**
     * Get the configurable normalizer corresponding to the given Magento parameters
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
        $client = $this->getMagentoSoapClient($clientParameters);

        $magentoVersion = $this->getMagentoVersion($client);

        switch ($magentoVersion) {
            case AbstractGuesser::MAGENTO_VERSION_1_8:
            case AbstractGuesser::MAGENTO_VERSION_1_7:
            case AbstractGuesser::MAGENTO_VERSION_1_6:
                return new ConfigurableNormalizer(
                    $this->channelManager,
                    $productNormalizer,
                    $priceMappingManager
                );
            default:
                throw new NotSupportedVersionException(AbstractGuesser::MAGENTO_VERSION_NOT_SUPPORTED_MESSAGE);
        }
    }

    /**
     * Get the Category normalizer corresponding to the given Magento parameters
     * @param MagentoSoapClientParameters $clientParameters
     *
     * @return AbstractNormalizer
     */
    public function getCategoryNormalizer(MagentoSoapClientParameters $clientParameters)
    {
        $client = $this->getMagentoSoapClient($clientParameters);

        $magentoVersion = $this->getMagentoVersion($client);

        switch ($magentoVersion) {
            case AbstractGuesser::MAGENTO_VERSION_1_8:
            case AbstractGuesser::MAGENTO_VERSION_1_7:
            case AbstractGuesser::MAGENTO_VERSION_1_6:
                return new CategoryNormalizer(
                    $this->channelManager,
                    $this->categoryMappingManager
                );
            default:
                throw new NotSupportedVersionException(AbstractGuesser::MAGENTO_VERSION_NOT_SUPPORTED_MESSAGE);
        }
    }

    /**
     * Get the option normalizer corresponding to the given Magento parameters
     * @param MagentoSoapClientParameters $clientParameters
     *
     * @return AbstractNormalizer
     */
    public function getOptionNormalizer(MagentoSoapClientParameters $clientParameters)
    {
        $client = $this->getMagentoSoapClient($clientParameters);

        $magentoVersion = $this->getMagentoVersion($client);

        switch ($magentoVersion) {
            case AbstractGuesser::MAGENTO_VERSION_1_8:
            case AbstractGuesser::MAGENTO_VERSION_1_7:
            case AbstractGuesser::MAGENTO_VERSION_1_6:
                return new OptionNormalizer($this->channelManager);
            default:
                throw new NotSupportedVersionException(AbstractGuesser::MAGENTO_VERSION_NOT_SUPPORTED_MESSAGE);
        }
    }

    /**
     * Get the attribute normalizer corresponding to the given Magento parameters
     * @param MagentoSoapClientParameters $clientParameters
     *
     * @return AbstractNormalizer
     */
    public function getAttributeNormalizer(MagentoSoapClientParameters $clientParameters) {
        $client = $this->getMagentoSoapClient($clientParameters);

        $magentoVersion = $this->getMagentoVersion($client);

        switch ($magentoVersion) {
            case AbstractGuesser::MAGENTO_VERSION_1_8:
            case AbstractGuesser::MAGENTO_VERSION_1_7:
            case AbstractGuesser::MAGENTO_VERSION_1_6:
                return new AttributeNormalizer($this->productValueNormalizer);
            default:
                throw new NotSupportedVersionException(AbstractGuesser::MAGENTO_VERSION_NOT_SUPPORTED_MESSAGE);
        }
    }
}
