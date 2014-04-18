<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Processor;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Pim\Bundle\CatalogBundle\Entity\Category;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\MagentoConnectorBundle\Manager\LocaleManager;
use Pim\Bundle\MagentoConnectorBundle\Merger\MappingMerger;
use Pim\Bundle\MagentoConnectorBundle\Mapper\MappingCollection;
use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesserFactory;
use Pim\Bundle\MagentoConnectorBundle\Webservice\StoreViewsWebservice;
use Pim\Bundle\MagentoConnectorBundle\Guesser\NormalizerGuesser;
use Pim\Bundle\MagentoConnectorBundle\Manager\CategoryMappingManager;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\AbstractNormalizer;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\CategoryNormalizer;
use Pim\Bundle\MagentoConnectorBundle\Webservice\CategoryWebservice;
use Akeneo\Bundle\BatchBundle\Entity\StepExecution;

class CategoryProcessorSpec extends ObjectBehavior
{
    function let(
        ChannelManager $channelManager,
        LocaleManager $localeManager,
        MappingMerger $storeViewMappingMerger,
        MappingMerger $categoryMappingMerger,
        WebserviceGuesserFactory $webserviceGuesserFactory,
        NormalizerGuesser $normalizerGuesser,
        CategoryMappingManager $categoryMappingManager,
        CategoryWebservice $categoryWebservice,
        CategoryNormalizer $categoryNormalizer,
        StepExecution $stepExecution,
        StoreViewsWebservice $storeViewsWebservice
    ) {
        $this->beConstructedWith(
            $webserviceGuesserFactory,
            $normalizerGuesser,
            $localeManager,
            $storeViewMappingMerger,
            $categoryMappingMerger,
            $categoryMappingManager
        );
        $this->setStepExecution($stepExecution);

        $webserviceGuesserFactory->getWebservice('category', Argument::any())->willReturn($categoryWebservice);
        $webserviceGuesserFactory->getWebservice('storeviews', Argument::any())->willReturn($storeViewsWebservice);

        $normalizerGuesser->getCategoryNormalizer(Argument::any(), Argument::any())->willReturn($categoryNormalizer);
    }

    function it_normalizes_categories(
        Category $category,
        Category $parentCategory,
        $storeViewsWebservice,
        $categoryWebservice,
        $categoryNormalizer
    ) {
        $categoryWebservice->getCategoriesStatus()->willReturn(array(
            1 => array(
                'category_id' => 1
            )
        ));

        $storeViewsWebservice->getStoreViewsList()->willReturn(array(
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

    function it_gives_category_mapping_in_json($categoryMappingMerger, MappingCollection $mappingCollection)
    {
        $categoryMappingMerger->getMapping()->willReturn($mappingCollection);
        $mappingCollection->toArray()->willReturn(array('foo'));

        $this->getCategoryMapping()->shouldReturn('["foo"]');
    }

    function it_gives_a_proper_configuration_for_fields($categoryMappingMerger, $storeViewMappingMerger)
    {
        $categoryMappingMerger->getConfigurationField()->willReturn(array('foo' => 'bar'));
        $storeViewMappingMerger->getConfigurationField()->willReturn(array('fooo' => 'baar'));
        $this->getConfigurationFields()->shouldReturn(array(
            'soapUsername' => array(
                'options' => array(
                    'required' => true,
                    'help'     => 'pim_magento_connector.export.soapUsername.help',
                    'label'    => 'pim_magento_connector.export.soapUsername.label'
                )
            ),
            'soapApiKey'   => array(
                //Should be remplaced by a password formType but who doesn't
                //empty the field at each edit
                'type'    => 'text',
                'options' => array(
                    'required' => true,
                    'help'     => 'pim_magento_connector.export.soapApiKey.help',
                    'label'    => 'pim_magento_connector.export.soapApiKey.label'
                )
            ),
            'soapUrl' => array(
                'options' => array(
                    'required' => true,
                    'help'     => 'pim_magento_connector.export.soapUrl.help',
                    'label'    => 'pim_magento_connector.export.soapUrl.label'
                )
            ),
            'defaultLocale' => array(
                'type' => 'choice',
                'options' => array(
                    'choices' => null,
                    'required' => true,
                    'attr' => array('class' => 'select2'),
                    'help'     => 'pim_magento_connector.export.defaultLocale.help',
                    'label'    => 'pim_magento_connector.export.defaultLocale.label'
                )
            ),
            'website' => array(
                'type' => 'text',
                'options' => array(
                    'required' => true,
                    'help'     => 'pim_magento_connector.export.website.help',
                    'label'    => 'pim_magento_connector.export.website.label'
                )
            ),
            'fooo' => 'baar',
            'foo' => 'bar',
        ));
    }
}
