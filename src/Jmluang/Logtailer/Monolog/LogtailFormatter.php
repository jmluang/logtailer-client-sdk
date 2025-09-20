<?php

/*
 * This file is part of the logtailer/monolog-logtail package.
 *
 * Compatible with logtail/monolog-logtail for Logtailer service
 */

namespace Jmluang\Logtailer\Monolog;

use Monolog\Formatter\JsonFormatter;
use Monolog\LogRecord;

/**
 * Format JSON records for Logtail
 */
class LogtailFormatter extends JsonFormatter {

    public function __construct() {
        parent::__construct(self::BATCH_MODE_JSON, false);
        $this->setMaxNormalizeItemCount(PHP_INT_MAX);
    }

    public function format(LogRecord $record): string {
        $normalized = $this->normalize(self::formatRecord($record));

        return $this->toJson($normalized, true);
    }

    public function formatBatch(array $records): string
    {
        $normalized = array_values($this->normalize(array_map(self::formatRecord(...), $records)));
        return $this->toJson($normalized, true);
    }

    protected static function formatRecord(LogRecord $record): array
    {
        return [
            'dt' => $record->datetime,
            'message' => $record->message,
            'level' => $record->level->name,
            'monolog' => [
                'channel' => $record->channel,
                'context' => $record->context,
                'extra' => $record->extra,
            ],
        ];
    }
}