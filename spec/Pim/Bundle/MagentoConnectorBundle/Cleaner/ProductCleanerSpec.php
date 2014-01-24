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
        Channel $channel
    ) {
        $this->beConstructedWith($webserviceGuesser, $channelManager, $productManager);

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
}
