<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Writer;

use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\MagentoConnectorBundle\Guesser\MagentoWebserviceGuesser;
use Pim\Bundle\MagentoConnectorBundle\Manager\CategoryMappingManager;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoWebservice;
use Pim\Bundle\CatalogBundle\Entity\Category;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CategoryMagentoWriterSpec extends ObjectBehavior
{
    public function let(
        ChannelManager $channelManager,
        MagentoWebserviceGuesser $magentoWebserviceGuesser,
        CategoryMappingManager $categoryMappingManager,
        MagentoWebservice $magentoWebservice
    ) {
        $magentoWebserviceGuesser->getWebservice(Argument::any())->willReturn($magentoWebservice);

        $this->beConstructedWith($channelManager, $magentoWebserviceGuesser, $categoryMappingManager);
    }

    public function it_sends_categories_to_create_on_magento_webservice(
        Category $category,
        $magentoWebservice,
        $categoryMappingManager
    ) {
        $categories = array(
            array(
                'create' => array(
                    array(
                        'pimCategory'     => $category,
                        'magentoCategory' => array('foo')
                    )
                )
            )
        );

        $magentoWebservice->sendNewCategory(array('foo'))->shouldBeCalled()->willReturn(12);
        $categoryMappingManager->registerCategoryMapping($category, 12, 'bar')->shouldBeCalled();

        $this->setSoapUrl('bar');
        $this->write($categories);
    }
}
