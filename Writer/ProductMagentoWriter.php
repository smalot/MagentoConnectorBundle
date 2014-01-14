<?php

namespace Pim\Bundle\MagentoConnectorBundle\Writer;

use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoWebservice;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientParameters;
use Pim\Bundle\MagentoConnectorBundle\Validator\Constraints\HasValidCredentials;

/**
 * Magento product writer
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @HasValidCredentials()
 */
class ProductMagentoWriter extends AbstractMagentoWriter
{
    /**
     * {@inheritdoc}
     */
    public function write(array $products)
    {
        $this->magentoWebservice = $this->magentoWebserviceGuesser->getWebservice($this->getClientParameters());

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
     */
    protected function computeProduct($product)
    {
        $this->pruneImages($product);

        foreach (array_keys($product) as $storeViewCode) {
            $this->createCall($product[$storeViewCode], $storeViewCode);
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
            case MagentoWebservice::SOAP_DEFAULT_STORE_VIEW:
                $this->magentoWebservice->sendProduct($productPart);
                break;
            case MagentoWebservice::IMAGES:
                $this->magentoWebservice->sendImages($productPart);
                break;
            default:
                $this->magentoWebservice->updateProductPart($productPart);
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
        $defaultStoreviewProduct = $product[MagentoWebservice::SOAP_DEFAULT_STORE_VIEW];

        if (count($defaultStoreviewProduct) == MagentoWebservice::CREATE_PRODUCT_SIZE) {
            return (string) $defaultStoreviewProduct[2];
        } else {
            return (string) $defaultStoreviewProduct[0];
        }
    }

    /**
     * Clean old images on magento product
     *
     * @param array $product
     */
    protected function pruneImages($product)
    {
        $sku = $this->getProductSku($product);
        $images = $this->magentoWebservice->getImages($sku);

        foreach ($images as $image) {
            $this->magentoWebservice->deleteImage($sku, $image['file']);
        }
    }

    /**
     * Get the magento soap client parameters
     *
     * @return MagentoSoapClientParameters
     */
    protected function getClientParameters()
    {
        if (!$this->clientParameters) {
            $this->clientParameters = new MagentoSoapClientParameters(
                $this->soapUsername,
                $this->soapApiKey,
                $this->soapUrl
            );
        }

        return $this->clientParameters;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return array(
            'soapUsername' => array(
                'options' => array(
                    'required' => true
                )
            ),
            'soapApiKey'   => array(
                //Should be remplaced by a password formType but who doesn't
                //empty the field at each edit
                'type'    => 'text',
                'options' => array(
                    'required' => true
                )
            ),
            'soapUrl'      => array(
                'options' => array(
                    'required' => true
                )
            ),
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
