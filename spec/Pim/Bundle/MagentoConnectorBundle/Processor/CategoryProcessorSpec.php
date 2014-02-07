<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Processor;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Pim\Bundle\CatalogBundle\Entity\Category;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\MagentoConnectorBundle\Manager\LocaleManager;
use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\MagentoConnectorBundle\Guesser\NormalizerGuesser;
use Pim\Bundle\MagentoConnectorBundle\Manager\CategoryMappingManager;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\AbstractNormalizer;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\CategoryNormalizer;
use Pim\Bundle\MagentoConnectorBundle\Webservice\Webservice;

class CategoryProcessorSpec extends ObjectBehavior
{
    function let(
        ChannelManager $channelManager,
        LocaleManager $localeManager,
        WebserviceGuesser $webserviceGuesser,
        NormalizerGuesser $normalizerGuesser,
        CategoryMappingManager $categoryMappingManager,
        Webservice $webservice,
        CategoryNormalizer $categoryNormalizer
    ) {
        $this->beConstructedWith(
            $webserviceGuesser,
            $normalizerGuesser,
            $localeManager,
            $categoryMappingManager
        );

        $webserviceGuesser->getWebservice(Argument::any())->willReturn($webservice);

        $normalizerGuesser->getCategoryNormalizer(Argument::any(), Argument::any())->willReturn($categoryNormalizer);

        $this->setCategoryMapping("test:4\n");
    }

    function it_normalizes_categories(
        Category $category,
        Category $parentCategory,
        $webservice,
        $categoryNormalizer
    ) {
        $webservice->getCategoriesStatus()->willReturn(array(
            1 => array(
                'category_id' => 1
            )
        ));

        $webservice->getStoreViewsList()->willReturn(array(
            array(
                'store_id' => 10,
                'code'     => 'fr_fr'
            )
        ));

        $category->getParent()->willReturn($parentCategory);

        $categoryNormalizer->normalize(
            $category,
            AbstractNormalizer::MAGENTO_FORMAT,
            Argument::any()
        )->willReturn(array(
            'create'    => array(),
            'update'    => array(),
            'move'      => array(),
            'variation' => array()
        ));

        $this->process($category)->shouldReturn(array(
            'create'    => array(),
            'update'    => array(),
            'move'      => array(),
            'variation' => array()
        ));
    }
}
