<?php

namespace Stillat\BladeParser\Console\Commands;

use Illuminate\Console\Command;
use Stillat\BladeParser\Document\Document;
use Stillat\BladeParser\Errors\BladeError;
use Stillat\BladeParser\Errors\ErrorFamily;
use Stillat\BladeParser\Validation\Workspaces\PhpStanWrapper;
use Stillat\BladeParser\Workspaces\Workspace;

class ValidateBladeCommand extends Command
{
    protected $signature = 'blade:validate';

    private function formatPeriod(float $endTime, float $startTime): string
    {
        $duration = $endTime - $startTime;

        $hours = (int) ($duration / 60 / 60);

        $minutes = (int) ($duration / 60) - $hours * 60;

        $seconds = (int) $duration - $hours * 60 * 60 - $minutes * 60;

        return ($hours == 0 ? '00' : $hours).':'.($minutes == 0 ? '00' : ($minutes < 10 ? '0'.$minutes : $minutes)).':'.($seconds == 0 ? '00' : ($seconds < 10 ? '0'.$seconds : $seconds));
    }

    public function handle(Workspace $workspace)
    {
        $totalStart = microtime(true);
        $bladeStart = microtime(true);
        $workspace->withCoreValidators()
            ->ignoreDirectives(config('blade.validation.ignore_directives', []))
            ->addDirectory(resource_path('views'));

        $runMessage = "Running {$workspace->validator()->getValidatorCount()} validators against {$workspace->getFileCount()} files.";

        if (PhpStanWrapper::canRun()) {
            $runMessage = mb_substr($runMessage, 0, -1);
            $runMessage .= '; PHPStan document validation enabled.';
        }

        $this->comment($runMessage);

        $workspace->validate();
        $bladeEnd = microtime(true);

        $phpStanStart = microtime(true);
        if (PhpStanWrapper::canRun()) {
            $this->runStaticAnalysisOnWorkspace($workspace);
        }
        $phpStanEnd = microtime(true);
        $totalEnd = microtime(true);

        $bladePeriod = $this->formatPeriod($bladeEnd, $bladeStart);
        $phpStanPeriod = $this->formatPeriod($phpStanEnd, $phpStanStart);
        $totalPeriod = $this->formatPeriod($totalEnd, $totalStart);

        /** @var Document $doc */
        foreach ($workspace->getDocuments() as $doc) {
            if (! $doc->hasErrors()) {
                continue;
            }

            $this->info(mb_substr($doc->getFilePath(), mb_strlen(base_path())));

            /** @var BladeError $error */
            foreach ($doc->getErrors()->sortBy(fn (BladeError $e) => $e->position->startLine) as $error) {
                if ($error->family == ErrorFamily::Validation) {
                    $this->warn('    '.$error->getErrorMessage());
                } else {
                    $this->error('    '.$error->getErrorMessage());
                }
            }
        }

        $finalMessage = "Analyzed {$workspace->getFileCount()} files in {$totalPeriod}";

        if (PhpStanWrapper::canRun()) {
            $finalMessage .= " (Blade Parser: {$bladePeriod} PHPStan: {$phpStanPeriod})";
        }

        $this->comment($finalMessage);
    }

    protected function runStaticAnalysisOnWorkspace(Workspace $workspace)
    {
        $workingDirectory = storage_path('blade_parser');
        if (! file_exists($workingDirectory)) {
            mkdir($workingDirectory, 0755);
        }

        /** @var PhpStanWrapper $wrapper */
        $wrapper = app(PhpStanWrapper::class);
        $wrapper->setDirectory($workingDirectory);

        $wrapper->checkWorkspace($workspace);
    }
}
