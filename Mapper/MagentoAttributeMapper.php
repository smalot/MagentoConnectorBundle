<?php

namespace Pim\Bundle\MagentoConnectorBundle\Mapper;

use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser;

/**
 * Magento attribute mapper
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MagentoAttributeMapper extends AbstractAttributeMapper
{
    /**
     * @var WebserviceGuesser
     */
    protected $webserviceGuesser;

    public function __construct(WebserviceGuesser $webserviceGuesser)
    {
        $this->webserviceGuesser = $webserviceGuesser;
    }

    public function getMapping()
    {
        if (!$this->isValid()) {
            return array();
        } else {
            $attributes = $this->webserviceGuesser->getWebservice($this->clientParameters)->getAllAttributes();

            $mapping = array();
            foreach(array_keys($attributes) as $attributeCode)
            {
                $mapping[$attributeCode] = '';
            }

            return $mapping;
        }
    }

    public function setMapping(array $mapping) {}

    public function getPriority()
    {
        return 0;
    }
}
