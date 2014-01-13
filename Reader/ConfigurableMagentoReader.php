<?php

namespace Pim\Bundle\MagentoConnectorBundle\Reader;

use Symfony\Component\Validator\Constraints as Assert;
use Pim\Bundle\ImportExportBundle\Reader\ORM\Reader;
use Pim\Bundle\ImportExportBundle\Validator\Constraints\Channel as ChannelConstraint;
use Pim\Bundle\ImportExportBundle\Converter\MetricConverter;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\CatalogBundle\Manager\CompletenessManager;
use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\MagentoConnectorBundle\Manager\GroupManager;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;

/**
 * Product reader
  *
  * @author    Julien Sanchhez <julien@akeneo.com>
  * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
  * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
  */
class ConfigurableMagentoReader extends Reader
{
    /**
     * @var string
     *
     * @Assert\NotBlank(groups={"Execution"})
     * @ChannelConstraint
     */
    protected $channel;

    /** @var GroupManager */
    protected $groupManager;

    /** @var ProductManager */
    protected $productManager;

    /** @var ChannelManager */
    protected $channelManager;

    /** @var CompletenessManager */
    protected $completenessManager;

    /* @var MetricConverter */
    protected $metricConverter;

    /**
     * @param GroupManager        $groupManager
     * @param ChannelManager      $channelManager
     * @param CompletenessManager $completenessManager
     * @param MetricConverter     $metricConverter
     */
    public function __construct(
        GroupManager $groupManager,
        ProductManager $productManager,
        ChannelManager $channelManager,
        CompletenessManager $completenessManager,
        MetricConverter $metricConverter
    ) {
        $this->groupManager        = $groupManager;
        $this->productManager      = $productManager;
        $this->channelManager      = $channelManager;
        $this->completenessManager = $completenessManager;
        $this->metricConverter     = $metricConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        if (!$this->query) {
            $channel = current($this->channelManager->getChannels(array('code' => $this->channel)));
            if (!$channel) {
                throw new \InvalidArgumentException(
                    sprintf('Could not find the channel %s', $this->channel)
                );
            }

            $this->completenessManager->generateChannelCompletenesses($channel);

            $this->query = $this->getProductRepository()
                ->buildByChannelAndCompleteness($channel)
                ->getQuery();
        }

        $groupsIds = $this->getGroupRepository()->getVariantGroupIds();

        $products = parent::read();

        if (is_array($products)) {
            $groups = $this->getProductsForGroups($products, $groupsIds);
        } else {
            $groups = null;
        }

        return $groups;
    }

    /**
     * Get products association for each groups
     * @param  array $products
     * @param  array $groupsIds
     * @return array
     */
    protected function getProductsForGroups($products, $groupsIds)
    {
        $groups = array();

        foreach ($products as $product) {
            foreach ($product->getGroups() as $group) {
                if (in_array($group->getId(), $groupsIds)) {
                    if (!isset($groups[$group->getId()])) {
                        $groups[$group->getId()] = array(
                            'group'    => $group,
                            'products' => array()
                        );
                    }

                    $groups[$group->getId()]['products'][] = $product;
                }
            }
        }

        return $groups;
    }

    /**
     * Set channel
     * @param string $channel
     */
    public function setChannel($channel)
    {
        $this->channel = $channel;
    }

    /**
     * Get channel
     * @return string
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return array(
            'channel' => array(
                'type'    => 'choice',
                'options' => array(
                    'choices'  => $this->channelManager->getChannelChoices(),
                    'required' => true,
                    'select2'  => true,
                    'label'    => 'pim_import_export.export.channel.label',
                    'help'     => 'pim_import_export.export.channel.help'
                )
            )
        );
    }

    /**
     * Get the group repository
     *
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getGroupRepository()
    {
        return $this->groupManager->getRepository();
    }

    /**
     * Get the product repository
     *
     * @return \Doctrine\ORM\EntityFlexibleRepository
     */
    protected function getProductRepository()
    {
        return $this->productManager->getFlexibleRepository();
    }
}
