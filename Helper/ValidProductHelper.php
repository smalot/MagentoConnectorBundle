<?php

namespace Pim\Bundle\MagentoConnectorBundle\Helper;

use Doctrine\Common\Collections\Collection;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Entity\Channel;

/**
 * Valid product helper
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ValidProductHelper
{
    /**
     * Provides a method to find ready to export products from an array of products
     *
     * @param Channel                       $channel
     * @param ProductInterface[]|Collection $products
     *
     * @return \Pim\Bundle\CatalogBundle\Model\ProductInterface[]
     */
    public function getValidProducts(Channel $channel, $products)
    {
        $validProducts  = [];
        $rootCategoryId = $channel->getCategory()->getId();

        foreach ($products as $product) {
            $isComplete = true;
            $completenesses = $product->getCompletenesses()->getIterator();
            while ((list($key, $completeness) = each($completenesses)) && $isComplete) {
                if ($completeness->getChannel()->getId() === $channel->getId() &&
                    $completeness->getRatio() < 100
                ) {
                    $isComplete = false;
                }
            }

            $productCategories = $product->getCategories()->getIterator();
            if ($isComplete && false !== $productCategories) {
                $isInChannel = false;
                while ((list($key, $category) = each($productCategories)) && !$isInChannel) {
                    if ($category->getRoot() === $rootCategoryId) {
                        $isInChannel = true;
                        $validProducts[] = $product;
                    }
                }
            }
        }

        return $validProducts;
    }
}
