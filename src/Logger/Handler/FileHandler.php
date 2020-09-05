<?php
/**
 * FileHandler.php
 *
 * @category    Leonex
 * @package     ???
 * @author      Thomas Hampe <hampe@leonex.de>
 * @copyright   Copyright (c) 2020, LEONEX Internet GmbH
 */


namespace M2Boilerplate\CriticalCss\Logger\Handler;


use Magento\Framework\Logger\Handler\Base;
use Monolog\Logger;

class FileHandler extends Base
{

    protected $loggerType = Logger::NOTICE;

}