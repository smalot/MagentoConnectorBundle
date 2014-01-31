<?php

namespace Pim\Bundle\MagentoConnectorBundle\Processor;

use Symfony\Component\Validator\Constraints as Assert;
use Oro\Bundle\BatchBundle\Item\ItemProcessorInterface;
use Oro\Bundle\BatchBundle\Item\InvalidItemException;

use Pim\Bundle\MagentoConnectorBundle\Item\MagentoItemStep;
use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\MagentoConnectorBundle\Guesser\NormalizerGuesser;
use Pim\Bundle\MagentoConnectorBundle\Validator\Constraints\HasValidCredentials;
use Pim\Bundle\MagentoConnectorBundle\Validator\Constraints\MagentoUrl;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientParameters;
use Pim\Bundle\MagentoConnectorBundle\Webservice\AttributeSetNotFoundException;
use Pim\Bundle\MagentoConnectorBundle\Manager\LocaleManager;

/**
 * Magento product processor
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @HasValidCredentials()
 */
abstract class AbstractProcessor extends MagentoItemStep implements ItemProcessorInterface
{
    /**
     * @var NormalizerGuesser
     */
    protected $normalizerGuesser;

    /**
     * @var LocaleManager
     */
    protected $localeManager;

    /**
     * @Assert\NotBlank(groups={"Execution"})
     */
    protected $defaultLocale;

    /**
     * @Assert\NotBlank(groups={"Execution"})
     */
    protected $website = 'base';

    /**
     * @var string
     */
    protected $storeViewMapping = '';

    /**
     * @var array
     */
    protected $globalContext = array();

    /**
     * @param WebserviceGuesser        $webserviceGuesser
     * @param ProductNormalizerGuesser $normalizerGuesser
     * @param LocaleManager            $localeManager
     */
    public function __construct(
        WebserviceGuesser $webserviceGuesser,
        NormalizerGuesser $normalizerGuesser,
        LocaleManager $localeManager
    ) {
        parent::__construct($webserviceGuesser);

        $this->localeManager     = $localeManager;
        $this->normalizerGuesser = $normalizerGuesser;
    }

    /**
     * get defaultLocale
     *
     * @return string defaultLocale
     */
    public function getDefaultLocale()
    {
        return $this->defaultLocale;
    }

    /**
     * Set defaultLocale
     *
     * @param string $defaultLocale defaultLocale
     *
     * @return AbstractProcessor
     */
    public function setDefaultLocale($defaultLocale)
    {
        $this->defaultLocale = $defaultLocale;

        return $this;
    }

    /**
     * get website
     *
     * @return string website
     */
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * Set website
     *
     * @param string $website website
     *
     * @return AbstractProcessor
     */
    public function setWebsite($website)
    {
        $this->website = $website;

        return $this;
    }

    /**
     * get storeViewMapping
     *
     * @return string storeViewMapping
     */
    public function getStoreViewMapping()
    {
        return $this->storeViewMapping;
    }

    /**
     * Set storeViewMapping
     *
     * @param string $storeViewMapping storeViewMapping
     *
     * @return AbstractProcessor
     */
    public function setStoreViewMapping($storeViewMapping)
    {
        $this->storeViewMapping = $storeViewMapping;

        return $this;
    }

    /**
     * Get computed storeView mapping (string to array)
     * @return array
     */
    protected function getComputedStoreViewMapping()
    {
        return $this->getComputedMapping($this->storeViewMapping);
    }

    /**
     * Get computed mapping
     * @param string $mapping
     *
     * @return array
     */
    protected function getComputedMapping($mapping)
    {
        $computedMapping = array();

        foreach (explode(chr(10), $mapping) as $line) {
            $computedLine = explode(':', $line);

            if (isset($computedLine[0]) && isset($computedLine[1])) {
                $computedMapping[$computedLine[0]] = $computedLine[1];
            }
        }

        return $computedMapping;
    }

    /**
     * Function called before all process
     */
    protected function beforeExecute()
    {
        parent::beforeExecute();

        $this->globalContext['defaultLocale'] = $this->defaultLocale;
    }

    /**
     * Get the attribute set id for the given family code
     *
     * @param string $familyCode
     * @param mixed  $relatedItem
     *
     * @throws InvalidItemException If The attribute set doesn't exist on Mangento
     * @return integer
     */
    protected function getAttributeSetId($familyCode, $relatedItem)
    {
        try {
            return $this->webservice
                ->getAttributeSetId(
                    $familyCode
                );
        } catch (AttributeSetNotFoundException $e) {
            throw new InvalidItemException($e->getMessage(), array($relatedItem));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return array_merge(
            parent::getConfigurationFields(),
            array(
                'defaultLocale' => array(
                    'type'    => 'choice',
                    'options' => array(
                        'choices'  => $this->localeManager->getLocaleChoices(),
                        'required' => true,
                        'attr' => array(
                            'class' => 'select2'
                        )
                    )
                ),
                'website' => array(
                    'type'    => 'text',
                    'options' => array(
                        'required' => true
                    )
                ),
                'storeViewMapping' => array(
                    'type'    => 'textarea',
                    'options' => array(
                        'required' => false
                    )
                )
            )
        );
    }
}
