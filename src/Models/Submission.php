<?php

declare(strict_types=1);

namespace Magna\Forms\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * One submission. The IP is stored HASHED (with the app key as the salt)
 * — enough to rate-limit and spot a flood, never enough to be a stored
 * personal identifier.
 *
 * @property string $id
 * @property string $form_id
 * @property array<string, string> $data
 * @property string|null $ip_hash
 * @property Carbon|null $created_at
 */
class Submission extends Model
{
    use HasUlids;

    public const UPDATED_AT = null;

    protected $table = 'forms_submissions';

    protected $fillable = ['form_id', 'data', 'ip_hash'];

    protected function casts(): array
    {
        return ['data' => 'array'];
    }

    public static function hashIp(?string $ip): ?string
    {
        return $ip === null || $ip === '' ? null : hash_hmac('sha256', $ip, (string) config('app.key'));
    }
}
