<?php

namespace Pim\Bundle\MagentoConnectorBundle\Mapper;

/**
 * Defines the interface of a mapper
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface MapperInterface
{
    public function getIdentifier();

    public function getMapping();

    public function setMapping(array $mapping);

    public function getPriority();

    public function isValid();
}
