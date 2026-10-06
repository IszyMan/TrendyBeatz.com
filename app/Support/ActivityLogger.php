<?php

namespace App\Support;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ActivityLogger
{
    public static function record(
        string $action,
        string $type,
        int|string $id,
        ?string $title = null,
        array $changes = []
    ): void {
        $verbs = [
            'created' => 'Created',
            'updated' => 'Updated',
            'deleted' => 'Deleted',
            'published' => 'Published',
            'unpublished' => 'Unpublished',
            'approved' => 'Approved',
            'activated' => 'Activated',
            'deactivated' => 'Deactivated',
            'role_changed' => 'Changed role for',
            'password_changed' => 'Changed password for',
        ];

        if (!isset($verbs[$action])) {
            throw new InvalidArgumentException('Unknown activity action.');
        }

        $title = trim((string) $title);

        $description = $verbs[$action] . ' ' . $type;

        if ($title !== '') {
            $description .= ' "' . $title . '"';
        }

        $description .= ' (ID: ' . $id . ').';

        // Pass only readable, non-sensitive changes.
        foreach ($changes as $field => $values) {
            $description .= sprintf(
                ' %s: %s → %s.',
                $field,
                self::readable($values['old'] ?? null),
                self::readable($values['new'] ?? null)
            );
        }

        DB::table('activity_logs')->insert([
            'user_id' => Auth::id(),
            'activity' => $type . '.' . $action,
            'description' => $description,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private static function readable(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '(empty)';
        }

        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        return (string) $value;
    }
}