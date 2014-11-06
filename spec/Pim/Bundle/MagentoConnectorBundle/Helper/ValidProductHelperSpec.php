<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Helper;

use Doctrine\Common\Collections\Collection;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use Pim\Bundle\CatalogBundle\Model\Completeness;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;

class ValidProductHelperSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType('\Pim\Bundle\MagentoConnectorBundle\Helper\ValidProductHelper');
    }

    public function it_returns_ready_to_export_products_from_a_product_array(
        ProductInterface $product,
        ProductInterface $productNotComplete,
        ProductInterface $productNotInChannel,
        Channel $channel,
        Collection $completenessColl,
        Collection $categoriesColl,
        Collection $completenessColl2,
        Collection $categoriesColl2,
        Completeness $badCompleteness,
        Completeness $goodCompleteness,
        CategoryInterface $category,
        CategoryInterface $categoryNotInChannel,
        CategoryInterface $categoryRoot
    ) {
        $products = [$product, $productNotComplete, $productNotInChannel];

        $product->getCategories()->willReturn($categoriesColl);
        $product->getCompletenesses()->willReturn($completenessColl);

        $productNotComplete->getCategories()->willReturn($categoriesColl);
        $productNotComplete->getCompletenesses()->willReturn($completenessColl2);

        $productNotInChannel->getCategories()->willReturn($categoriesColl2);
        $productNotInChannel->getCompletenesses()->willReturn($completenessColl);

        $completenessColl->toArray()->willReturn([$goodCompleteness]);
        $completenessColl2->toArray()->willReturn([$goodCompleteness, $badCompleteness]);
        $categoriesColl->toArray()->willReturn([$category]);
        $categoriesColl2->toArray()->willReturn([$categoryNotInChannel]);

        $goodCompleteness->getChannel()->willReturn($channel);
        $goodCompleteness->getRatio()->willReturn(100);
        $badCompleteness->getChannel()->willReturn($channel);
        $badCompleteness->getRatio()->willReturn(50);

        $channel->getCategory()->willReturn($categoryRoot);
        $channel->getId()->willReturn(1);

        $category->getId()->willReturn(42);
        $category->getRoot()->willReturn(2);
        $categoryNotInChannel->getRoot()->willReturn(3);
        $categoryRoot->getId()->willReturn(2);

        $this->getValidProducts($channel, $products)->shouldReturn([$product]);
    }
}
