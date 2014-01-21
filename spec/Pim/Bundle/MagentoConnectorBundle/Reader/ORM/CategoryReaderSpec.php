<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Reader\ORM;

use PhpSpec\ObjectBehavior;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\AbstractQuery;
use Oro\Bundle\BatchBundle\Entity\StepExecution;
use Pim\Bundle\MagentoConnectorBundle\Entity\Repository\CategoryRepository;

class CategoryReaderSpec extends ObjectBehavior
{
    const CATEGORY_CLASS = 'Pim\Bundle\CatalogBundle\Entity\Category';

    public function let(
        EntityManager $em,
        CategoryRepository $repository,
        StepExecution $stepExecution
    ) {
        $this->beConstructedWith($em, self::CATEGORY_CLASS, $repository);
        $this->setStepExecution($stepExecution);
    }

    public function it_reads_categories_with_custom_order_query(
        $repository,
        QueryBuilder $qb,
        AbstractQuery $query
    ) {
        $repository->findOrderedCategories()->willReturn($qb);
        $qb->getQuery()->willReturn($query);
        $query->execute()->willReturn(array('foo', 'bar'));

        $this->read()->shouldReturn('foo');
        $this->read()->shouldReturn('bar');
        $this->read()->shouldReturn(null);
    }
}
