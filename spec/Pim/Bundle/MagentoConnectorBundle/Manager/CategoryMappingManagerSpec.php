<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\CatalogBundle\Entity\Category;
use Doctrine\ORM\EntityRepository;
use Pim\Bundle\MagentoConnectorBundle\Entity\MagentoCategoryMapping;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CategoryMappingManagerSpec extends ObjectBehavior
{
    function let(ObjectManager $objectManager, EntityRepository $entityRepository)
    {
        $this->beConstructedWith($objectManager, 'Pim\Bundle\MagentoConnectorBundle\Entity\MagentoCategoryMapping');
        $objectManager->getRepository('Pim\Bundle\MagentoConnectorBundle\Entity\MagentoCategoryMapping')
            ->willReturn($entityRepository);
    }

    function it_gets_id_from_root_category_mapping(Category $category)
    {
        $categoryMapping = array(
            'default' => 12
        );

        $category->getCode()->willReturn('default');

        $this->getIdFromCategory($category, '', $categoryMapping)->shouldReturn(12);
    }

    function it_gets_id_from_category_mapping_stored_in_database(Category $category, $entityRepository, MagentoCategoryMapping $categoryMapping)
    {
        $entityRepository->findOneBy(
            array(
                'category'   => $category,
                'magentoUrl' => ''
            )
        )->willReturn($categoryMapping);

        $categoryMapping->getMagentoCategoryId()->willReturn(13);

        $categoryMapping = array(
            'default' => 12
        );

        $category->getCode()->willReturn('colors');

        $this->getIdFromCategory($category, '', $categoryMapping)->shouldReturn(13);
    }

    function it_returns_null_if_category_is_not_found(Category $category, $entityRepository)
    {
        $entityRepository->findOneBy(
            array(
                'category'   => $category,
                'magentoUrl' => ''
            )
        )->willReturn(null);

        $categoryMapping = array(
            'default' => 12
        );

        $category->getCode()->willReturn('colors');

        $this->getIdFromCategory($category, '', $categoryMapping)->shouldReturn(null);
    }
}
