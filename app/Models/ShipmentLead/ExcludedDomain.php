<?php

namespace App\Models\ShipmentLead;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class ExcludedDomain extends Model
{
    use HasFactory;

    protected $table = 'shipment_excluded_domains';

    protected $fillable = [
        'domain',
        'description',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Clean and normalize domain attribute on set.
     */
    public function setDomainAttribute($value): void
    {
        $domain = strtolower(trim((string) $value));
        // Remove scheme if present
        $domain = preg_replace('#^https?://#i', '', $domain);
        // Remove leading @
        $domain = ltrim($domain, '@');
        // Remove www.
        if (str_starts_with($domain, 'www.')) {
            $domain = substr($domain, 4);
        }
        // Remove any path/trailing slash
        $domain = explode('/', $domain)[0];
        $this->attributes['domain'] = trim($domain);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Clear cached active domains on save/delete.
     */
    protected static function booted()
    {
        static::saved(function () {
            Cache::forget('shipment_active_excluded_domains');
        });
        static::deleted(function () {
            Cache::forget('shipment_active_excluded_domains');
        });
    }

    /**
     * Retrieve all active excluded domain strings.
     *
     * @return array<string>
     */
    public static function getActiveDomainList(): array
    {
        try {
            return Cache::remember('shipment_active_excluded_domains', 300, function () {
                return static::where('is_active', true)
                    ->pluck('domain')
                    ->map(fn($d) => strtolower(trim($d)))
                    ->filter()
                    ->values()
                    ->toArray();
            });
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Check if a given email address or domain is in the exclusion list.
     */
    public static function isDomainExcluded(?string $emailOrDomain): bool
    {
        if (empty($emailOrDomain)) {
            return false;
        }

        $input = strtolower(trim($emailOrDomain));

        // Extract domain part if full email given
        if (str_contains($input, '@')) {
            $domain = substr(strrchr($input, '@'), 1);
        } else {
            $domain = $input;
        }

        $domain = ltrim($domain, '@');
        if (str_starts_with($domain, 'www.')) {
            $domain = substr($domain, 4);
        }
        $domain = explode('/', $domain)[0];

        if (empty($domain)) {
            return false;
        }

        $excludedList = static::getActiveDomainList();

        foreach ($excludedList as $excluded) {
            $excluded = strtolower(trim($excluded));
            if (empty($excluded)) {
                continue;
            }

            // Exact match (e.g. mesk.com == mesk.com)
            if ($domain === $excluded) {
                return true;
            }

            // Subdomain match (e.g. tracking.mesk.com matches mesk.com)
            if (str_ends_with($domain, '.' . $excluded)) {
                return true;
            }
        }

        return false;
    }
}
