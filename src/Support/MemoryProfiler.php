<?php

namespace Laravel\Surveyor\Support;

class MemoryProfiler
{
    public static function measure(mixed $value): array
    {
        $startMemory = memory_get_usage(true);

        // Method 1: Serialization size (approximate actual size)
        $serialized = @serialize($value);
        $serializedSize = $serialized ? strlen($serialized) : 0;

        // Method 2: JSON size (alternative approximation)
        $json = @json_encode($value, JSON_PARTIAL_OUTPUT_ON_ERROR);
        $jsonSize = $json ? strlen($json) : 0;

        // Method 3: Deep array/object analysis
        $structure = static::analyzeStructure($value);

        return [
            'serialized_bytes' => $serializedSize,
            'serialized_mb' => round($serializedSize / 1024 / 1024, 3),
            'json_bytes' => $jsonSize,
            'json_mb' => round($jsonSize / 1024 / 1024, 3),
            'structure' => $structure,
            'human_readable' => static::formatBytes($serializedSize),
        ];
    }

    public static function measureArray(array $array): array
    {
        $totalSize = 0;
        $entries = count($array);
        $breakdown = [];

        foreach ($array as $key => $value) {
            $size = strlen(@serialize($value));
            $totalSize += $size;
            $breakdown[] = [
                'key' => static::truncate($key, 50),
                'type' => static::getType($value),
                'size' => $size,
                'size_formatted' => static::formatBytes($size),
            ];
        }

        // Sort by size descending
        usort($breakdown, fn ($a, $b) => $b['size'] <=> $a['size']);

        return [
            'entries' => $entries,
            'total_bytes' => $totalSize,
            'total_mb' => round($totalSize / 1024 / 1024, 3),
            'avg_bytes' => $entries > 0 ? round($totalSize / $entries) : 0,
            'avg_formatted' => $entries > 0 ? static::formatBytes($totalSize / $entries) : '0 B',
            'largest_entries' => array_slice($breakdown, 0, 10),
        ];
    }

    public static function compareCaches(array $caches): array
    {
        $results = [];
        $total = 0;

        foreach ($caches as $name => $cache) {
            $size = is_array($cache)
                ? strlen(@serialize($cache))
                : strlen(@serialize([$cache]));

            $results[$name] = [
                'entries' => is_array($cache) ? count($cache) : 1,
                'bytes' => $size,
                'formatted' => static::formatBytes($size),
                'mb' => round($size / 1024 / 1024, 3),
            ];

            $total += $size;
        }

        // Add percentages
        foreach ($results as $name => $data) {
            $results[$name]['percentage'] = $total > 0
                ? round(($data['bytes'] / $total) * 100, 2).'%'
                : '0%';
        }

        $results['_total'] = [
            'bytes' => $total,
            'formatted' => static::formatBytes($total),
            'mb' => round($total / 1024 / 1024, 3),
        ];

        return $results;
    }

    protected static function analyzeStructure(mixed $value): array
    {
        if (is_array($value)) {
            return [
                'type' => 'array',
                'count' => count($value),
                'depth' => static::getArrayDepth($value),
            ];
        }

        if (is_object($value)) {
            return [
                'type' => 'object',
                'class' => get_class($value),
                'properties' => count(get_object_vars($value)),
            ];
        }

        if (is_string($value)) {
            return [
                'type' => 'string',
                'length' => strlen($value),
            ];
        }

        return [
            'type' => gettype($value),
        ];
    }

    protected static function getArrayDepth(array $array): int
    {
        $maxDepth = 1;

        foreach ($array as $value) {
            if (is_array($value)) {
                $depth = static::getArrayDepth($value) + 1;
                $maxDepth = max($maxDepth, $depth);
            }
        }

        return $maxDepth;
    }

    protected static function getType(mixed $value): string
    {
        if (is_object($value)) {
            return get_class($value);
        }

        if (is_array($value)) {
            return 'array['.count($value).']';
        }

        return gettype($value);
    }

    protected static function truncate(string $str, int $length): string
    {
        if (strlen($str) <= $length) {
            return $str;
        }

        return substr($str, 0, $length - 3).'...';
    }

    protected static function formatBytes(int|float $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2).' '.$units[$pow];
    }
}
