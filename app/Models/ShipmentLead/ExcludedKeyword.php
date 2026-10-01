<?php

namespace App\Models\ShipmentLead;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class ExcludedKeyword extends Model
{
    use HasFactory;

    protected $table = 'shipment_excluded_keywords';

    protected $fillable = [
        'keyword',
        'description',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Clean and normalize keyword attribute on set.
     */
    public function setKeywordAttribute($value): void
    {
        $this->attributes['keyword'] = strtolower(trim((string) $value));
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Invalidate cache whenever keywords are changed or deleted.
     */
    protected static function booted()
    {
        static::saved(function () {
            Cache::forget('shipment_active_excluded_keywords');
        });
        static::deleted(function () {
            Cache::forget('shipment_active_excluded_keywords');
        });
    }

    /**
     * Retrieve all active excluded keywords / phrases.
     *
     * @return array<string>
     */
    public static function getActiveKeywordList(): array
    {
        try {
            return Cache::remember('shipment_active_excluded_keywords', 300, function () {
                return static::where('is_active', true)
                    ->pluck('keyword')
                    ->map(fn($k) => strtolower(trim($k)))
                    ->filter()
                    ->values()
                    ->toArray();
            });
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Check if a given email subject contains any active excluded keywords or phrases.
     */
    public static function isSubjectExcluded(?string $subject): bool
    {
        if (empty($subject)) {
            return false;
        }

        $subjectLower = strtolower(trim($subject));
        $keywords = static::getActiveKeywordList();

        foreach ($keywords as $kw) {
            $kw = strtolower(trim($kw));
            if (empty($kw)) {
                continue;
            }

            if (str_contains($subjectLower, $kw)) {
                return true;
            }
        }

        return false;
    }
}
