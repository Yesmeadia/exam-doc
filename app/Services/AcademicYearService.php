<?php

namespace App\Services;

use App\Models\AcademicYear;
use Illuminate\Support\Facades\DB;

class AcademicYearService
{
    /**
     * Get currently active academic year.
     */
    public function getActiveYear(): ?AcademicYear
    {
        return AcademicYear::active()->first();
    }

    /**
     * Set a specific academic year as active, deactivating others.
     */
    public function setActiveYear(AcademicYear $year): void
    {
        DB::transaction(function () use ($year) {
            AcademicYear::where('id', '!=', $year->id)->update(['is_active' => false]);
            $year->update(['is_active' => true]);
        });

        AuditLogService::log('academic_year_activated', $year, ['name' => $year->name]);
    }

    /**
     * Create a new academic year.
     */
    public function create(array $data): AcademicYear
    {
        return DB::transaction(function () use ($data) {
            $isActive = !empty($data['is_active']);

            if ($isActive) {
                AcademicYear::query()->update(['is_active' => false]);
            }

            $year = AcademicYear::create($data);

            AuditLogService::log('academic_year_created', $year, ['name' => $year->name]);

            return $year;
        });
    }

    /**
     * Update an academic year.
     */
    public function update(AcademicYear $year, array $data): AcademicYear
    {
        return DB::transaction(function () use ($year, $data) {
            $isActive = !empty($data['is_active']);

            if ($isActive) {
                AcademicYear::where('id', '!=', $year->id)->update(['is_active' => false]);
            }

            $year->update($data);

            AuditLogService::log('academic_year_updated', $year, ['name' => $year->name]);

            return $year;
        });
    }
}
