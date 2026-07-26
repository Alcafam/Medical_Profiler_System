<?php

namespace App\Services;

use App\Models\ClientFieldValue;
use App\Models\FormField;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Validation\ValidationException;

class ClientFieldValueService
{
    /**
     * @return array{status: string, value: ClientFieldValue, conflict?: array<string, mixed>}
     */
    public function save(
        Visit $visit,
        FormField $field,
        User $user,
        ?string $value,
        ?int $expectedVersion = null,
        bool $force = false,
    ): array {
        if ($user->isEncoder() && ! $field->is_active) {
            abort(403);
        }

        $record = ClientFieldValue::query()->firstOrNew([
            'visit_id' => $visit->id,
            'form_field_id' => $field->id,
        ]);

        if (! $record->exists) {
            $record->client_id = $visit->client_id;
        }

        $currentVersion = $record->exists ? $record->version : 0;

        if (
            ! $force
            && $record->exists
            && $expectedVersion !== null
            && $expectedVersion !== $currentVersion
            && (string) $record->value !== (string) $value
        ) {
            $record->load('editor:id,name');

            return [
                'status' => 'conflict',
                'value' => $record,
                'conflict' => [
                    'current_value' => $record->value,
                    'current_version' => $record->version,
                    'updated_by' => $record->editor?->name,
                    'updated_at' => optional($record->updated_at)?->toDateTimeString(),
                    'your_value' => $value,
                ],
            ];
        }

        $record->client_id = $visit->client_id;
        $record->value = $value;
        $record->updated_by = $user->id;
        $record->version = $record->exists ? $record->version + 1 : 1;
        $record->save();
        $record->load('editor:id,name');

        return [
            'status' => 'saved',
            'value' => $record,
        ];
    }

    public function assertCanEdit(User $user, FormField $field): void
    {
        if ($user->canUseGrid() || $user->isSuperAdmin() || $user->isAdmin() || $user->isEncoder()) {
            return;
        }

        throw ValidationException::withMessages([
            'field' => 'You do not have permission to edit this field.',
        ]);
    }
}
