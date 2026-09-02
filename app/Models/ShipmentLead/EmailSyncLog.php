<?php

namespace App\Models\ShipmentLead;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailSyncLog extends Model
{
    use HasFactory;

    protected $table = 'shipment_email_sync_logs';

    protected $fillable = [
        'email_account_id',
        'sync_started_at',
        'sync_finished_at',
        'emails_checked',
        'emails_imported',
        'leads_created',
        'replies_detected',
        'skipped_duplicates',
        'status',
        'error_message',
    ];

    protected $casts = [
        'sync_started_at' => 'datetime',
        'sync_finished_at' => 'datetime',
    ];

    public function account()
    {
        return $this->belongsTo(EmailAccount::class, 'email_account_id');
    }
}
