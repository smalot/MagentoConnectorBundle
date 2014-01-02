<?php

namespace Pim\Bundle\MagentoConnectorBundle\Webservice;

/**
 * A magento soap client to abstract interaction with the magento api (above 1.6 version)
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MagentoWebservice16 extends MagentoWebservice
{
    /**
     * Get magento storeview list from magento
     * @return array
     */
    public function getStoreViewsList()
    {
        if (!$this->magentoStoreViewList) {
            $this->magentoStoreViewList = array();
        }

        return $this->magentoStoreViewList;
    }
}
