<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Cleaner;

use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\MagentoConnectorBundle\Manager\CategoryMappingManager;
use Pim\Bundle\MagentoConnectorBundle\Webservice\Webservice;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CategoryCleanerSpec extends ObjectBehavior
{
    function let(
        WebserviceGuesser $webserviceGuesser,
        CategoryMappingManager $categoryMappingManager,
        Webservice $webservice
    ) {
        $this->beConstructedWith($webserviceGuesser, $categoryMappingManager);

        $webserviceGuesser->getWebservice(Argument::cetera())->willReturn($webservice);
    }

    function it_asks_soap_client_to_delete_categories_that_are_not_in_pim_anymore($webservice, $categoryMappingManager)
    {
        $this->setNotInPimAnymoreAction('delete');

        $webservice->getCategoriesStatus()->willReturn(
            array(
                array('category_id' => '1'),
                array('category_id' => '12', 'level' => '0'),
                array('category_id' => '13', 'level' => '2')
            )
        );

        $categoryMappingManager->magentoCategoryExists('1', Argument::cetera())->willReturn(true);
        $categoryMappingManager->magentoCategoryExists('12', Argument::cetera())->willReturn(false);
        $categoryMappingManager->magentoCategoryExists('13', Argument::cetera())->willReturn(false);

        $webservice->deleteCategory('13')->shouldBeCalled();

        $this->execute();
    }

    function it_asks_soap_client_to_disable_categories_that_are_not_in_pim_anymore($webservice, $categoryMappingManager)
    {
        $this->setNotInPimAnymoreAction('disable');

        $webservice->getCategoriesStatus()->willReturn(
            array(
                array('category_id' => '13', 'level' => '2')
            )
        );

        $categoryMappingManager->magentoCategoryExists('13', Argument::cetera())->willReturn(false);

        $webservice->disableCategory('13')->shouldBeCalled();

        $this->execute();
    }
}
