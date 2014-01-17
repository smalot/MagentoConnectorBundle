<?php

namespace Pim\Bundle\MagentoConnectorBundle\Entity;

use Pim\Bundle\CatalogBundle\Entity\Category;

/**
 * MagentoCategoryMapping
 */
class MagentoCategoryMapping
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $magentoUrl;

    /**
     * @var integer
     */
    private $magentoCategoryId;

    /**
     * @var Category
     */
    private $category;

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
     * @param string $category
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
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }
}
