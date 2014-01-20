<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Writer;

use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\MagentoConnectorBundle\Manager\CategoryMappingManager;
use Pim\Bundle\MagentoConnectorBundle\Webservice\Webservice;
use Pim\Bundle\CatalogBundle\Entity\Category;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CategoryMagentoWriterSpec extends ObjectBehavior
{
    public function let(
        ChannelManager $channelManager,
        WebserviceGuesser $webserviceGuesser,
        CategoryMappingManager $categoryMappingManager,
        Webservice $webservice
    ) {
        $webserviceGuesser->getWebservice(Argument::any())->willReturn($webservice);

        $this->beConstructedWith($channelManager, $webserviceGuesser, $categoryMappingManager);
    }

    public function it_sends_categories_to_create_on_magento_webservice(
        Category $category,
        $webservice,
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

        $webservice->sendNewCategory(array('foo'))->shouldBeCalled()->willReturn(12);
        $categoryMappingManager->registerCategoryMapping($category, 12, 'bar')->shouldBeCalled();

        $this->setSoapUrl('bar');
        $this->write($categories);
    }
}
