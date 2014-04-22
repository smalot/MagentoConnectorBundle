<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Validator\Checks;

use Pim\Bundle\MagentoConnectorBundle\Validator\Exceptions\InvalidXmlException;
use PhpSpec\ObjectBehavior;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class XmlCheckerSpec extends ObjectBehavior
{
    function it_should_failed_with_invalid_xml()
    {
        $exception = new InvalidXmlException();
        $invalidXml = '<note><to>Tove</to><from>Jani</from><heading>Reminder</heading><body>Don\'t forget me this weekend!</body>';
        $this->shouldThrow($exception)->duringCheckXml($invalidXml);
    }
}
