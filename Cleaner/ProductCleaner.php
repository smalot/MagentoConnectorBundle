<?php

namespace Pim\Bundle\MagentoConnectorBundle\Cleaner;

use Symfony\Component\Validator\Constraints as Assert;
use Pim\Bundle\MagentoConnectorBundle\Validator\Constraints\HasValidCredentials;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;

/**
 * Magento product cleaner
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @HasValidCredentials()
 */
class ProductCleaner extends Cleaner
{
    /**
     * @Assert\NotBlank(groups={"Execution"})
     */
    protected $channel;

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
     * @return AbstractProcessor
     */
    public function setChannel($channel)
    {
        $this->channel = $channel;

        return $this;
    }

    /**
     * @var string
     */
    protected $notCompleteAnymoreAction;

    /**
     * get notCompleteAnymoreAction
     *
     * @return string notCompleteAnymoreAction
     */
    public function getNotCompleteAnymoreAction()
    {
        return $this->notCompleteAnymoreAction;
    }

    /**
     * Set notCompleteAnymoreAction
     *
     * @param string $notCompleteAnymoreAction notCompleteAnymoreAction
     *
     * @return ProductCleaner
     */
    public function setNotCompleteAnymoreAction($notCompleteAnymoreAction)
    {
        $this->notCompleteAnymoreAction = $notCompleteAnymoreAction;

        return $this;
    }

    /**
     * @param WebserviceGuesser $webserviceGuesser
     * @param ChannelManager    $channelManager
     * @param ProductManager    $productManager
     */
    public function __construct(
        WebserviceGuesser $webserviceGuesser,
        ChannelManager $channelManager,
        ProductManager $productManager
    ) {
        parent::__construct($webserviceGuesser);

        $this->channelManager = $channelManager;
        $this->productManager = $productManager;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        parent::beforeExecute();

        $magentoProducts  = $this->webservice->getProductsStatus();
        $exportedProducts = $this->getExportedProductsSkus();
        $pimProducts      = $this->getPimProductsSkus();

        foreach ($magentoProducts as $product) {
            if (!in_array($product['sku'], $pimProducts)) {
                $this->handleProductNotInPimAnymore($product);
            } elseif (!in_array($product['sku'], $exportedProducts)) {
                $this->handleProductNotCompleteAnymore($product);
            }
        }
    }

    /**
     * Get all products' skus in channel
     * @return array
     */
    protected function getExportedProductsSkus()
    {
        $products = $this->productManager->getFlexibleRepository()
            ->buildByChannelAndCompleteness($this->channelManager->getChannelByCode($this->channel))
            ->getQuery()
            ->getResult();

        return $this->getProductsSkus($products);
    }

    /**
     * Get all products' skus
     * @return array
     */
    protected function getPimProductsSkus()
    {
        return $this->getProductsSkus($this->productManager->getFlexibleRepository()->findAll());
    }

    /**
     * Get skus for the given products
     * @param array $products
     *
     * @return array
     */
    protected function getProductsSkus(array $products)
    {
        $productsSkus = array();

        foreach ($products as $product) {
            $productsSkus[] = (string) $product->getIdentifier();
        };

        return $productsSkus;
    }

    /**
     * Handle products that are not in pim anymore
     * @param array $product
     */
    protected function handleProductNotInPimAnymore(array $product)
    {
        $this->handleProduct($product, $this->notInPimAnymoreAction);
    }

    /**
     * Handle products that are not in channel anymore
     * @param array $product
     */
    protected function handleProductNotCompleteAnymore(array $product)
    {
        $this->handleProduct($product, $this->notCompleteAnymoreAction);
    }

    /**
     * Handle product for the given action
     * @param array  $product
     * @param string $action
     */
    protected function handleProduct(array $product, $action)
    {
        if ($action === self::DISABLE) {
            $this->webservice->disableProduct($product['sku']);
        } elseif ($action === self::DELETE) {
            $this->webservice->deleteProduct($product['sku']);
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
                'notCompleteAnymoreAction' => array(
                    'type'    => 'choice',
                    'options' => array(
                        'choices'  => array(
                            Cleaner::DO_NOTHING => Cleaner::DO_NOTHING,
                            Cleaner::DISABLE    => Cleaner::DISABLE,
                            Cleaner::DELETE     => Cleaner::DELETE
                        ),
                        'required' => true
                    )
                ),
                'channel'      => array(
                    'type'    => 'choice',
                    'options' => array(
                        'choices'  => $this->channelManager->getChannelChoices(),
                        'required' => true
                    )
                )
            )
        );
    }
}
