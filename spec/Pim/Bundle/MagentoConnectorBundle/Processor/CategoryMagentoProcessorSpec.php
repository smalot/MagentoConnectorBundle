<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Processor;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Pim\Bundle\CatalogBundle\Entity\Category;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\MagentoConnectorBundle\Guesser\NormalizerGuesser;
use Pim\Bundle\MagentoConnectorBundle\Manager\CategoryMappingManager;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\AbstractNormalizer;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\CategoryNormalizer;
use Pim\Bundle\MagentoConnectorBundle\Webservice\Webservice;

class CategoryMagentoProcessorSpec extends ObjectBehavior
{
    public function let(
        ChannelManager $channelManager,
        WebserviceGuesser $webserviceGuesser,
        NormalizerGuesser $normalizerGuesser,
        CategoryMappingManager $categoryMappingManager,
        Webservice $webservice,
        CategoryNormalizer $categoryNormalizer
    ) {
        $this->beConstructedWith(
            $channelManager,
            $webserviceGuesser,
            $normalizerGuesser,
            $categoryMappingManager
        );

        $webserviceGuesser->getWebservice(Argument::any())->willReturn($webservice);

        $normalizerGuesser->getCategoryNormalizer(Argument::any(), Argument::any())
            ->willReturn($categoryNormalizer);

        $this->setRootCategoryMapping("test:4\n");
    }

    public function it_normalize_categories(
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
            array(
                'magentoCategories'   => array(1 => array(1)),
                'magentoUrl'          => null,
                'defaultLocale'       => null,
                'channel'             => null,
                'rootCategoryMapping' => array('4'),
                'magentoStoreViews'   => array(array('store_id' => 10, 'code' => 'fr_fr')),
                'storeViewMapping'    => array()
            )
        )->willReturn(array(
            'create'    => array(),
            'update'    => array(),
            'move'      => array(),
            'variation' => array()
        ));

        $this->process($category);
    }
}
