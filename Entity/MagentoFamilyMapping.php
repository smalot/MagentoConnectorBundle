<?php

namespace Pim\Bundle\MagentoConnectorBundle\Entity;
use Pim\Bundle\CatalogBundle\Entity\Family;


/**
 * Magento family mapping
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MagentoFamilyMapping
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
    protected $magentoFamilyId;

    /**
     * @var Family
     */
    protected $family;

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
     * @return MagentoFamilyMapping
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
     * Set magentoFamilyId
     *
     * @param string $magentoFamilyId
     *
     * @return MagentoFamilyMapping
     */
    public function setMagentoFamilyId($magentoFamilyId)
    {
        $this->magentoFamilyId = $magentoFamilyId;

        return $this;
    }

    /**
     * Get magentoFamilyId
     *
     * @return string
     */
    public function getMagentoFamilyId()
    {
        return $this->magentoFamilyId;
    }

    /**
     * Set family
     *
     * @param Family $family
     *
     * @return MagentoFamilyMapping
     */
    public function setFamily(Family $family)
    {
        $this->family = $family;

        return $this;
    }

    /**
     * Get family
     *
     * @return Family
     */
    public function getFamily()
    {
        return $this->family;
    }
}
