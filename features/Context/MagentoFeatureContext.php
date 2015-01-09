<?php

namespace Context;

/**
 * Magento override of the FeatureContext to add the useContext('magento')
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MagentoFeatureContext extends FeatureContext
{
    /**
     * {@inheritdoc}
     */
    public function __construct(array $parameters)
    {
        parent::__construct($parameters);
        $this->useContext('magento', new MagentoContext());
    }
}
