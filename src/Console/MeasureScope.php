<?php

namespace Laravel\Surveyor\Console;

use Illuminate\Console\Command;
use Laravel\Surveyor\Analyzer\Analyzer;

class MeasureScope extends Command
{
    protected $signature = 'measure:scope {path}';

    protected $description = 'Measure the memory size of a Scope object';

    public function handle(Analyzer $analyzer)
    {
        $path = getcwd().'/'.$this->argument('path');

        $this->info('Analyzing: '.$path);
        $this->newLine();

        $beforeMemory = memory_get_usage(true);

        $analyzer->analyze($path);
        $scope = $analyzer->analyzed();

        $afterMemory = memory_get_usage(true);

        if (! $scope) {
            $this->error('No scope analyzed');

            return 1;
        }

        // Measure the entire scope with all children
        $this->line('=== Scope Size Analysis ===');
        $fullSize = strlen(serialize($scope));
        $this->info('Full Scope (serialized): '.$this->formatBytes($fullSize));
        $this->info('Memory increase: '.$this->formatBytes($afterMemory - $beforeMemory));
        $this->newLine();

        // Basic info
        $this->line('=== Basic Info ===');
        $this->line('Entity: '.($scope->entityName() ?? 'unknown'));
        $this->line('Children: '.count($scope->children()));
        $this->line('Path: '.$scope->path());
        $this->newLine();

        // Measure components
        $this->line('=== Component Breakdown ===');

        $stateTrackerSize = strlen(serialize($scope->state()));
        $this->line('StateTracker: '.$this->formatBytes($stateTrackerSize).' ('.$this->percentage($stateTrackerSize, $fullSize).')');

        $childrenSize = strlen(serialize($scope->children()));
        $this->line('Children: '.$this->formatBytes($childrenSize).' ('.$this->percentage($childrenSize, $fullSize).')');

        $constantsSize = strlen(serialize($scope->constants()));
        $this->line('Constants: '.$this->formatBytes($constantsSize));

        $usesSize = strlen(serialize($scope->uses()));
        $this->line('Uses: '.$this->formatBytes($usesSize));

        $returnTypesSize = strlen(serialize($scope->returnTypes()));
        $this->line('Return Types: '.$this->formatBytes($returnTypesSize));

        $this->newLine();

        // Variable state analysis
        $this->line('=== Variable State Analysis ===');

        $variables = $scope->state()->variables()->variables();
        $properties = $scope->state()->properties()->variables();

        $totalVarStates = 0;
        $totalPropStates = 0;

        foreach ($variables as $name => $states) {
            $count = count($states);
            $totalVarStates += $count;
            if ($count > 5) {
                $this->line("  Variable '{$name}': {$count} states");
            }
        }

        foreach ($properties as $name => $states) {
            $count = count($states);
            $totalPropStates += $count;
            if ($count > 5) {
                $this->line("  Property '{$name}': {$count} states");
            }
        }

        $this->info('Total variable states: '.$totalVarStates);
        $this->info('Total property states: '.$totalPropStates);
        $this->info('Total combined: '.($totalVarStates + $totalPropStates));
        $this->newLine();

        // Child scope analysis
        if (count($scope->children()) > 0) {
            $this->line('=== Child Scope Analysis ===');

            $totalChildStates = 0;
            $childSizes = [];

            foreach ($scope->children() as $index => $child) {
                $childSize = strlen(serialize($child));
                $childSizes[] = $childSize;

                $childVars = $child->state()->variables()->variables();
                $childProps = $child->state()->properties()->variables();

                $childVarCount = array_sum(array_map('count', $childVars));
                $childPropCount = array_sum(array_map('count', $childProps));
                $totalChildStates += $childVarCount + $childPropCount;

                $name = $child->methodName() ?? $child->entityName() ?? "child_{$index}";

                $this->line(sprintf(
                    '  %s: %s | %d vars, %d props (%d total states)',
                    $name,
                    $this->formatBytes($childSize),
                    $childVarCount,
                    $childPropCount,
                    $childVarCount + $childPropCount
                ));
            }

            $avgChildSize = array_sum($childSizes) / count($childSizes);
            $maxChildSize = max($childSizes);

            $this->newLine();
            $this->line('Average child size: '.$this->formatBytes($avgChildSize));
            $this->line('Largest child size: '.$this->formatBytes($maxChildSize));
            $this->line('Total child states: '.$totalChildStates);
            $this->newLine();
        }

        // Estimate what's using memory
        $this->line('=== Memory Usage Estimate ===');
        $parentStates = $totalVarStates + $totalPropStates;
        $this->line("Parent scope states: {$parentStates}");
        $this->line("Child scope states: {$totalChildStates}");
        $this->line('Total states: '.($parentStates + $totalChildStates));

        $avgBytesPerState = ($parentStates + $totalChildStates) > 0
            ? $fullSize / ($parentStates + $totalChildStates)
            : 0;

        $this->line('Avg bytes per state: '.round($avgBytesPerState, 2));
        $this->newLine();

        // Suggestions
        if ($totalVarStates > 100) {
            $this->warn('⚠️  High variable state count detected. Consider if full history is needed.');
        }

        if ($totalChildStates > 500) {
            $this->warn('⚠️  High child state count. Check if parent data is being duplicated.');
        }

        if ($fullSize > 10 * 1024 * 1024) {
            $this->error('🔴 Scope is very large (>10MB). This is likely causing memory issues.');
        } elseif ($fullSize > 5 * 1024 * 1024) {
            $this->warn('⚠️  Scope is large (>5MB). This could contribute to memory issues.');
        } else {
            $this->info('✅ Scope size seems reasonable.');
        }
    }

    protected function formatBytes(int|float $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes.' B';
        } elseif ($bytes < 1024 * 1024) {
            return round($bytes / 1024, 2).' KB';
        } else {
            return round($bytes / 1024 / 1024, 2).' MB';
        }
    }

    protected function percentage(int|float $part, int|float $total): string
    {
        if ($total == 0) {
            return '0%';
        }

        return round(($part / $total) * 100, 1).'%';
    }
}
