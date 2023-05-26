<?php

namespace LookstarKernel\Logging;

use Monolog\Formatter\JsonFormatter;
use Monolog\LogRecord;

class AnalyticsFormatter extends JsonFormatter
{
    public function format(LogRecord $record): string
    {
        $normalized = $this->normalize($record->toArray());
        if (isset($normalized['context'])) {
            $normalized = array_merge($normalized, $normalized['context']);
            unset($normalized['context']);
        }
        return $this->toJson($normalized, true) . ($this->appendNewline ? "\n" : '');
    }
}
