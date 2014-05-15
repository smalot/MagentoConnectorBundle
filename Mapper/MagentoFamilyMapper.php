<?php

namespace Pim\Bundle\MagentoConnectorBundle\Mapper;

use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\MagentoConnectorBundle\Validator\Constraints\HasValidCredentialsValidator;

/**
 * Magento family mapper
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MagentoFamilyMapper extends MagentoMapper
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
     * Get all targets
     * @return array
     */
    public function getAllTargets()
    {
        $targets = array();

        if ($this->isValid()) {
            $families = $this->webserviceGuesser->getWebservice($this->clientParameters)->getAttributeSetList();

            foreach ($families as $familyId => $family) {
                $targets[] = array('id' => $familyId, 'name' => $family['name']);
            }
        }

        return $targets;
    }

    /**
     * Get mapper identifier
     * @param string $rootIdentifier
     *
     * @return string
     */
    public function getIdentifier($rootIdentifier = 'family')
    {
        return parent::getIdentifier($rootIdentifier);
    }
}
