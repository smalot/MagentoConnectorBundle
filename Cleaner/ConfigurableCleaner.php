<?php

namespace Pim\Bundle\MagentoConnectorBundle\Cleaner;

use Symfony\Component\Validator\Constraints as Assert;
use Pim\Bundle\MagentoConnectorBundle\Validator\Constraints\HasValidCredentials;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\AbstractNormalizer;
use Pim\Bundle\MagentoConnectorBundle\Manager\GroupManager;

/**
 * Magento configurable cleaner
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @HasValidCredentials()
 */
class ConfigurableCleaner extends ProductCleaner
{
    /**
     * @var GroupManager
     */
    protected $groupManager;

    /**
     * @param ChannelManager    $channelManager
     * @param WebserviceGuesser $webserviceGuesser
     * @param ProductManager    $productManager
     * @param GroupManager      $groupManager
     */
    public function __construct(
        ChannelManager $channelManager,
        WebserviceGuesser $webserviceGuesser,
        ProductManager $productManager,
        GroupManager $groupManager
    ) {
        parent::__construct($channelManager, $webserviceGuesser, $productManager);

        $this->groupManager = $groupManager;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        parent::beforeExecute();

        $magentoProducts  = $this->webservice->getProductsStatus();
        $pimConfigurables = $this->getPimConfigurablesSkus();

        foreach ($magentoProducts as $product) {
            if ($product['type'] === AbstractNormalizer::MAGENTO_CONFIGURABLE_PRODUCT_KEY &&
                !in_array($product['sku'], $pimConfigurables)
            ) {
                $this->handleProductNotInPimAnymore($product);
            }
        }
    }

    /**
     * Get all variant group skus
     * @return array
     */
    protected function getPimConfigurablesSkus()
    {
        return $this->groupManager->getRepository()->getVariantGroupSkus();
    }
}
