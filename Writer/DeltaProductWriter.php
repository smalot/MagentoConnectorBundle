<?php

namespace Pim\Bundle\MagentoConnectorBundle\Writer;

use Symfony\Component\Validator\Constraints as Assert;
use Pim\Bundle\MagentoConnectorBundle\Webservice\Webservice;
use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\MagentoConnectorBundle\Webservice\SoapCallException;
use Akeneo\Bundle\BatchBundle\Item\InvalidItemException;
use Pim\Bundle\DeltaExportBundle\Manager\ProductExportManager;
use Akeneo\Bundle\BatchBundle\Entity\StepExecution;

/**
 * Magento product writer
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DeltaProductWriter extends ProductWriter
{
    /**
     * @var ProductExportManager
     */
    protected $productExportManager;

    /**
     * @var JobInstance
     */
    protected $jobInstance;

    /**
     * Constructor
     *
     * @param WebserviceGuesser $webserviceGuesser
     * @param ChannelManager    $channelManager
     */
    public function __construct(
        WebserviceGuesser $webserviceGuesser,
        ChannelManager $channelManager,
        ProductExportManager $productExportManager
    ) {
        parent::__construct($webserviceGuesser, $channelManager);

        $this->productExportManager = $productExportManager;
    }

    /**
     * Compute an individual product and all his parts (translations)
     *
     * @param array $product The product and his parts
     */
    protected function computeProduct($product)
    {
        $sku = $this->getProductSku($product);

        parent::computeProduct($product);

        $this->productExportManager->updateProductExport($sku, $this->jobInstance);
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        parent::setStepExecution($stepExecution);

        $this->jobInstance = $stepExecution->getJobExecution()->getJobInstance();
    }
}
