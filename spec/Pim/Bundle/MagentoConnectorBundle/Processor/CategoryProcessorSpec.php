<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Processor;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Pim\Bundle\CatalogBundle\Entity\Category;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\MagentoConnectorBundle\Manager\LocaleManager;
use Pim\Bundle\MagentoConnectorBundle\Merger\MagentoConnectorMappingMerger;
use Pim\Bundle\ConnectorMappingBundle\Mapper\MappingCollection;
use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\MagentoConnectorBundle\Guesser\NormalizerGuesser;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientParameters;
use Pim\Bundle\MagentoConnectorBundle\Manager\CategoryMappingManager;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\AbstractNormalizer;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\CategoryNormalizer;
use Pim\Bundle\MagentoConnectorBundle\Webservice\Webservice;
use Akeneo\Bundle\BatchBundle\Entity\StepExecution;

class CategoryProcessorSpec extends ObjectBehavior
{
    function let(
        ChannelManager $channelManager,
        LocaleManager $localeManager,
        MagentoConnectorMappingMerger $storeViewMappingMerger,
        MagentoConnectorMappingMerger $categoryMappingMerger,
        WebserviceGuesser $webserviceGuesser,
        NormalizerGuesser $normalizerGuesser,
        CategoryMappingManager $categoryMappingManager,
        Webservice $webservice,
        CategoryNormalizer $categoryNormalizer,
        StepExecution $stepExecution
    ) {
        $this->beConstructedWith(
            $webserviceGuesser,
            $normalizerGuesser,
            $localeManager,
            $storeViewMappingMerger,
            $categoryMappingMerger,
            $categoryMappingManager
        );
        $this->setStepExecution($stepExecution);

        $webserviceGuesser->getWebservice(Argument::any())->willReturn($webservice);

        $normalizerGuesser->getCategoryNormalizer(Argument::any(), Argument::any())->willReturn($categoryNormalizer);
    }

    function it_normalizes_categories(
        Category $category,
        Category $parentCategory,
        $webservice,
        $categoryNormalizer,
        $categoryMappingMerger
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
            'magentoUrl' => array(
                'options' => array(
                    'required' => true,
                    'help'     => 'pim_magento_connector.export.magentoUrl.help',
                    'label'    => 'pim_magento_connector.export.magentoUrl.label'
                )
            ),
            'wsdlUrl' => array(
                'options' => array(
                    'required' => true,
                    'help'     => 'pim_magento_connector.export.wsdlUrl.help',
                    'label'    => 'pim_magento_connector.export.wsdlUrl.label',
                    'data'     => MagentoSoapClientParameters::SOAP_WSDL_URL
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
