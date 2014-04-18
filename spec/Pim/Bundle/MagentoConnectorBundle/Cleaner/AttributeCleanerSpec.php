<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Cleaner;

use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesserFactory;
use Pim\Bundle\MagentoConnectorBundle\Webservice\AttributeWebservice;
use Doctrine\ORM\EntityManager;
use Pim\Bundle\MagentoConnectorBundle\Merger\MappingMerger;
use Pim\Bundle\MagentoConnectorBundle\Mapper\MappingCollection;
use Doctrine\ORM\EntityRepository;
use Pim\Bundle\CatalogBundle\Entity\Attribute;
use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class AttributeCleanerSpec extends ObjectBehavior
{
    function let(
        WebserviceGuesserFactory $webserviceGuesserFactory,
        MappingMerger $attributeMappingMerger,
        EntityManager $em,
        AttributeWebservice $attributeWebservice,
        EntityRepository $entityRepository,
        MappingCollection $mappingCollection,
        StepExecution $stepExecution
    ) {
        $this->beConstructedWith($webserviceGuesserFactory, $attributeMappingMerger, $em, 'attribute_class');
        $this->setStepExecution($stepExecution);

        $webserviceGuesserFactory->getWebservice('attribute', Argument::cetera())->willReturn($attributeWebservice);
        $em->getRepository('attribute_class')->willReturn($entityRepository);
        $attributeMappingMerger->getMapping()->willReturn($mappingCollection);
    }

    function it_shoulds_delete_attribute_not_in_pim_anymore($attributeWebservice, $entityRepository, $mappingCollection)
    {
        $this->setNotInPimAnymoreAction('delete');

        $attributeWebservice->getAllAttributes()->willReturn(array(array('code' => 'foo')));
        $entityRepository->findOneBy(array('code' => 'foo'))->willReturn(null);
        $mappingCollection->getSource('foo')->willReturn('foo');

        $attributeWebservice->deleteAttribute('foo')->shouldBeCalled();

        $this->execute();
    }

    function it_shoulds_not_delete_attribute_not_in_pim_anymore_if_parameters_doesnt_say_to_do_so($attributeWebservice, $entityRepository, $mappingCollection)
    {
        $this->setNotInPimAnymoreAction('do_nothing');

        $attributeWebservice->getAllAttributes()->willReturn(array(array('code' => 'foo')));
        $entityRepository->findOneBy(array('code' => 'foo'))->willReturn(null);
        $mappingCollection->getSource('foo')->willReturn('foo');

        $attributeWebservice->deleteAttribute('foo')->shouldNotBeCalled();

        $this->execute();
    }

    function it_shoulds_delete_attribute_not_in_family_anymore($attributeWebservice, $entityRepository, $mappingCollection, Attribute $attribute)
    {
        $this->setNotInPimAnymoreAction('delete');

        $attributeWebservice->getAllAttributes()->willReturn(array(array('code' => 'foo')));
        $entityRepository->findOneBy(array('code' => 'foo'))->willReturn($attribute);
        $attribute->getFamilies()->willReturn(null);
        $mappingCollection->getSource('foo')->willReturn('foo');

        $attributeWebservice->deleteAttribute('foo')->shouldBeCalled();

        $this->execute();
    }

    function it_shoulds_delete_attribute_which_got_renamed($attributeWebservice, $entityRepository, $mappingCollection, Attribute $attribute)
    {
        $this->setNotInPimAnymoreAction('delete');

        $attributeWebservice->getAllAttributes()->willReturn(array(array('code' => 'foo')));
        $entityRepository->findOneBy(array('code' => null))->willReturn($attribute);
        $attribute->getFamilies()->willReturn(false);
        $mappingCollection->getSource('foo')->willReturn(null);

        $attributeWebservice->deleteAttribute('foo')->shouldBeCalled();

        $this->execute();
    }

    function it_raises_an_invalid_item_exception_when_something_goes_wrong_with_the_sopa_api($attributeWebservice, $entityRepository, $mappingCollection, Attribute $attribute)
    {
        $this->setNotInPimAnymoreAction('delete');

        $attributeWebservice->getAllAttributes()->willReturn(array(array('code' => 'foo')));
        $entityRepository->findOneBy(array('code' => null))->willReturn($attribute);
        $attribute->getFamilies()->willReturn(false);
        $mappingCollection->getSource('foo')->willReturn(null);

        $attributeWebservice->deleteAttribute('foo')->willThrow('Pim\Bundle\MagentoConnectorBundle\Webservice\Exception\SoapCallException');

        $this->shouldThrow('Akeneo\Bundle\BatchBundle\Item\InvalidItemException')->during('execute');
    }

    function it_shoulds_get_attribute_mapping_from_attribute_mapping_merger($attributeMappingMerger, MappingCollection $mappingCollection)
    {
        $attributeMappingMerger->getMapping()->willReturn($mappingCollection);
        $mappingCollection->toArray()->willReturn(array());

        $this->getAttributeMapping()->shouldReturn('[]');
    }

    function it_shoulds_set_attribute_mapping_to_the_attribute_mapping_merger($attributeMappingMerger)
    {
        $attributeMappingMerger->setMapping(array())->shouldBeCalled();

        $this->setAttributeMapping('[]');
    }

    function it_shoulds_give_configuration_fields($attributeMappingMerger)
    {
        $attributeMappingMerger->getConfigurationField()->willReturn(array('attributeMapping' => array()));

        $this->getConfigurationFields()->shouldReturn(
            array(
                'soapUsername' => array(
                    'options' => array(
                        'required' => true,
                        'help'     => 'pim_magento_connector.export.soapUsername.help',
                        'label'    => 'pim_magento_connector.export.soapUsername.label'
                    )
                ),
                'soapApiKey'   => array(
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
                'notInPimAnymoreAction' => array(
                    'type'    => 'choice',
                    'options' => array(
                        'choices'  => array(
                            'do_nothing' => 'pim_magento_connector.export.do_nothing.label',
                            'delete'     => 'pim_magento_connector.export.delete.label'
                        ),
                        'required' => true,
                        'help'     => 'pim_magento_connector.export.notInPimAnymoreAction.help',
                        'label'    => 'pim_magento_connector.export.notInPimAnymoreAction.label'
                    )
                ),
                'attributeMapping' => array()
            )
        );
    }
}
