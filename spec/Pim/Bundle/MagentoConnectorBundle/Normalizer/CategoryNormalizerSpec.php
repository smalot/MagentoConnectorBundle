<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Normalizer;

use Pim\Bundle\CatalogBundle\Entity\CategoryTranslation;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\MagentoConnectorBundle\Manager\CategoryMappingManager;
use Pim\Bundle\MagentoConnectorBundle\Mapper\MappingCollection;
use Pim\Bundle\CatalogBundle\Entity\Category;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CategoryNormalizerSpec extends ObjectBehavior
{
    protected $globalContext;

    function let(
        ChannelManager $channelManager,
        CategoryMappingManager $categoryMappingManager,
        MappingCollection $categoryMapping,
        MappingCollection $storeViewMapping
    ) {
        $this->beConstructedWith($channelManager, $categoryMappingManager);

        $this->globalContext = array(
            'magentoCategories' => array(),
            'magentoUrl'        => 'soap_url',
            'defaultLocale'     => 'default_locale',
            'magentoStoreViews' => array(),
            'categoryMapping'   => $categoryMapping,
            'storeViewMapping'  => $storeViewMapping
        );
    }

    function it_normalizes_a_new_category(Category $category, Category $parentCategory, $categoryMapping, $categoryMappingManager)
    {
        $category->getParent()->willReturn($parentCategory);
        $category->getLabel()->willReturn('category_label');
        $category->setLocale('default_locale')->shouldBeCalled();
        $category->getTranslations()->willReturn(array());
        $category->getCode()->willReturn('category_code');

        $categoryMappingManager->getIdFromCategory($category, 'soap_url')->willReturn(null);
        $categoryMappingManager->getIdFromCategory($parentCategory, 'soap_url', $categoryMapping)->willReturn(3);

        $categoryMapping->getTarget('category_code')->willReturn('category_code');

        $this->normalize($category, 'MagentoArray', $this->globalContext)->shouldReturn(array(
            'create'    => array(
                array(
                    'magentoCategory' => array(
                        '3',
                        array(
                            'name'              => 'category_label',
                            'is_active'         => 1,
                            'include_in_menu'   => 1,
                            'available_sort_by' => 1,
                            'default_sort_by'   => 1,
                        ),
                        'default'
                    ),
                    'pimCategory' => $category
                )
            ),
            'update'    => array(),
            'move'      => array(),
            'variation' => array()
        ));
    }

    function it_normalizes_a_updated_category(Category $category, Category $parentCategory, $categoryMapping, $categoryMappingManager)
    {
        $this->globalContext = array_merge(
            $this->globalContext,
            array(
                'magentoCategories' => array(
                    4 => array('parent_id' => 3)
                )
            )
        );

        $category->getParent()->willReturn($parentCategory);
        $category->getLabel()->willReturn('category_label');
        $category->setLocale('default_locale')->shouldBeCalled();
        $category->getTranslations()->willReturn(array());
        $category->getCode()->willReturn('category_code');

        $categoryMappingManager->getIdFromCategory($category, 'soap_url')->willReturn(4);
        $categoryMappingManager->getIdFromCategory($parentCategory, 'soap_url', $categoryMapping)->willReturn(3);
        $categoryMappingManager->getIdFromCategory($parentCategory, 'soap_url')->willReturn(3);

        $categoryMapping->getTarget('category_code')->willReturn('category_code');

        $this->normalize($category, 'MagentoArray', $this->globalContext)->shouldReturn(array(
            'create'    => array(),
            'update'    => array(
                array(
                    4,
                    array(
                        'name'              => 'category_label',
                        'available_sort_by' => 1,
                        'default_sort_by'   => 1,
                    ),
                    'default'
                )
            ),
            'move'      => array(),
            'variation' => array()
        ));
    }

    function it_normalizes_a_updated_category_who_have_moved(Category $category, Category $parentCategory, $categoryMapping, $categoryMappingManager)
    {
        $this->globalContext = array_merge(
            $this->globalContext,
            array(
                'magentoCategories' => array(
                    4 => array('parent_id' => 5)
                )
            )
        );

        $category->getParent()->willReturn($parentCategory);
        $category->getLabel()->willReturn('category_label');
        $category->setLocale('default_locale')->shouldBeCalled();
        $category->getTranslations()->willReturn(array());
        $category->getCode()->willReturn('category_code');

        $categoryMappingManager->getIdFromCategory($category, 'soap_url')->willReturn(4);
        $categoryMappingManager->getIdFromCategory($parentCategory, 'soap_url', $categoryMapping)->willReturn(3);
        $categoryMappingManager->getIdFromCategory($parentCategory, 'soap_url')->willReturn(3);

        $categoryMapping->getTarget('category_code')->willReturn('category_code');

        $this->normalize($category, 'MagentoArray', $this->globalContext)->shouldReturn(array(
            'create'    => array(),
            'update'    => array(
                array(
                    4,
                    array(
                        'name'              => 'category_label',
                        'available_sort_by' => 1,
                        'default_sort_by'   => 1,
                    ),
                    'default'
                )
            ),
            'move'      => array(
                array(
                    4,
                    3
                )
            ),
            'variation' => array()
        ));
    }

    function it_normalizes_category_variations(Category $category, Category $parentCategory, CategoryTranslation $translation, $categoryMapping, $storeViewMapping, $categoryMappingManager)
    {
        $this->globalContext = array_merge(
            $this->globalContext,
            array(
                'magentoStoreViews' => array(
                    array('code' => 'fr_fr')
                )
            )
        );

        $category->getParent()->willReturn($parentCategory);
        $category->getLabel()->willReturn('category_label');
        $category->setLocale('default_locale')->shouldBeCalled();
        $category->getTranslations()->willReturn(array($translation));
        $category->getCode()->willReturn('category_code');

        $translation->getLocale()->willReturn('fr_FR');

        $storeViewMapping->getTarget('fr_FR')->willReturn('fr_fr');
        $category->setLocale('fr_FR')->shouldBeCalled();
        $category->getLabel()->willReturn('Libélé de la catégorie');


        $categoryMappingManager->getIdFromCategory($category, 'soap_url')->willReturn(null);
        $categoryMappingManager->getIdFromCategory($parentCategory, 'soap_url', $categoryMapping)->willReturn(3);

        $categoryMapping->getTarget('category_code')->willReturn('category_code');

        $this->normalize($category, 'MagentoArray', $this->globalContext)->shouldReturn(array(
            'create'    => array(
                array(
                    'magentoCategory' => array(
                        '3',
                        array(
                            'name'              => 'Libélé de la catégorie',
                            'is_active'         => 1,
                            'include_in_menu'   => 1,
                            'available_sort_by' => 1,
                            'default_sort_by'   => 1,
                        ),
                        'default'
                    ),
                    'pimCategory' => $category
                )
            ),
            'update'    => array(),
            'move'      => array(),
            'variation' => array(
                array(
                    'magentoCategory' => array(
                        null,
                        array(
                            'name'              => 'Libélé de la catégorie',
                            'available_sort_by' => 1,
                            'default_sort_by'   => 1,
                        ),
                        'fr_fr'
                    ),
                    'pimCategory' => $category
                )
            )
        ));
    }
}
