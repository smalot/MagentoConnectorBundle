<?php

namespace Pim\Bundle\MagentoConnectorBundle\Entity;

use Akeneo\Bundle\BatchBundle\Entity\JobInstance;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;

/**
 * Delta published product export entity
 *
 * @author Romain Monceau <romain@akeneo.com>
 */
class DeltaProductExport
{
    /** @var int */
    protected $id;

    /** @var \DateTime */
    protected $lastExport;

    /** @var ProductInterface */
    protected $product;

    /** @var JobInstance */
    protected $jobInstance;

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param \DateTime $lastExport
     *
     * @return DeltaProductExport
     */
    public function setLastExport($lastExport)
    {
        $this->lastExport = $lastExport;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getLastExport()
    {
        return $this->lastExport;
    }

    /**
     * @param ProductInterface $product
     *
     * @return DeltaProductExport
     */
    public function setProduct(ProductInterface $product = null)
    {
        $this->product = $product;

        return $this;
    }

    /**
     * @return ProductInterface
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @param JobInstance $jobInstance
     *
     * @return DeltaProductExport
     */
    public function setJobInstance(JobInstance $jobInstance = null)
    {
        $this->jobInstance = $jobInstance;

        return $this;
    }

    /**
     * @return JobInstance
     */
    public function getJobInstance()
    {
        return $this->jobInstance;
    }
}
