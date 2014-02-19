<?php

namespace Pim\Bundle\MagentoConnectorBundle\Cleaner;

use Oro\Bundle\BatchBundle\Step\StepExecutionAwareInterface;
use Oro\Bundle\BatchBundle\Entity\StepExecution;
use Pim\Bundle\MagentoConnectorBundle\Item\MagentoItemStep;
use Pim\Bundle\MagentoConnectorBundle\Validator\Constraints\HasValidCredentials;

/**
 * Magento item cleaner
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @HasValidCredentials()
 */
abstract class Cleaner extends MagentoItemStep implements StepExecutionAwareInterface
{
    const DO_NOTHING = 'do_nothing';
    const DISABLE    = 'disable';
    const DELETE     = 'delete';

    /**
     * @var StepExecution
     */
    protected $stepExecution;

    /**
     * @var string
     */
    protected $notInPimAnymoreAction;

    /**
     * Get notInPimAnymoreAction
     *
     * @return string notInPimAnymoreAction
     */
    public function getNotInPimAnymoreAction()
    {
        return $this->notInPimAnymoreAction;
    }

    /**
     * Set notInPimAnymoreAction
     *
     * @param string $notInPimAnymoreAction notInPimAnymoreAction
     *
     * @return Cleaner
     */
    public function setNotInPimAnymoreAction($notInPimAnymoreAction)
    {
        $this->notInPimAnymoreAction = $notInPimAnymoreAction;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    abstract public function execute();

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return array_merge(
            parent::getConfigurationFields(),
            array(
                'notInPimAnymoreAction' => array(
                    'type'    => 'choice',
                    'options' => array(
                        'choices'  => array(
                            Cleaner::DO_NOTHING => Cleaner::DO_NOTHING,
                            Cleaner::DISABLE    => Cleaner::DISABLE,
                            Cleaner::DELETE     => Cleaner::DELETE
                        ),
                        'required' => true
                    )
                )
            )
        );
    }
}
