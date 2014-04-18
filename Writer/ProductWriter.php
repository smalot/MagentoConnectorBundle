<?php

namespace Pim\Bundle\MagentoConnectorBundle\Writer;

use Symfony\Component\Validator\Constraints as Assert;
use Pim\Bundle\MagentoConnectorBundle\Webservice\AbstractWebservice;
use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesserFactory;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\MagentoConnectorBundle\Webservice\Exception\SoapCallException;
use Akeneo\Bundle\BatchBundle\Item\InvalidItemException;

/**
 * Magento product writer
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductWriter extends AbstractWriter
{
    const PRODUCT_SENT             = 'Products sent';
    const PRODUCT_IMAGE_SENT       = 'Products images sent';
    const PRODUCT_TRANSLATION_SENT = 'Products images sent';

    /**
     * @var ChannelManager
     */
    protected $channelManager;

    /**
     * @Assert\NotBlank(groups={"Execution"})
     */
    protected $channel;

    /**
     * Constructor
     *
     * @param WebserviceGuesserFactory $webserviceGuesserFactory
     * @param ChannelManager    $channelManager
     */
    public function __construct(
        WebserviceGuesserFactory $webserviceGuesserFactory,
        ChannelManager $channelManager
    ) {
        parent::__construct($webserviceGuesserFactory);

        $this->channelManager = $channelManager;
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
     *
     * @return AbstractWriter
     */
    public function setChannel($channel)
    {
        $this->channel = $channel;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $products)
    {
        //creation for each product in the admin storeView (with default locale)
        foreach ($products as $batch) {
            foreach ($batch as $product) {
                $this->computeProduct($product);
            }
        }
    }

    /**
     * Compute an individual product and all his parts (translations)
     *
     * @param array $product The product and his parts
     * @throws \Akeneo\Bundle\BatchBundle\Item\InvalidItemException
     */
    protected function computeProduct($product)
    {
        $sku = $this->getProductSku($product);
        $images = $this->webserviceGuesserFactory
            ->getWebservice('product', $this->getClientParameters())->getImages($sku);
        $this->pruneImages($sku, $images);

        foreach (array_keys($product) as $storeViewCode) {
            try {
                $this->createCall($product[$storeViewCode], $storeViewCode);
            } catch (SoapCallException $e) {
                throw new InvalidItemException($e->getMessage(), array($product[$storeViewCode]));
            }
        }

    }

    /**
     * Create a call for the given product part
     *
     * @param array  $productPart   A product part
     * @param string $storeViewCode The storeview code
     */
    protected function createCall($productPart, $storeViewCode)
    {
        switch ($storeViewCode) {
            case AbstractWebservice::SOAP_DEFAULT_STORE_VIEW:
                $this->webserviceGuesserFactory
                    ->getWebservice('product', $this->getClientParameters())->sendProduct($productPart);
                $this->stepExecution->incrementSummaryInfo(self::PRODUCT_SENT);
                break;
            case AbstractWebservice::IMAGES:
                $this->webserviceGuesserFactory
                    ->getWebservice('product', $this->getClientParameters())->sendImages($productPart);
                $this->stepExecution->incrementSummaryInfo(self::PRODUCT_IMAGE_SENT);
                break;
            default:
                $this->webserviceGuesserFactory
                    ->getWebservice('product', $this->getClientParameters())->updateProductPart($productPart);
                $this->stepExecution->incrementSummaryInfo(self::PRODUCT_TRANSLATION_SENT);
        }
    }

    /**
     * Get the sku of the given normalized product
     *
     * @param array $product
     *
     * @return string
     */
    protected function getProductSku($product)
    {
        $defaultStoreviewProduct = $product[AbstractWebservice::SOAP_DEFAULT_STORE_VIEW];

        if (count($defaultStoreviewProduct) == AbstractWebservice::CREATE_PRODUCT_SIZE) {
            return (string) $defaultStoreviewProduct[2];
        } else {
            return (string) $defaultStoreviewProduct[0];
        }
    }

    /**
     * Clean old images on magento product
     *
     * @param string $sku
     * @param array $images
     * @throws \Akeneo\Bundle\BatchBundle\Item\InvalidItemException
     * @throws \Pim\Bundle\MagentoConnectorBundle\Guesser\NotSupportedVersionException
     */
    protected function pruneImages($sku, array $images = array())
    {
        foreach ($images as $image) {
            try {
                $this->webserviceGuesserFactory
                     ->getWebservice('product', $this->getClientParameters())->deleteImage($sku, $image['file']);
            } catch (SoapCallException $e) {
                throw new InvalidItemException($e->getMessage(), $image);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return array_merge(
            parent::getConfigurationFields(),
            array(
                'channel' => array(
                    'type'    => 'choice',
                    'options' => array(
                        'choices'  => $this->channelManager->getChannelChoices(),
                        'required' => true,
                        'help'     => 'pim_magento_connector.export.channel.help',
                        'label'    => 'pim_magento_connector.export.channel.label'
                    )
                )
            )
        );
    }
}
