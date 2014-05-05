<?php

namespace Pim\Bundle\MagentoConnectorBundle\Entity;

use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;

/**
 * Magento attribute mapping
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MagentoAttributeMapping
{
    /**
     * @var integer
     */
    protected $id;

    /**
     * @var string
     */
    protected $magentoUrl;

    /**
     * @var integer
     */
    protected $magentoAttributeId;

    /**
     * @var AbstractAttribute
     */
    protected $attribute;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set magento url
     *
     * @param string $magentoUrl
     *
     * @return MagentoAttributeMapping
     */
    public function setMagentoUrl($magentoUrl)
    {
        $this->magentoUrl = $magentoUrl;

        return $this;
    }

    /**
     * Get magento url
     *
     * @return string
     */
    public function getMagentoUrl()
    {
        return $this->magentoUrl;
    }

    /**
     * Set magento attribute id
     *
     * @param string $magentoAttributeId
     *
     * @return MagentoAttributeMapping
     */
    public function setMagentoAttributeId($magentoAttributeId)
    {
        $this->magentoAttributeId = $magentoAttributeId;

        return $this;
    }

    /**
     * Get magento attribute id
     *
     * @return string
     */
    public function getMagentoAttributeId()
    {
        return $this->magentoAttributeId;
    }

    /**
     * Set attribute
     *
     * @param AbstractAttribute $attribute
     *
     * @return MagentoAttributeMapping
     */
    public function setAttribute(AbstractAttribute $attribute)
    {
        $this->attribute = $attribute;

        return $this;
    }

    /**
     * Get attribute
     *
     * @return AbstractAttribute
     */
    public function getAttribute()
    {
        return $this->attribute;
    }
}
