<?php

namespace Pim\Bundle\MagentoConnectorBundle\Cleaner;

use Symfony\Component\Validator\Constraints as Assert;
use Pim\Bundle\MagentoConnectorBundle\Validator\Constraints\HasValidCredentials;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesserFactory;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\MagentoConnectorBundle\Webservice\SoapCallException;
use Akeneo\Bundle\BatchBundle\Item\InvalidItemException;

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
    const PRODUCT_DISABLED = 'Product disabled';
    const PRODUCT_DELETED  = 'Product deleted';

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
     * @param WebserviceGuesserFactory $webserviceGuesserFactory
     * @param ChannelManager           $channelManager
     * @param ProductManager           $productManager
     */
    public function __construct(
        WebserviceGuesserFactory $webserviceGuesserFactory,
        ChannelManager           $channelManager,
        ProductManager           $productManager
    ) {
        parent::__construct($webserviceGuesserFactory);

        $this->channelManager = $channelManager;
        $this->productManager = $productManager;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {

        $magentoProducts  = $this->webserviceGuesserFactory
            ->getWebservice('product', $this->getClientParameters())->getProductsStatus();
        $exportedProducts = $this->getExportedProductsSkus();
        $pimProducts      = $this->getPimProductsSkus();

        foreach ($magentoProducts as $product) {
            try {
                if (!in_array($product['sku'], $pimProducts)) {
                    $this->handleProductNotInPimAnymore($product);
                } elseif (!in_array($product['sku'], $exportedProducts)) {
                    $this->handleProductNotCompleteAnymore($product);
                }
            } catch (SoapCallException $e) {
                throw new InvalidItemException($e->getMessage(), array(json_encode($product)));
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
            $this->webserviceGuesserFactory
                ->getWebservice('product', $this->getClientParameters())->disableProduct($product['sku']);
            $this->stepExecution->incrementSummaryInfo(self::PRODUCT_DISABLED);
        } elseif ($action === self::DELETE) {
            $this->webserviceGuesserFactory
                ->getWebservice('product', $this->getClientParameters())->deleteProduct($product['sku']);
            $this->stepExecution->incrementSummaryInfo(self::PRODUCT_DELETED);
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
                            Cleaner::DO_NOTHING => 'pim_magento_connector.export.do_nothing.label',
                            Cleaner::DISABLE    => 'pim_magento_connector.export.disable.label',
                            Cleaner::DELETE     => 'pim_magento_connector.export.delete.label'
                        ),
                        'required' => true,
                        'help'     => 'pim_magento_connector.export.notCompleteAnymoreAction.help',
                        'label'    => 'pim_magento_connector.export.notCompleteAnymoreAction.label'
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
