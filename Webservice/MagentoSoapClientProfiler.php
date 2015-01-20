<?php

namespace Pim\Bundle\MagentoConnectorBundle\Webservice;

/**
 * Class MagentoSoapClientProfiler
 *
 * @author    Damien Carcel (https://github.com/damien-carcel)
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MagentoSoapClientProfiler
{
    /** @staticvar string */
    const DATE_FORMAT = 'Y-m-d H:i:s';

    /** @staticvar boolean */
    const IS_LOG_ACTIVE = false;

    /** @staticvar string */
    const LOG_FILE_NAME = 'soap_profile.log';

    /** @var string */
    protected $logDir;

    /** @var string */
    protected $resource;

    /** @var int */
    protected $startingCallTime;

    /**
     * @param string  $logDir
     */
    public function __construct($logDir)
    {
        $this->logDir = $logDir;
    }

    /**
     * Set starting time of the call and the soap resource called.
     *
     * @param string $resource
     */
    public function startProfileCallLog($resource)
    {
        $this->startingCallTime = microtime(true);
        $this->resource         = $resource;
    }

    /**
     * Set ending time of the call and write log.
     */
    public function endAndWriteProfileCallLog()
    {
        if (true === static::IS_LOG_ACTIVE) {
            $endingCallTime   = microtime(true);
            $duration         = number_format(($endingCallTime - $this->startingCallTime), 3);
            $stepStartingDate = date(static::DATE_FORMAT, $this->startingCallTime);
            $filePath         = $this->logDir . static::LOG_FILE_NAME;

            $this->writeLogInFile($filePath, $stepStartingDate, $this->resource, $duration);
        }
    }

    /**
     * Manage log file.
     *
     * @param string $filePath
     * @param string $stepStartingDate
     * @param string $resource
     * @param string $duration
     */
    protected function writeLogInFile($filePath, $stepStartingDate, $resource, $duration)
    {
        $log = fopen($filePath, "a");

        if ([] === file($filePath)) {
            fwrite($log, "datetime;soap_call_resource;duration (s)\n");
        }
        fwrite($log, sprintf("%s;%s;%s\n", $stepStartingDate, $resource, $duration));
        fclose($log);
    }
}
