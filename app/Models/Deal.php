<?php

namespace App\Models;

use App\Enums\DealStage;
use Database\Factories\DealFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'contact_id', 'company_id', 'title', 'value', 'stage', 'expected_close_date', 'closed_at', 'notes'])]
class Deal extends Model
{
    /** @use HasFactory<DealFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'stage' => DealStage::class,
            'expected_close_date' => 'date',
            'closed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    public function aiInsights(): HasMany
    {
        return $this->hasMany(AiInsight::class);
    }
}
