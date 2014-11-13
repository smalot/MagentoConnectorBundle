<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Normalizer;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\Category;
use Pim\Bundle\CatalogBundle\Entity\Repository\CategoryRepository;
use Pim\Bundle\CatalogBundle\Manager\CategoryManager;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;

class ProductCategoryNormalizerSpec extends ObjectBehavior
{
    public function let(CategoryManager $categoryManager)
    {
        $this->beConstructedWith($categoryManager);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType('\Pim\Bundle\MagentoConnectorBundle\Normalizer\ProductCategoryNormalizer');
    }

    public function it_returns_true_if_the_normalizer_can_support_given_data(CategoryInterface $category)
    {
        $this->supportsNormalization($category, 'api_import')->shouldReturn(true);
    }

    public function it_returns_false_if_the_normalizer_can_not_support_given_data($object)
    {
        $this->supportsNormalization($object, 'api_import')->shouldReturn(false);
    }

    public function it_returns_false_if_the_normalizer_can_not_support_given_data_because_of_the_format(
        CategoryInterface $category
    ) {
        $this->supportsNormalization($category, 'foo_bar')->shouldReturn(false);
    }

    public function it_normalizes_a_product_category_root(Category $category)
    {
        $context = ['defaultLocale' => 'en_US'];

        $category->isRoot()->willReturn(true);
        $category->setLocale('en_US')->willReturn($category);
        $category->getLabel()->willReturn('my_category_root');

        $this->normalize($category, 'api_import', $context)->shouldReturn([
            'category' => '',
            'root' => 'my_category_root'
        ]);
    }

    public function it_normalizes_a_product_category(
        Category $category,
        Category $category2,
        Category $categoryRoot,
        CategoryRepository $repo,
        $categoryManager
    ) {
        $context = ['defaultLocale' => 'en_US'];

        $category->isRoot()->willReturn(false);
        $category->setLocale('en_US')->willReturn($category);
        $category->getLabel()->willReturn('my_category');

        $categoryRoot->setLocale('en_US')->willReturn($categoryRoot);
        $categoryRoot->getLabel()->willReturn('my_category_root');

        $category2->setLocale('en_US')->willReturn($category2);
        $category2->getLabel()->willReturn('my_category_2');

        $categoryManager->getEntityRepository()->willReturn($repo);
        $repo->getPath($category)->willReturn([$categoryRoot, $category2, $category]);

        $this->normalize($category, 'api_import', $context)->shouldReturn([
            'category' => 'my_category_2/my_category',
            'root' => 'my_category_root'
        ]);
    }
}
