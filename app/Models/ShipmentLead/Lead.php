<?php

namespace App\Models\ShipmentLead;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    use HasFactory;

    protected $table = 'shipment_leads';

    protected $fillable = [
        'email_id',
        'email_account_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'company_name',
        'email_subject',
        'ai_summary',
        'original_content',
        'received_date',
        'shipment_type',
        'origin',
        'destination',
        'pol',
        'pod',
        'pickup_address',
        'delivery_address',
        'commodity',
        'weight',
        'dimensions',
        'quantity',
        'pallets',
        'container_type',
        'shipment_date',
        'incoterms',
        'lead_status',
        'reply_status',
        'replied_at',
        'replied_by_email_account_id',
        'reply_message_id',
        'assigned_to',
        'is_read',
        'notes',
    ];

    protected $casts = [
        'received_date' => 'datetime',
        'replied_at' => 'datetime',
        'is_read' => 'boolean',
    ];

    public function email()
    {
        return $this->belongsTo(Email::class, 'email_id');
    }

    public function account()
    {
        return $this->belongsTo(EmailAccount::class, 'email_account_id');
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function leadNotes()
    {
        return $this->hasMany(LeadNote::class, 'lead_id')->latest();
    }

    public function repliedByAccount()
    {
        return $this->belongsTo(EmailAccount::class, 'replied_by_email_account_id');
    }

    public function getShipmentTypeLabelAttribute(): string
    {
        return match ($this->shipment_type) {
            'sea_fcl' => 'Sea FCL',
            'sea_lcl' => 'Sea LCL',
            'air_freight' => 'Air Freight',
            'road_freight' => 'Road Freight',
            'reefer' => 'Reefer Container',
            'express' => 'Express / Courier',
            default => 'General Cargo',
        };
    }

    public function getWaitingDurationAttribute(): string
    {
        if ($this->reply_status === 'replied' || !$this->received_date) {
            return '-';
        }
        return $this->received_date->diffForHumans();
    }

    /**
     * Detect the lead stage based on email subject codes:
     * - Stage 1 (New Lead): Normal subject (default)
     * - Stage 2 (Quotation Sent): Subject contains 'QGLT'
     * - Stage 3 (Final Lead): Subject contains both 'QGLT' and 'GLT'
     *
     * Constraint: 'GLTEXO' must NOT be counted as 'GLT' and must not promote the stage to Final Lead.
     */
    public static function detectSubjectStage(?string $subject): string
    {
        if (empty($subject)) {
            return 'new';
        }

        // 1. Detect QGLT (word boundary / non-alphanumeric preceding)
        $hasQglt = (bool) preg_match('/(?<![A-Za-z0-9])QGLT/i', $subject);

        // 2. Detect GLT as independent code, strictly excluding QGLT (preceding Q) and GLTEXO (following EXO)
        $hasGlt  = (bool) preg_match('/(?<![A-Za-z0-9])GLT(?![\-_]?EXO)/i', $subject);

        if ($hasQglt && $hasGlt) {
            return 'final_lead';
        }

        if ($hasQglt) {
            return 'quotation_sent';
        }

        return 'new';
    }

    public function getStageLabelAttribute(): string
    {
        return match ($this->lead_status) {
            'new' => 'New Lead',
            'quotation_sent' => 'Quotation Sent',
            'final_lead' => 'Final Lead',
            'booked' => 'Booked',
            'won' => 'Won Deal',
            'lost' => 'Lost',
            'replied' => 'Replied',
            'not_replied' => 'Not Replied',
            'follow_up' => 'Follow Up',
            'negotiation' => 'Negotiation',
            'spam' => 'Spam',
            'closed' => 'Closed',
            default => ucwords(str_replace('_', ' ', $this->lead_status ?? 'New Lead')),
        };
    }
}

