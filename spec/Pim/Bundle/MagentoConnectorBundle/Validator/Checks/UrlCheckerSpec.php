<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Validator\Checks;

use Pim\Bundle\MagentoConnectorBundle\Validator\Exceptions\InvalidUrlException;
use Pim\Bundle\MagentoConnectorBundle\Validator\Exceptions\NotReachableUrlException;
use PhpSpec\ObjectBehavior;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UrlCheckerSpec extends ObjectBehavior
{
    function it_should_not_allow_invalid_url()
    {
        $exception = new InvalidUrlException();

        $this->shouldThrow($exception)->duringCheckAnUrl('notAnUrl', '\n0t/url@', 'http://n0turl4t4ll', 'myurl.url');
    }

    function it_should_success_with_valid_url()
    {
        $this->checkAnUrl('http://valid.url')->shouldReturn(true);
    }

    function it_should_failed_if_url_not_return_200_http_status()
    {
        $exception = new NotReachableUrlException();

        $this->shouldThrow($exception)->duringCheckReachableUrl('notAnUrl', 'http://n0turl4t4ll', 'myurl.url', 'http://notfoundazerty.url');
    }

    function it_should_success_with_reachable_200_http_status_url()
    {
        $this->checkReachableUrl('https://www.google.fr')->shouldReturn(true);
    }
}
