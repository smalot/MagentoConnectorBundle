<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Normalizer;

use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\Category;
use Pim\Bundle\CatalogBundle\Entity\Repository\CategoryRepository;

class CategoryNormalizerSpec extends ObjectBehavior
{
    function let(CategoryRepository $categoryRepository)
    {
        $this->beConstructedWith($categoryRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('\Pim\Bundle\MagentoConnectorBundle\Normalizer\CategoryNormalizer');
    }

    function it_returns_true_if_the_normalizer_can_support_given_data(Category $category)
    {
        $this->supportsNormalization($category, 'api_import_category')->shouldReturn(true);
    }

    function it_returns_false_if_the_normalizer_can_not_support_given_data($object)
    {
        $this->supportsNormalization($object, 'api_import_category')->shouldReturn(false);
    }

    function it_returns_false_if_the_normalizer_can_not_support_given_data_because_of_the_format(
        Category $category
    ) {
        $this->supportsNormalization($category, 'foo_bar')->shouldReturn(false);
    }

    function it_normalizes_a_category(
        Category $category,
        Category $categoryUS,
        Category $categoryFR,
        Category $parentCategory,
        Category $rootCategory,
        ArrayCollection $categoriesColl,
        $categoryRepository
    ){
        $context = [
            'defaultStoreView'    => 'Default',
            'defaultLocale'       => 'en_US',
            'website'             => 'base',
            'defaultCurrency'     => 'USD',
            'visibility'          => '4',
            'enabled'             => '1',
            'storeViewMapping'    => [
                'fr_FR' => 'fr_fr'
            ],
            'userCategoryMapping' => [
                'Master catalog' => 'Default Category'
            ]
        ];

        $category->getRoot()->willReturn(1);
        $category->setLocale('en_US')->shouldBeCalled()->willReturn($categoryUS);
        $category->setLocale('fr_FR')->shouldBeCalled()->willReturn($categoryFR);
        $categoryUS->getLabel()->willReturn('My category');
        $categoryFR->getLabel()->willReturn('Ma categorie');
        $category->getLeft()->willReturn(4);

        $categoryRepository->getCategoriesByIds([1])->shouldBeCalled()->willReturn($categoriesColl);
        $categoryRepository->getPath($category)->willReturn([$rootCategory, $parentCategory, $category]);
        $categoriesColl->first()->willReturn($rootCategory);

        $rootCategory->setLocale('en_US')->shouldBeCalled()->willReturn($rootCategory);
        $rootCategory->getLabel()->willReturn('Master catalog');

        $parentCategory->setLocale('en_US')->shouldBeCalled()->willReturn($parentCategory);
        $parentCategory->getLabel()->willReturn('Parent Category');

        $this->normalize($category, 'api_import_format', $context)->shouldReturn([
            [
                '_root'             => 'Default Category',
                'name'              => 'My category',
                '_category'         => 'Parent Category/My category',
                'is_active'         => 'yes',
                'position'          => 4,
                'include_in_menu'   => 'yes',
                'available_sort_by' => 'position',
                'default_sort_by'   => 'position'
            ],
            [
                'name'              => 'Ma categorie',
                '_store'            => 'fr_fr',
                '_root'             => 'Default Category'
            ]
        ]);
    }
}
