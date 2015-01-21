<?php

namespace Pim\Bundle\MagentoConnectorBundle\Helper;

use Doctrine\Common\Collections\Collection;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Entity\Channel;

/**
 * Exportable product helper
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ExportableProductHelper
{
    /**
     * Provides a method to find ready to export products from an array of products
     *
     * @param Channel                       $channel
     * @param ProductInterface[]|Collection $products
     *
     * @return \Pim\Bundle\CatalogBundle\Model\ProductInterface[]
     */
    public function getExportableProducts(Channel $channel, $products)
    {
        $exportableProducts  = [];
        $rootCategoryId = $channel->getCategory()->getId();

        foreach ($products as $product) {
            $isProductComplete = $this->isProductComplete($product, $channel);

            $productCategories = $product->getCategories()->toArray();
            if ($isProductComplete &&
                false !== $productCategories &&
                $this->doesProductBelongToChannel($productCategories, $rootCategoryId)
            ) {
                $exportableProducts[] = $product;
            }
        }

        return $exportableProducts;
    }

    /**
     * Is the given product is complete for the given channel ?
     *
     * @param ProductInterface $product
     * @param Channel          $channel
     *
     * @return bool
     */
    protected function isProductComplete(ProductInterface $product, Channel $channel)
    {
        $isComplete = true;
        $completenesses = $product->getCompletenesses()->toArray();
        while ((list($key, $completeness) = each($completenesses)) && $isComplete) {
            if ($completeness->getChannel()->getId() === $channel->getId() &&
                $completeness->getRatio() < 100
            ) {
                $isComplete = false;
            }
        }

        return $isComplete;
    }

    /**
     * Compute the belonging of a product to a channel
     * validating one of its categories has the same root as the channel root category
     *
     * @param \ArrayIterator|array $productCategories
     * @param int                  $channelRootCategoryId
     *
     * @return bool
     */
    protected function doesProductBelongToChannel($productCategories, $channelRootCategoryId)
    {
        $isInChannel = false;
        while ((list($key, $category) = each($productCategories)) && !$isInChannel) {
            if ($category->getRoot() === $channelRootCategoryId) {
                $isInChannel = true;
            }
        }

        return $isInChannel;
    }
}
