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
class MagentoStoreViewMapper extends Mapper
{
    /**
     * @var WebserviceGuesser
     */
    protected $webserviceGuesser;

    /**
     * @param HasValidCredentialsValidator $hasValidCredentialsValidator
     * @param boolean                      $allowAddition
     * @param WebserviceGuesser            $webserviceGuesser
     */
    public function __construct(
        HasValidCredentialsValidator $hasValidCredentialsValidator,
        $allowAddition,
        WebserviceGuesser $webserviceGuesser
    ) {
        parent::__construct($hasValidCredentialsValidator, $allowAddition);

        $this->webserviceGuesser = $webserviceGuesser;
    }

    /**
     * Get all targets
     * @return array
     */
    public function getAllTargets()
    {
        $targets = array();

        if ($this->isValid()) {
            $storeViews = $this->webserviceGuesser->getWebservice($this->clientParameters)->getStoreViewsList();

            foreach ($storeViews as $storeView) {
                $targets[] = array('id' => $storeView['code'], 'text' => $storeView['code']);
            }
        }

        return array('targets' => $targets, 'allowAddition' => $this->allowAddition);
    }

    /**
     * Get mapper identifier
     * @param string $rootIdentifier
     *
     * @return string
     */
    public function getIdentifier($rootIdentifier = 'storeview')
    {
        return parent::getIdentifier($rootIdentifier);
    }
}
