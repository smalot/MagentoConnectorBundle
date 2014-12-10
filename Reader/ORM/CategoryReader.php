<?php

namespace Pim\Bundle\MagentoConnectorBundle\Reader;

use Doctrine\ORM\EntityManager;
use Pim\Bundle\BaseConnectorBundle\Reader\ORM\EntityReader;
use Pim\Bundle\CatalogBundle\Entity\Repository\CategoryRepository;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;

/**
 * Category reader to read only from the channel tree
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryReader extends EntityReader
{
    /**
     * @var CategoryRepository
     */
    protected $repository;

    /**
     * @var ChannelManager
     */
    protected $channelManager;

    /**
     * @var string Channel code
     */
    protected $channel;

    /**
     * @param EntityManager      $em
     * @param string             $className
     * @param CategoryRepository $repository
     * @param ChannelManager     $channelManager
     */
    public function __construct(
        EntityManager $em,
        $className,
        CategoryRepository $repository,
        ChannelManager $channelManager
    ) {
        parent::__construct($em, $className);

        $this->repository = $repository;
        $this->channelManager = $channelManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getQuery()
    {
        if (!$this->query) {
            $channel = $this->channelManager->getChannelByCode($this->channel);

            $qb = $this->getRepository()->createQueryBuilder('c');
            $qb
                ->andWhere('c.root = '. $channel->getCategory()->getId())
                ->orderBy('c.level, c.left', 'ASC');

            $this->query = $qb->getQuery();
        }

        return $this->query;
    }

    /**
     * @return CategoryRepository
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return array_merge(
            parent::getConfigurationFields(),
            array(
                'channel' => array(
                    'type'    => 'choice',
                    'options' => array(
                        'choices'  => $this->channelManager->getChannelChoices(),
                        'required' => true,
                        'select2'  => true,
                        'label'    => 'pim_base_connector.export.channel.label',
                        'help'     => 'pim_base_connector.export.channel.help'
                    )
                )
            )
        );
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
}
