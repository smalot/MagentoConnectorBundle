<?php

namespace Pim\Bundle\MagentoConnectorBundle\Entity;

use Pim\Bundle\CatalogBundle\Entity\Category;

/**
 * Magento category mapping
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MagentoCategoryMapping
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
    protected $magentoCategoryId;

    /**
     * @var Category
     */
    protected $category;

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
     * @return MagentoCategoryMapping
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
     * Set magentoCategoryId
     *
     * @param string $magentoCategoryId
     *
     * @return MagentoCategoryMapping
     */
    public function setMagentoCategoryId($magentoCategoryId)
    {
        $this->magentoCategoryId = $magentoCategoryId;

        return $this;
    }

    /**
     * Get magentoCategoryId
     *
     * @return string
     */
    public function getMagentoCategoryId()
    {
        return $this->magentoCategoryId;
    }

    /**
     * Set category
     *
     * @param Category $category
     *
     * @return MagentoCategoryMapping
     */
    public function setCategory(Category $category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return Category
     */
    public function getCategory()
    {
        return $this->category;
    }
}
