<?php

namespace Waad\Metadata\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

class Metadata extends Model
{
    use HasUlids;

    protected $table = 'model_metadata';

    protected $guarded = [];

    protected $casts = [
        'metadata' => 'json',
    ];

    public function metadatable(): MorphTo
    {
        return $this->morphTo();
    }

    protected function asDateTime($value)
    {
        if (! $value instanceof Carbon) {
            $value = parent::asDateTime($value);
        }

        $value->setTimezone(date_default_timezone_get());

        return $value;
    }

    protected function asJson($value, $flags = 0)
    {
        // For Laravel 12.3+, the method signature includes a second parameter
        // For older versions, we'll just ignore the second parameter
        return json_encode($value, JSON_UNESCAPED_UNICODE | $flags);
    }

    public function mergeIdToMetadata(string $keyNameId = 'id'): self
    {
        $this->metadata = array_merge([$keyNameId => $this->id], $this->metadata ?? []);

        return $this;
    }
}
