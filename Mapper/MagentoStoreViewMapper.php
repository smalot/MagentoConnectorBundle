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
class MagentoStoreViewMapper extends AbstractStoreviewMapper
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
     * Set mapping
     * @param array $mapping
     */
    public function setMapping(array $mapping)
    {

    }

    /**
     * Get all targets
     * @return array
     */
    public function getAllTargets()
    {
        if (!$this->isValid()) {
            return array();
        } else {
            $storeViews = $this->webserviceGuesser->getWebservice($this->clientParameters)->getStoreViewsList();

            $targets = array();

            foreach ($storeViews as $storeView) {
                $targets[] => $storeView['code'];
            }

            return $targets;
        }
    }

    /**
     * Get all sources
     * @return array
     */
    public function getAllSources()
    {
        return array();
    }

    /**
     * Get mapper priority
     * @return integer
     */
    public function getPriority()
    {
        return 0;
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
