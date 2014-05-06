<?php

namespace Pim\Bundle\MagentoConnectorBundle\Entity;

use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;

/**
 * Magento group mapping
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MagentoGroupMapping
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
    protected $magentoGroupId;

    /**
     * @var AttributeGroup
     */
    protected $group;

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
     * Set magentoUrl
     *
     * @param string $magentoUrl
     *
     * @return MagentoGroupMapping
     */
    public function setMagentoUrl($magentoUrl)
    {
        $this->magentoUrl = $magentoUrl;

        return $this;
    }

    /**
     * Get magentoUrl
     *
     * @return string
     */
    public function getMagentoUrl()
    {
        return $this->magentoUrl;
    }

    /**
     * Set magentoGroupId
     *
     * @param string $magentoGroupId
     *
     * @return MagentoGroupMapping
     */
    public function setMagentoGroupId($magentoGroupId)
    {
        $this->magentoGroupId = $magentoGroupId;

        return $this;
    }

    /**
     * Get magentoGroupId
     *
     * @return string
     */
    public function getMagentoGroupId()
    {
        return $this->magentoGroupId;
    }

    /**
     * Set group
     *
     * @param AttributeGroup $group
     *
     * @return MagentoGroupMapping
     */
    public function setGroup(AttributeGroup $group)
    {
        $this->group = $group;

        return $this;
    }

    /**
     * Get group
     *
     * @return AttributeGroup
     */
    public function getGroup()
    {
        return $this->group;
    }
}
