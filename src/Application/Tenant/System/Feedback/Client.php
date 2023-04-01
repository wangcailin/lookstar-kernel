<?php

namespace LookstarKernel\Application\Tenant\System\Feedback;

use LookstarKernel\Application\Tenant\System\Feedback\Models\Feedback;
use Composer\Http\Controller;

class Client extends Controller
{
    public function __construct(Feedback $feedback)
    {
        $this->model = $feedback;
        $this->validateRules = [
            'score' => 'numeric',
            'text' => 'required',
        ];
    }
}
