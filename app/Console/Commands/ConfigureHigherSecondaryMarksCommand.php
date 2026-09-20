<?php

namespace App\Console\Commands;

use App\Models\Subject;
use Illuminate\Console\Command;

class ConfigureHigherSecondaryMarksCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subjects:set-higher-secondary-marks
                            {--dry-run : Preview subject mark updates without applying}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set standard maximum and pass marks for 11th and 12th class subjects (Physics/Chemistry/Biology: 70/24, EVS: 50/17, others: 100/33)';

    /**
     * Subject mark configuration rules based on name keywords.
     *
     * @var array<string, array{max: float, pass: float}>
     */
    protected array $markRules = [
        'physics' => ['max' => 70.0, 'pass' => 24.0],
        'chemistry' => ['max' => 70.0, 'pass' => 24.0],
        'biology' => ['max' => 70.0, 'pass' => 24.0],
        'bio' => ['max' => 70.0, 'pass' => 24.0],
        'environmental science' => ['max' => 50.0, 'pass' => 17.0],
        'evs' => ['max' => 50.0, 'pass' => 17.0],
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $isDryRun = (bool) $this->option('dry-run');

        $subjects = Subject::all();

        if ($subjects->isEmpty()) {
            $this->warn('No subjects found in the database.');
            return Command::SUCCESS;
        }

        $rows = [];
        $updates = [];

        foreach ($subjects as $subject) {
            $subName = strtolower(trim((string) $subject->name));
            $targetMax = 100.0;
            $targetPass = 33.0;

            foreach ($this->markRules as $keyword => $rule) {
                if (preg_match('/\b' . preg_quote($keyword, '/') . '\b/i', $subName)) {
                    $targetMax = $rule['max'];
                    $targetPass = $rule['pass'];
                    break;
                }
            }

            $currentMax = (float) $subject->maximum_marks;
            $currentPass = (float) $subject->pass_marks;
            $changed = ($currentMax !== $targetMax || $currentPass !== $targetPass);

            if ($changed) {
                $updates[] = [
                    'model' => $subject,
                    'target_max' => $targetMax,
                    'target_pass' => $targetPass,
                ];
            }

            $rows[] = [
                'ID' => $subject->id,
                'Name' => $subject->name,
                'Current Max/Pass' => "{$currentMax} / {$currentPass}",
                'Target Max/Pass' => "{$targetMax} / {$targetPass}",
                'Status' => $changed ? ($isDryRun ? 'Pending Update' : 'Updated') : 'Unchanged',
            ];
        }

        $this->table(['ID', 'Name', 'Current Max/Pass', 'Target Max/Pass', 'Status'], $rows);

        if ($isDryRun) {
            $this->warn("\n[DRY RUN] Found " . count($updates) . " subject(s) to update. No database changes were saved.");
            $this->line("Run without --dry-run to commit these updates.");
            return Command::SUCCESS;
        }

        foreach ($updates as $item) {
            $item['model']->update([
                'maximum_marks' => $item['target_max'],
                'pass_marks' => $item['target_pass'],
            ]);
        }

        $this->info("\nSuccessfully updated " . count($updates) . " subject(s) with standard Higher Secondary maximum and pass marks!");

        return Command::SUCCESS;
    }
}
