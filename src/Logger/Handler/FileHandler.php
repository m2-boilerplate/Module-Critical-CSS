<?php

namespace M2Boilerplate\CriticalCss\Logger\Handler;

use Magento\Framework\Logger\Handler\Base;
use Monolog\Logger;

class FileHandler extends Base
{

    protected $loggerType = Logger::NOTICE;

}