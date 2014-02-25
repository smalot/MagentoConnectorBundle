<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Cleaner;

use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\MagentoConnectorBundle\Webservice\Webservice;
use Pim\Bundle\CatalogBundle\Entity\Repository\ProductRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\AbstractQuery;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Entity\Channel;
use Oro\Bundle\BatchBundle\Entity\StepExecution;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ProductCleanerSpec extends ObjectBehavior
{
    protected $products;

    function let(
        ChannelManager $channelManager,
        WebserviceGuesser $webserviceGuesser,
        ProductManager $productManager,
        Webservice $webservice,
        ProductRepository $productRepository,
        QueryBuilder $queryBuilder,
        AbstractQuery $query,
        ProductInterface $firstProduct,
        ProductInterface $secondProduct,
        ProductInterface $thirdProduct,
        Channel $channel,
        StepExecution $stepExecution
    ) {
        $this->beConstructedWith($webserviceGuesser, $channelManager, $productManager);
        $this->setStepExecution($stepExecution);

        $webserviceGuesser->getWebservice(Argument::cetera())->willReturn($webservice);

        $webservice->getProductsStatus()->willReturn(
            array(
                array('sku' => 'sku-000'),
                array('sku' => 'sku-001'),
                array('sku' => 'sku-002')
            )
        );

        $channelManager->getChannelByCode(Argument::any())->willReturn($channel);

        $queryBuilder->getQuery()->willReturn($query);
        $productRepository->buildByChannelAndCompleteness(Argument::any())->willReturn($queryBuilder);
        $productManager->getFlexibleRepository()->willReturn($productRepository);

        $firstProduct->getIdentifier()->willReturn('sku-000');
        $secondProduct->getIdentifier()->willReturn('sku-001');
        $thirdProduct->getIdentifier()->willReturn('sku-002');

        $this->products = array(
            $firstProduct,
            $secondProduct,
            $thirdProduct
        );
    }

    function it_tells_magento_to_disable_deleted_products($webservice, $productRepository, $query)
    {
        $this->setNotCompleteAnymoreAction('do_nothing');
        $this->setNotInPimAnymoreAction('disable');

        $query->getResult()->willReturn(array($this->products[0], $this->products[1]));
        $productRepository->findAll()->willReturn(array($this->products[0], $this->products[1]));

        $webservice->disableProduct('sku-002')->shouldBeCalled();

        $this->execute();
    }

    function it_tells_magento_to_delete_deleted_products($webservice, $productRepository, $query)
    {
        $this->setNotCompleteAnymoreAction('do_nothing');
        $this->setNotInPimAnymoreAction('delete');

        $query->getResult()->willReturn(array($this->products[0], $this->products[2]));
        $productRepository->findAll()->willReturn(array($this->products[0], $this->products[2]));

        $webservice->deleteProduct('sku-001')->shouldBeCalled();

        $this->execute();
    }

    function it_tells_magento_to_disable_incomplete_products($webservice, $productRepository, $query)
    {
        $this->setNotCompleteAnymoreAction('disable');
        $this->setNotInPimAnymoreAction('do_nothing');

        $query->getResult()->willReturn(array($this->products[1], $this->products[2]));
        $productRepository->findAll()->willReturn(array($this->products[0], $this->products[1], $this->products[2]));

        $webservice->disableProduct('sku-000')->shouldBeCalled();

        $this->execute();
    }

    function it_tells_magento_to_delete_incomplete_products($webservice, $productRepository, $query)
    {
        $this->setNotCompleteAnymoreAction('delete');
        $this->setNotInPimAnymoreAction('do_nothing');

        $query->getResult()->willReturn(array($this->products[0], $this->products[2]));
        $productRepository->findAll()->willReturn(array($this->products[0], $this->products[1], $this->products[2]));

        $webservice->deleteProduct('sku-001')->shouldBeCalled();

        $this->execute();
    }

    function it_raises_an_invalid_item_exception_when_something_goes_wrong_with_the_sopa_api($webservice, $productRepository, $query)
    {
        $this->setNotCompleteAnymoreAction('delete');
        $this->setNotInPimAnymoreAction('do_nothing');

        $query->getResult()->willReturn(array($this->products[0], $this->products[2]));
        $productRepository->findAll()->willReturn(array($this->products[0], $this->products[1], $this->products[2]));

        $webservice->deleteProduct('sku-001')->willThrow('Pim\Bundle\MagentoConnectorBundle\Webservice\SoapCallException');

        $this->shouldThrow('Oro\Bundle\BatchBundle\Item\InvalidItemException')->during('execute');
    }

    function it_shoulds_have_a_well_formed_form_configuration($channelManager)
    {
        $channelManager->getChannelChoices()->willReturn(array('channel' => 'channel'));

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
            'notInPimAnymoreAction' => array(
                'type'    => 'choice',
                'options' => array(
                    'choices'  => array(
                        'do_nothing' => 'pim_magento_connector.clean.do_nothing.label',
                        'disable'    => 'pim_magento_connector.clean.disable.label',
                        'delete'     => 'pim_magento_connector.clean.delete.label'
                    ),
                    'required' => true,
                    'help'     => 'pim_magento_connector.clean.notInPimAnymoreAction.help',
                    'label'    => 'pim_magento_connector.clean.notInPimAnymoreAction.label'
                )
            ),
            'notCompleteAnymoreAction' => array(
                'type'    => 'choice',
                'options' => array(
                    'choices'  => array(
                        'do_nothing' => 'pim_magento_connector.clean.do_nothing.label',
                        'disable'    => 'pim_magento_connector.clean.disable.label',
                        'delete'     => 'pim_magento_connector.clean.delete.label'
                    ),
                    'required' => true,
                    'help'     => 'pim_magento_connector.clean.notCompleteAnymoreAction.help',
                    'label'    => 'pim_magento_connector.clean.notCompleteAnymoreAction.label'
                )
            ),
            'channel'      => array(
                'type'    => 'choice',
                'options' => array(
                    'choices'  => $this->channelManager->getChannelChoices(),
                    'required' => true
                )
            )
        ));
    }

    function it_shoulds_be_configurable()
    {
        $this->setChannel('channel');
        $this->setNotCompleteAnymoreAction('not_complete_anymore_action');
        $this->setNotInPimAnymoreAction('not_in_pim_anymore_action');
        $this->getChannel()->shouldReturn('channel');
        $this->getNotCompleteAnymoreAction()->shouldReturn('not_complete_anymore_action');
        $this->getNotInPimAnymoreAction()->shouldReturn('not_in_pim_anymore_action');
    }
}
