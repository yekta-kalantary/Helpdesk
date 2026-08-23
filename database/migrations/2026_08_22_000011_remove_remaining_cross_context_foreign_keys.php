<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Reference columns whose foreign keys crossed bounded-context boundaries.
     *
     * @var array<string, list<string>>
     */
    private const CROSS_CONTEXT_REFERENCE_COLUMNS = [
        'task_comments' => ['user_id', 'hidden_by'],
        'attachments' => ['uploaded_by', 'hidden_by'],
        'task_checklist_items' => ['created_by'],
        'activities' => ['actor_id', 'project_id', 'task_id'],
    ];

    public function up(): void
    {
        foreach (self::CROSS_CONTEXT_REFERENCE_COLUMNS as $tableName => $referenceColumns) {
            $this->dropForeignKeysOnReferenceColumns($tableName, $referenceColumns);
            $this->addMissingReferenceColumnIndexes($tableName, $referenceColumns);
        }
    }

    /**
     * The boundary repair is forward-only: every reference value is preserved,
     * and fresh installations no longer define these foreign keys at all.
     */
    public function down(): void {}

    /**
     * @param  list<string>  $referenceColumns
     */
    private function dropForeignKeysOnReferenceColumns(string $tableName, array $referenceColumns): void
    {
        $constraintNames = [];

        foreach (Schema::getForeignKeys($tableName) as $foreignKey) {
            if (array_intersect($foreignKey['columns'], $referenceColumns) !== []) {
                $constraintNames[] = $foreignKey['name'];
            }
        }

        if ($constraintNames === []) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($constraintNames): void {
            foreach ($constraintNames as $constraintName) {
                $table->dropForeign($constraintName);
            }
        });
    }

    /**
     * @param  list<string>  $referenceColumns
     */
    private function addMissingReferenceColumnIndexes(string $tableName, array $referenceColumns): void
    {
        $coveredColumns = [];

        foreach (Schema::getIndexes($tableName) as $index) {
            $leftmostColumn = $index['columns'][0] ?? null;

            if ($leftmostColumn !== null) {
                $coveredColumns[$leftmostColumn] = true;
            }
        }

        $uncoveredColumns = array_values(array_diff($referenceColumns, array_keys($coveredColumns)));

        if ($uncoveredColumns === []) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($uncoveredColumns): void {
            foreach ($uncoveredColumns as $columnName) {
                $table->index($columnName);
            }
        });
    }
};
