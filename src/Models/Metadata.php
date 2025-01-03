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
}