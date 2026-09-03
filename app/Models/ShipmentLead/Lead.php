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
        'notes',
    ];

    protected $casts = [
        'received_date' => 'datetime',
        'replied_at' => 'datetime',
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
}
