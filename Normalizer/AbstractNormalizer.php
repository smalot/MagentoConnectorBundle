<?php

namespace Pim\Bundle\MagentoConnectorBundle\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\Exception\LocaleNotMatchedException;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;

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
    protected $pimLocales;

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

    /**
     * Get all Pim locales for the given channel
     * @param string $channel
     *
     * @return array The locales
     */
    protected function getPimLocales($channel)
    {
        if (!$this->pimLocales) {
            $this->pimLocales = $this->channelManager
                ->getChannelByCode($channel)
                ->getLocales();
        }

        return $this->pimLocales;
    }

    /**
     * Get the corresponding storeview code for a givent locale
     * @param string $locale
     * @param array  $magentoStoreViews
     * @param array  $storeViewMapping
     *
     * @return string
     */
    protected function getStoreViewCodeForLocale($locale, $magentoStoreViews, $storeViewMapping)
    {
        $mappedStoreView = $this->getMappedStoreView($locale, $storeViewMapping);

        $code = ($mappedStoreView) ? $mappedStoreView : $locale;

        return $this->getStoreView($code, $magentoStoreViews);
    }

    /**
     * Get the locale based on storeViewMapping
     * @param string $locale
     * @param array  $storeViewMapping
     *
     * @return string
     */
    protected function getMappedStoreView($locale, $storeViewMapping)
    {
        foreach ($storeViewMapping as $storeview) {
            if ($storeview[0] === strtolower($locale)) {
                return $storeview[1];
            }
        }
    }

    /**
     * Get the storeview for the given code
     * @param string $code
     * @param array  $magentoStoreViews
     *
     * @return null|string
     */
    protected function getStoreView($code, $magentoStoreViews)
    {
        foreach ($magentoStoreViews as $magentoStoreView) {
            if ($magentoStoreView['code'] === strtolower($code)) {
                return $magentoStoreView['code'];
            }
        }
    }

    /**
     * Manage not found locales
     * @param string $storeViewCode
     * @param array  $magentoStoreViewMapping
     *
     * @throws LocaleNotMatchedException
     */
    protected function localeNotFound($storeViewCode, array $magentoStoreViewMapping)
    {
        throw new LocaleNotMatchedException(
            sprintf(
                'No storeview found for "%s" locale. Please create a storeview named "%s" on your Magento or map ' .
                'this locale to a storeview code.',
                $storeViewCode,
                $storeViewCode
            )
        );
    }
}
