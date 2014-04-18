<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Cleaner;

use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesserFactory;
use Pim\Bundle\MagentoConnectorBundle\Manager\CategoryMappingManager;
use Pim\Bundle\MagentoConnectorBundle\Webservice\CategoryWebservice;
use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CategoryCleanerSpec extends ObjectBehavior
{
    function let(
        WebserviceGuesserFactory $webserviceGuesserFactory,
        CategoryMappingManager $categoryMappingManager,
        CategoryWebservice $categoryWebservice,
        StepExecution $stepExecution
    ) {
        $this->beConstructedWith($webserviceGuesserFactory, $categoryMappingManager);
        $this->setStepExecution($stepExecution);

        $webserviceGuesserFactory->getWebservice('category', Argument::cetera())->willReturn($categoryWebservice);
    }

    function it_asks_soap_client_to_delete_categories_that_are_not_in_pim_anymore($categoryWebservice, $categoryMappingManager)
    {
        $this->setNotInPimAnymoreAction('delete');

        $categoryWebservice->getCategoriesStatus()->willReturn(
            array(
                array('category_id' => '1'),
                array('category_id' => '12', 'level' => '0'),
                array('category_id' => '13', 'level' => '2')
            )
        );

        $categoryMappingManager->magentoCategoryExists('1', Argument::cetera())->willReturn(true);
        $categoryMappingManager->magentoCategoryExists('12', Argument::cetera())->willReturn(false);
        $categoryMappingManager->magentoCategoryExists('13', Argument::cetera())->willReturn(false);

        $categoryWebservice->deleteCategory('13')->shouldBeCalled();

        $this->execute();
    }

    function it_asks_soap_client_to_disable_categories_that_are_not_in_pim_anymore($categoryWebservice, $categoryMappingManager)
    {
        $this->setNotInPimAnymoreAction('disable');

        $categoryWebservice->getCategoriesStatus()->willReturn(
            array(
                array('category_id' => '13', 'level' => '2')
            )
        );

        $categoryMappingManager->magentoCategoryExists('13', Argument::cetera())->willReturn(false);

        $categoryWebservice->disableCategory('13')->shouldBeCalled();

        $this->execute();
    }

    function it_raises_invalid_item_exception_if_something_goes_wrong_with_the_soap_api($categoryWebservice, $categoryMappingManager)
    {
        $this->setNotInPimAnymoreAction('disable');

        $categoryWebservice->getCategoriesStatus()->willReturn(
            array(
                array('category_id' => '13', 'level' => '2')
            )
        );

        $categoryMappingManager->magentoCategoryExists('13', Argument::cetera())->willReturn(false);

        $categoryWebservice->disableCategory('13')->willThrow('Pim\Bundle\MagentoConnectorBundle\Webservice\Exception\SoapCallException');

        $this->shouldThrow('Akeneo\Bundle\BatchBundle\Item\InvalidItemException')->during('execute');
    }
}
