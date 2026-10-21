<?php

declare(strict_types=1);

namespace Modules\Listing\Support;

final class ListingDescriptionBlocks
{
    private const BULLET_PATTERN = '/^\s*(?:[•\-\*·▪◦]|\d+[.)])\s+/u';

    public static function fromText(string $text): array
    {
        $normalized = str_replace(["\r\n", "\r"], "\n", trim($text));

        if ($normalized === '') {
            return [];
        }

        $blocks = [];

        foreach (preg_split('/\n\s*\n/u', $normalized) ?: [] as $index => $chunk) {
            $lines = array_values(array_filter(
                array_map('trim', explode("\n", $chunk)),
                fn (string $line): bool => $line !== ''
            ));

            if ($lines === []) {
                continue;
            }

            if (self::isHeading($lines[0])) {
                $blocks[] = ['type' => 'heading', 'text' => self::headingText($lines[0])];
                array_shift($lines);
            }

            if ($lines === []) {
                continue;
            }

            if ($index === 0 && $blocks === [] && str_contains($lines[0], ' | ')) {
                $blocks[] = ['type' => 'meta', 'items' => array_map('trim', explode('|', $lines[0]))];
                array_shift($lines);

                if ($lines !== []) {
                    $blocks[] = ['type' => 'note', 'text' => implode(' ', $lines)];
                }

                continue;
            }

            $bullets = array_filter($lines, fn (string $line): bool => (bool) preg_match(self::BULLET_PATTERN, $line));

            if (count($bullets) === count($lines)) {
                $blocks[] = [
                    'type' => 'list',
                    'items' => array_map(fn (string $line): string => (string) preg_replace(self::BULLET_PATTERN, '', $line), $lines),
                ];

                continue;
            }

            $blocks[] = ['type' => 'paragraph', 'text' => implode("\n", $lines)];
        }

        return $blocks;
    }

    private static function isHeading(string $line): bool
    {
        $letters = preg_replace('/[^\pL]/u', '', $line) ?? '';

        return mb_strlen($line) <= 70
            && mb_strlen($letters) >= 3
            && mb_strtoupper($line) === $line
            && ! preg_match(self::BULLET_PATTERN, $line);
    }

    private static function headingText(string $line): string
    {
        $lower = mb_strtolower(rtrim($line, ':'));
        $text = mb_strtoupper(mb_substr($lower, 0, 1)).mb_substr($lower, 1);

        return strtr($text, [
            "st. john's community health" => "St. John's Community Health",
            "st. john's" => "St. John's",
        ]);
    }
}
