<?php

namespace App\Models;

use App\Modules\Platform\Tenancy\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $company_id
 * @property int $user_id
 * @property int|null $notification_template_id
 * @property string $type
 * @property string $channel
 * @property string $title
 * @property string $message
 * @property string $priority
 * @property string $status
 * @property string $delivery_status
 * @property string|null $deep_link
 * @property int $retry_count
 * @property string|null $last_error
 * @property array<string, mixed>|null $data
 * @property-read User|null $user
 * @property-read NotificationTemplate|null $template
 */
#[Fillable([
    'company_id',
    'user_id',
    'notification_template_id',
    'type',
    'channel',
    'title',
    'message',
    'priority',
    'status',
    'delivery_status',
    'deep_link',
    'retry_count',
    'last_error',
    'data',
    'read_at',
    'delivered_at',
])]
class NotificationRecord extends Model
{
    use BelongsToCompany;

    protected $table = 'notifications';

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'read_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<NotificationTemplate, $this>
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(NotificationTemplate::class, 'notification_template_id');
    }
}
