<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'company_id',
    'key',
    'name',
    'category',
    'channel',
    'subject',
    'content',
    'variables',
    'status',
])]
class NotificationTemplate extends Model
{
    protected function casts(): array
    {
        return [
            'variables' => 'array',
        ];
    }

    /**
     * @return BelongsTo<Company, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
