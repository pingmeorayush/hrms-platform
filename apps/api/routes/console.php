<?php

use App\Modules\EmployeeManagement\Services\EmployeeDirectoryBenchmarkService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command(
    'benchmark:employee-directory {--employees=100000 : Number of employee rows to benchmark against} {--iterations=5 : Number of measured runs per scenario} {--keep-dataset : Skip benchmark dataset cleanup after the run}',
    function (EmployeeDirectoryBenchmarkService $benchmarkService): void {
        $employeeCount = max((int) $this->option('employees'), 2);
        $iterations = max((int) $this->option('iterations'), 1);

        $this->info(sprintf(
            'Running employee directory benchmark with %d employees and %d iterations per scenario...',
            $employeeCount,
            $iterations,
        ));

        /** @var array{
         *     employee_count:int,
         *     iterations:int,
         *     seed_duration_ms:float,
         *     scenarios:array<int, array{
         *         name:string,
         *         result_count:int,
         *         average_ms:float,
         *         p95_ms:float
         *     }>,
         *     worst_p95_ms:float,
         *     nfr_limit_ms:float,
         *     within_nfr:bool
         * } $results
         */
        $results = $benchmarkService->run(
            employeeCount: $employeeCount,
            iterations: $iterations,
            cleanup: ! (bool) $this->option('keep-dataset'),
        );

        $this->table(
            ['Scenario', 'Results', 'Average (ms)', 'P95 (ms)'],
            collect($results['scenarios'])
                ->map(fn (array $scenario): array => [
                    $scenario['name'],
                    $scenario['result_count'],
                    number_format($scenario['average_ms'], 2),
                    number_format($scenario['p95_ms'], 2),
                ])
                ->all(),
        );

        $this->newLine();
        $this->line('Seed duration (ms): '.number_format($results['seed_duration_ms'], 2));
        $this->line('Worst P95 (ms): '.number_format($results['worst_p95_ms'], 2));
        $this->line('NFR limit (ms): '.number_format($results['nfr_limit_ms'], 2));
        $this->info($results['within_nfr'] ? 'Result: PASS' : 'Result: FAIL');
    },
)->purpose('Benchmark employee directory search performance against a large dataset');
