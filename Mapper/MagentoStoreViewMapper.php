<?php

namespace Pim\Bundle\MagentoConnectorBundle\Mapper;

use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\MagentoConnectorBundle\Validator\Constraints\HasValidCredentialsValidator;

/**
 * Magento storeview mapper
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MagentoStoreViewMapper extends AbstractMapper
{
    /**
     * @var WebserviceGuesser
     */
    protected $webserviceGuesser;

    /**
     * @param HasValidCredentialsValidator $hasValidCredentialsValidator
     * @param WebserviceGuesser            $webserviceGuesser
     */
    public function __construct(
        HasValidCredentialsValidator $hasValidCredentialsValidator,
        WebserviceGuesser $webserviceGuesser
    ) {
        parent::__construct($hasValidCredentialsValidator);

        $this->webserviceGuesser = $webserviceGuesser;
    }

    /**
     * Get mapping
     * @return array
     */
    public function getMapping()
    {
        if (!$this->isValid()) {
            return new MappingCollection();
        } else {
            $storeViews = $this->webserviceGuesser->getWebservice($this->clientParameters)->getStoreViewsList();

            $mapping = new MappingCollection();
            foreach ($storeViews as $storeView) {
                if (in_array($storeView['code'], $this->mandatoryStoreViews())) {
                    $mapping->add(array(
                        'source'    => $storeView['code'],
                        'target'    => $storeView['code'],
                        'deletable' => false
                    ));
                }
            }

            return $mapping;
        }
    }

    /**
     * Get all targets
     * @return array
     */
    public function getAllTargets()
    {
        return $this->isValid() ? array_map(function($storeView) {
            return $storeView['code'];
        }, $this->webserviceGuesser->getWebservice($this->clientParameters)->getStoreViewsList()) : array();
    }

    /**
     * Get mapper identifier
     * @param string rootIdentifier
     *
     * @return string
     */
    public function getIdentifier($rootIdentifier = 'storeview')
    {
        return parent::getIdentifier($rootIdentifier);
    }

    /**
     * Get mandatory attributes
     * @return array
     */
    protected function mandatoryStoreViews()
    {
        return array(
            'default',
        );
    }
}
