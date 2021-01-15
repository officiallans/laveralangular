<?php

namespace App\Exceptions;

class WorkflowValidationException extends \Exception {
    const DIFFERENT_YEARS_VACATION = 1;
    const TOO_MANY_VACATION_DAYS = 2;
    const PERIOD_CONFLICT = 3;
}