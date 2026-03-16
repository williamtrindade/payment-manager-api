<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $name
 * @property bool $is_active
 * @property int $priority
 * @property string $api_url
 * @property string $api_token
 * @property string $client_id
 * @property string $client_secret
 */
class Gateway extends Model
{
    protected $fillable = ['name', 'is_active', 'priority', 'api_url', 'api_token'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function scopeActiveAndPrioritized($query)
    {
        return $query
            ->where('is_active', true)
            ->orderBy('priority', 'asc');
    }
}
