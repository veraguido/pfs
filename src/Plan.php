<?php

namespace PFS;

use DateTime;
use Error;

class Plan
{
    protected bool $onTime;
    public function __construct(
        protected DateTime $endDate,
        protected DateTime $estimatedDate,
    ){}
    public function execute(): void
    {
        $this->onTime = boolval($this->endDate <= $this->estimatedDate);
    }
    public function getOnTime(): bool
    {
        return $this->onTime;
    }
    public function getEstimatedDate():DateTime
    {
        return $this->estimatedDate;
    }
}
