<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Cleaner;

use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\MagentoConnectorBundle\Webservice\Webservice;
use Doctrine\ORM\EntityManager;
use Pim\Bundle\MagentoConnectorBundle\Merger\MappingMerger;
use Pim\Bundle\MagentoConnectorBundle\Mapper\MappingCollection;
use Doctrine\ORM\EntityRepository;
use Pim\Bundle\CatalogBundle\Entity\Attribute;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class AttributeCleanerSpec extends ObjectBehavior
{
    function let(
        WebserviceGuesser $webserviceGuesser,
        MappingMerger $attributeMappingMerger,
        EntityManager $em,
        Webservice $webservice,
        EntityRepository $entityRepository,
        MappingCollection $mappingCollection
    ) {
        $this->beConstructedWith($webserviceGuesser, $attributeMappingMerger, $em, 'attribute_class');
        $webserviceGuesser->getWebservice(Argument::cetera())->willReturn($webservice);
        $em->getRepository('attribute_class')->willReturn($entityRepository);
        $attributeMappingMerger->getMapping()->willReturn($mappingCollection);
    }

    function it_shoulds_delete_attribute_not_in_pim_anymore($webservice, $entityRepository, $mappingCollection)
    {
        $this->setNotInPimAnymoreAction('delete');

        $webservice->getAllAttributes()->willReturn(array(array('code' => 'foo')));
        $entityRepository->findOneBy(array('code' => 'foo'))->willReturn(null);
        $mappingCollection->getSource('foo')->willReturn('foo');

        $webservice->deleteAttribute('foo')->shouldBeCalled();

        $this->execute();
    }

    function it_shoulds_not_delete_attribute_not_in_pim_anymore_if_parameters_doesnt_say_to_do_so($webservice, $entityRepository, $mappingCollection)
    {
        $this->setNotInPimAnymoreAction('do_nothing');

        $webservice->getAllAttributes()->willReturn(array(array('code' => 'foo')));
        $entityRepository->findOneBy(array('code' => 'foo'))->willReturn(null);
        $mappingCollection->getSource('foo')->willReturn('foo');

        $webservice->deleteAttribute('foo')->shouldNotBeCalled();

        $this->execute();
    }

    function it_shoulds_delete_attribute_not_in_family_anymore($webservice, $entityRepository, $mappingCollection, Attribute $attribute)
    {
        $this->setNotInPimAnymoreAction('delete');

        $webservice->getAllAttributes()->willReturn(array(array('code' => 'foo')));
        $entityRepository->findOneBy(array('code' => 'foo'))->willReturn($attribute);
        $attribute->getFamilies()->willReturn(null);
        $mappingCollection->getSource('foo')->willReturn('foo');

        $webservice->deleteAttribute('foo')->shouldBeCalled();

        $this->execute();
    }

    function it_shoulds_delete_attribute_which_dot_renamed($webservice, $entityRepository, $mappingCollection, Attribute $attribute)
    {
        $this->setNotInPimAnymoreAction('delete');

        $webservice->getAllAttributes()->willReturn(array(array('code' => 'foo')));
        $entityRepository->findOneBy(array('code' => 'foo'))->willReturn($attribute);
        $attribute->getFamilies()->willReturn(false);
        $mappingCollection->getSource('foo')->willReturn(null);

        $webservice->deleteAttribute('foo')->shouldBeCalled();

        $this->execute();
    }
}
