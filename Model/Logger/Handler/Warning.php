<?php

declare(strict_types=1);

namespace Prostor\CumDiscount\Model\Logger\Handler;

use Monolog\Logger;
use Magento\Framework\Logger\Handler\Base;

class Warning extends Base
{
    /**
     * @var int
     */
    protected $loggerType = Logger::WARNING;
}
