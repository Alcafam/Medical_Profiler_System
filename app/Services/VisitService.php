<?php

namespace App\Services;

use App\Models\Client;
use App\Models\ClientFieldValue;
use App\Models\FormField;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Support\Facades\DB;

class VisitService
{
    public function createForClient(Client $client, User $user, bool $copyIdentityFromLatest = true): Visit
    {
        return DB::transaction(function () use ($client, $user, $copyIdentityFromLatest) {
            $previous = $client->visits()
                ->with(['fieldValues.formField'])
                ->latest('visited_at')
                ->latest('id')
                ->first();

            $visit = Visit::query()->create([
                'client_id' => $client->id,
                'visited_at' => now(),
                'created_by' => $user->id,
            ]);

            if ($copyIdentityFromLatest && $previous) {
                $this->copyIdentityFields($previous, $visit, $user);
            }

            return $visit;
        });
    }

    public function ensureLatestVisit(Client $client, User $user): Visit
    {
        $latest = $client->latestVisit;

        if ($latest) {
            return $latest;
        }

        return $this->createForClient($client, $user, copyIdentityFromLatest: false);
    }

    public function bulkCreateForAllClients(User $user): int
    {
        $created = 0;

        Client::query()
            ->orderBy('id')
            ->each(function (Client $client) use ($user, &$created) {
                $this->createForClient($client, $user, copyIdentityFromLatest: true);
                $created++;
            });

        return $created;
    }

    private function copyIdentityFields(Visit $from, Visit $to, User $user): void
    {
        $slugs = Visit::COPY_SLUGS;
        $fields = FormField::query()->whereIn('slug', $slugs)->get()->keyBy('slug');

        foreach ($slugs as $slug) {
            $field = $fields->get($slug);
            if (! $field) {
                continue;
            }

            $source = $from->fieldValues->firstWhere('form_field_id', $field->id);
            if (! $source || $source->value === null || trim((string) $source->value) === '') {
                continue;
            }

            ClientFieldValue::query()->create([
                'client_id' => $to->client_id,
                'visit_id' => $to->id,
                'form_field_id' => $field->id,
                'value' => $source->value,
                'version' => 1,
                'updated_by' => $user->id,
            ]);
        }
    }
}
