<?php

namespace App\Common\Event;

class AfterRequirementApproval extends Base
{
    public function __construct(
        protected ?array $data
    ) {
    }
}
