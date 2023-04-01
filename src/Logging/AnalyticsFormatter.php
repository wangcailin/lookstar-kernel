<?php

namespace LookstarKernel\Logging;

use Monolog\Formatter\JsonFormatter;

class AnalyticsFormatter extends JsonFormatter
{
    public function format(array $record): string
    {
        $normalized = $this->normalize($record);
        if (isset($normalized['context'])) {
            $normalized = array_merge($normalized, $normalized['context']);
            unset($normalized['context']);
        }
        return $this->toJson($normalized, true) . ($this->appendNewline ? "\n" : '');
    }
}
