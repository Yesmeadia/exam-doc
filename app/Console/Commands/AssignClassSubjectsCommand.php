<?php

namespace App\Console\Commands;

use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Subject;
use Illuminate\Console\Command;

class AssignClassSubjectsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subjects:assign-class
                            {class=10TH : Class name or pattern, e.g. 10TH, 10}
                            {--subjects=English,Mathematics,Science,SST,URDU : Comma-separated list of subject names or codes}
                            {--dry-run : Preview the assignments without saving changes to the database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign curriculum subjects (compulsory) to all sections of a specified class';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $className = $this->argument('class');
        $subjectInput = $this->option('subjects');
        $isDryRun = (bool) $this->option('dry-run');

        // 1. Find the class
        $class = SchoolClass::where('name', $className)
            ->orWhere('name', 'like', "%{$className}%")
            ->first();

        if (!$class) {
            $this->error("Class '{$className}' not found in database.");
            $allClasses = SchoolClass::pluck('name')->implode(', ');
            $this->line("Available classes: {$allClasses}");
            return Command::FAILURE;
        }

        $this->info("Target Class: [ID: {$class->id}] {$class->name}");

        // 2. Resolve requested subjects
        $subjectNames = array_filter(array_map('trim', explode(',', $subjectInput)));
        $resolvedSubjects = collect();
        $missingSubjects = [];

        foreach ($subjectNames as $name) {
            $sub = Subject::whereRaw('LOWER(name) = ?', [strtolower($name)])
                ->orWhereRaw('LOWER(code) = ?', [strtolower($name)])
                ->first();

            if ($sub) {
                $resolvedSubjects->push($sub);
            } else {
                // Try partial match
                $subPartial = Subject::where('name', 'like', "%{$name}%")
                    ->orWhere('code', 'like', "%{$name}%")
                    ->first();

                if ($subPartial) {
                    $resolvedSubjects->push($subPartial);
                } else {
                    $missingSubjects[] = $name;
                }
            }
        }

        if (!empty($missingSubjects)) {
            $this->warn("The following subjects were not found in the subjects table: " . implode(', ', $missingSubjects));
            $this->line("Available subjects: " . Subject::pluck('name')->implode(', '));
        }

        if ($resolvedSubjects->isEmpty()) {
            $this->error("No matching subjects found to assign.");
            return Command::FAILURE;
        }

        $subjectTable = $resolvedSubjects->map(fn ($s) => [
            'ID' => $s->id,
            'Name' => $s->name,
            'Code' => $s->code,
            'Max Marks' => $s->maximum_marks,
            'Pass Marks' => $s->pass_marks,
        ]);
        $this->table(['ID', 'Name', 'Code', 'Max Marks', 'Pass Marks'], $subjectTable);

        // 3. Find sections for this class
        $sections = Section::where('class_id', $class->id)->orderBy('name')->get();

        if ($sections->isEmpty()) {
            $this->warn("No sections found under class '{$class->name}'.");
            return Command::FAILURE;
        }

        $this->info("Found " . $sections->count() . " sections for class {$class->name}: " . $sections->pluck('name')->implode(', '));

        if ($isDryRun) {
            $this->warn("\n[DRY RUN] No changes were written to the database.");
            $this->line("Run without --dry-run to commit these assignments.");
            return Command::SUCCESS;
        }

        // 4. Sync subjects to Class
        $class->subjects()->syncWithoutDetaching($resolvedSubjects->pluck('id')->toArray());

        // 5. Sync subjects to each Section as Compulsory (is_optional = false)
        $syncData = [];
        foreach ($resolvedSubjects as $sub) {
            $syncData[$sub->id] = ['is_optional' => false];
        }

        $updatedCount = 0;
        foreach ($sections as $section) {
            $section->subjects()->sync($syncData);
            $this->line(" -> Section [{$section->id}] {$section->name}: Synced " . count($syncData) . " subjects.");
            $updatedCount++;
        }

        $this->newLine();
        $this->info("Successfully assigned " . $resolvedSubjects->count() . " subjects across {$updatedCount} sections of Class {$class->name}!");

        return Command::SUCCESS;
    }
}
