<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Manager;

use PhpSpec\ObjectBehavior;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\Common\Persistence\ObjectRepository;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupManagerSpec extends ObjectBehavior
{
    function let(RegistryInterface $registryInterface, ObjectRepository $objectRepository)
    {
        $this->beConstructedWith($registryInterface, 'productClass', 'attributeClass', $objectRepository);
    }

    function it_gives_repository($objectRepository)
    {
        $this->getRepository()->shouldReturn($objectRepository);
    }
}
