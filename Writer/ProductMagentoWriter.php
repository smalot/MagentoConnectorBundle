<?php

namespace Pim\Bundle\MagentoConnectorBundle\Writer;

use Symfony\Component\Validator\Constraints as Assert;
use Oro\Bundle\BatchBundle\Item\ItemWriterInterface;
use Oro\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;

use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClient;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientParameters;

/**
 * Magento product writer
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductMagentoWriter extends AbstractConfigurableStepElement implements
    ItemWriterInterface
{
    const MAXIMUM_CALLS = 1;

    /**
     * @var ChannelManager
     */
    protected $channelManager;

    /**
     * @var MagentoSoapClient
     */
    protected $magentoSoapClient;

    /**
     * @Assert\NotBlank
     */
    protected $soapUsername;

    /**
     * @Assert\NotBlank
     */
    protected $soapApiKey;

    /**
     * @Assert\NotBlank
     */
    protected $soapUrl;

    /**
     * @Assert\NotBlank
     */
    protected $channel;

    protected $clientParameters;

    /**
     * @param ChannelManager $channelManager
     * @param MagentoSoapClient $channelManager
     */
    public function __construct(
        ChannelManager $channelManager,
        MagentoSoapClient $magentoSoapClient
    )
    {
        $this->channelManager    = $channelManager;
        $this->magentoSoapClient = $magentoSoapClient;
    }

    /**
     * get soapUsername
     *
     * @return string Soap mangeto soapUsername
     */
    public function getSoapUsername()
    {
        return $this->soapUsername;
    }

    /**
     * Set soapUsername
     *
     * @param string $soapUsername Soap mangeto soapUsername
     */
    public function setSoapUsername($soapUsername)
    {
        $this->soapUsername = $soapUsername;

        return $this;
    }

    /**
     * get soapApiKey
     *
     * @return string Soap mangeto soapApiKey
     */
    public function getSoapApiKey()
    {
        return $this->soapApiKey;
    }

    /**
     * Set soapApiKey
     *
     * @param string $soapApiKey Soap mangeto soapApiKey
     */
    public function setSoapApiKey($soapApiKey)
    {
        $this->soapApiKey = $soapApiKey;

        return $this;
    }

    /**
     * get soapUrl
     *
     * @return string mangeto soap url
     */
    public function getSoapUrl()
    {
        return $this->soapUrl;
    }

    /**
     * Set soapUrl
     *
     * @param string $soapUrl mangeto soap url
     */
    public function setSoapUrl($soapUrl)
    {
        $this->soapUrl = $soapUrl;

        return $this;
    }

    /**
     * get channel
     *
     * @return string channel
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * Set channel
     *
     * @param string $channel channel
     */
    public function setChannel($channel)
    {
        $this->channel = $channel;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $items)
    {
        if (!$this->clientParameters) {
            $this->clientParameters = new MagentoSoapClientParameters(
                $this->soapUsername,
                $this->soapApiKey,
                $this->soapUrl
            );
        }

        $this->magentoSoapClient->init($this->clientParameters);

        //creation for each product in the admin storeView (with default locale)
        foreach($items as $batch) {
            foreach ($batch as $item) {
                $this->computeProduct($item);
            }
        }

        $this->magentoSoapClient->sendCalls($this->clientParameters);
    }

    /**
     * Compute an individual product and all his parts (translations)
     *
     * @param  array $item The product and his parts
     */
    private function computeProduct($item)
    {
        $imageToModify = $this->getImagesToModify($item);
        $this->pruneImages($imageToModify, $item);

        foreach(array_keys($item) as $storeViewCode) {
            $this->createCall($item[$storeViewCode], $storeViewCode, $imageToModify);
        }
    }

    /**
     * Create a call for the given item part
     *
     * @param  array  $itemPart      A product part
     * @param  string $storeViewCode The storeview code
     */
    private function createCall($itemPart, $storeViewCode, $imageToModify)
    {
        if ($storeViewCode == MagentoSoapClient::SOAP_DEFAULT_STORE_VIEW) {
            if (count($itemPart) == 5) {
                $resource = MagentoSoapClient::SOAP_ACTION_CATALOG_PRODUCT_CREATE;
            } else {
                $resource = MagentoSoapClient::SOAP_ACTION_CATALOG_PRODUCT_UPDATE;
            }

            $this->magentoSoapClient->addCall(
                array(
                    $resource,
                    $itemPart,
                ),
                self::MAXIMUM_CALLS
            );
        } elseif ($storeViewCode == MagentoSoapClient::IMAGES) {
            foreach ($itemPart as $imageCall) {
                $pimImageFilename = $imageCall[1]['file']['name'];

                if (
                    $magentoImageFilename =
                        $this->imageHasToBeUpdated($pimImageFilename, $imageToModify['toUpdate'])
                ) {
                    $this->magentoSoapClient->deleteImage($magentoImageFilename, $itemPart[0][0]);
                }

                $this->magentoSoapClient->addCall(
                    array(
                        MagentoSoapClient::SOAP_ACTION_PRODUCT_MEDIA_CREATE,
                        $imageCall
                    ),
                    self::MAXIMUM_CALLS
                );
            }
        } else {
            $this->magentoSoapClient->addCall(
                array(
                    MagentoSoapClient::SOAP_ACTION_CATALOG_PRODUCT_UPDATE,
                    $itemPart,
                ),
                self::MAXIMUM_CALLS
            );
        }
    }

    protected function isSameImage($pimFilename, $magentoFilename)
    {
        $pimFilename = str_replace(' ', '_', $pimFilename);
        $pimFilename = str_replace(':', '_', $pimFilename);

        return ($pimFilename && $magentoFilename) && (false !== strpos($magentoFilename, $pimFilename));
    }

    protected function imageHasToBeDeleted($magentoImage, $pimImages)
    {
        foreach ($pimImages as $pimImage) {
            $pimImageFilename = $pimImage[1]['file']['name'];

            if ($this->isSameImage($pimImageFilename, $magentoImage['file'])) {
                return false;
            }
        }

        return true;
    }

    protected function imageHasToBeUpdated($pimImageFilename, $toUpdateImages)
    {
        foreach ($toUpdateImages as $toUpdateImage) {
            if ($this->isSameImage($pimImageFilename, $toUpdateImage)) {
                return $toUpdateImage;
            }
        }

        return false;
    }

    protected function getImagesToModify($item)
    {
        if (!isset($item['default'][0])) {
            var_dump($item);
        }
        $magentoImages = $this->magentoSoapClient->getImages($item['default'][0]);
        $pimImages     = $item[MagentoSoapClient::IMAGES];

        $imagesToModify = array(
            'toDelete' => array(),
            'toUpdate' => array()
        );

        foreach ($magentoImages as $magentoImage) {
            if ($this->imageHasToBeDeleted($magentoImage ,$pimImages)) {
                $imagesToModify['toDelete'][] = $magentoImage['file'];
            } else {
                $imagesToModify['toUpdate'][] = $magentoImage['file'];
            }
        }

        return $imagesToModify;
    }

    protected function pruneImages($imagesToModify, $item)
    {
        foreach ($imagesToModify['toDelete'] as $imageToDelete) {
            $this->magentoSoapClient->deleteImage($imageToDelete, (string) $item['default'][0]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return array(
            'soapUsername' => array(),
            'soapApiKey'   => array(
                //Should be remplaced by a password formType but who doesn't
                //empty the field at each edit
                'type' => 'text'
            ),
            'soapUrl'      => array(),
            'channel'      => array(
                'type'    => 'choice',
                'options' => array(
                    'choices'  => $this->channelManager->getChannelChoices(),
                    'required' => true
                )
            ),
        );
    }
}
