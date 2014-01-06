<?php

namespace Pim\Bundle\MagentoConnectorBundle\Guesser;

use Pim\Bundle\MagentoConnectorBundle\Normalizer\ProductNormalizer;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\ProductNormalizer16;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientParameters;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClient;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\CatalogBundle\Manager\MediaManager;

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
     * Constructor
     * @param ChannelManager $channelManager
     * @param MediaManager   $mediaManager
     */
    public function __construct(
        ChannelManager $channelManager,
        MediaManager   $mediaManager
    ) {
        $this->channelManager = $channelManager;
        $this->mediaManager   = $mediaManager;
    }

    /**
     * Get the MagentoWebservice corresponding to the given Magento parameters
     * @param  MagentoSoapClientParameters $clientParameters
     * @return MagentoWebservice
     */
    public function getNormalizer(MagentoSoapClientParameters $clientParameters)
    {
        $client = new MagentoSoapClient($clientParameters);

        $magentoVersion = $this->getMagentoVersion($client);

        switch ($magentoVersion) {
            case '1.8':
            case '1.7':
                $magentoNormalizer = new ProductNormalizer($this->channelManager, $this->mediaManager);
            break;
            case '1.6':
                $magentoNormalizer = new ProductNormalizer16($this->channelManager, $this->mediaManager);
            break;
            default:
                throw new NotSupportedVersionException('Your Magento version is not supported yet.');
        }

        return $magentoNormalizer;
    }
}
