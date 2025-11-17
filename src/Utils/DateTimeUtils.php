<?php

declare(strict_types=1);

namespace ClaudePhp\Utils;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;

/**
 * DateTime parsing utilities.
 *
 * Provides helpers for parsing and working with date/time strings.
 */
final class DateTimeUtils
{
    /**
     * Parse an ISO 8601 date string.
     *
     * @param string $dateString The date string in ISO 8601 format
     * @return DateTimeImmutable
     * @throws \Exception If the date string is invalid
     */
    public static function parseDate(string $dateString): DateTimeImmutable
    {
        return DateTimeImmutable::createFromFormat('Y-m-d', $dateString)
            ?: throw new \Exception("Invalid date format: {$dateString}");
    }

    /**
     * Parse an ISO 8601 datetime string.
     *
     * @param string $dateTimeString The datetime string in ISO 8601 format
     * @return DateTimeImmutable
     * @throws \Exception If the datetime string is invalid
     */
    public static function parseDateTime(string $dateTimeString): DateTimeImmutable
    {
        // Try ISO 8601 formats
        $formats = [
            DateTimeInterface::ATOM,          // 2023-01-01T12:00:00+00:00
            'Y-m-d\TH:i:s.uP',               // 2023-01-01T12:00:00.000+00:00
            'Y-m-d\TH:i:s.u\Z',              // 2023-01-01T12:00:00.000Z
            'Y-m-d\TH:i:sP',                 // 2023-01-01T12:00:00+00:00
            'Y-m-d\TH:i:s\Z',                // 2023-01-01T12:00:00Z
        ];

        foreach ($formats as $format) {
            $result = DateTimeImmutable::createFromFormat($format, $dateTimeString);
            if ($result !== false) {
                return $result;
            }
        }

        // Fallback to strtotime for more flexible parsing
        $timestamp = strtotime($dateTimeString);
        if ($timestamp === false) {
            throw new \Exception("Invalid datetime format: {$dateTimeString}");
        }

        return (new DateTimeImmutable())->setTimestamp($timestamp);
    }

    /**
     * Format a DateTime as an ISO 8601 string.
     *
     * @param DateTimeInterface $dateTime
     * @return string
     */
    public static function formatDateTime(DateTimeInterface $dateTime): string
    {
        return $dateTime->format(DateTimeInterface::ATOM);
    }

    /**
     * Format a DateTime as a date-only string (YYYY-MM-DD).
     *
     * @param DateTimeInterface $dateTime
     * @return string
     */
    public static function formatDate(DateTimeInterface $dateTime): string
    {
        return $dateTime->format('Y-m-d');
    }
}
