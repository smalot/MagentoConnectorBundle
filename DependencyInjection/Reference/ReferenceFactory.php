<?php

namespace Pim\Bundle\MagentoConnectorBundle\DependencyInjection\Reference;

use Symfony\Component\DependencyInjection\Reference;

/**
 * Factory to create container service reference
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReferenceFactory
{
    /**
     * Create a reference to a container service
     *
     * @param string $serviceId
     *
     * @return Reference
     */
    public function createReference($serviceId)
    {
        return new Reference($serviceId);
    }
}
