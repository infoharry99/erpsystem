<?php

namespace App\Models\ShipmentLead;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Email extends Model
{
    use HasFactory;

    protected $table = 'shipment_emails';

    protected $fillable = [
        'email_account_id',
        'message_id',
        'imap_uid',
        'thread_id',
        'direction',
        'from_name',
        'from_email',
        'to_email',
        'cc',
        'bcc',
        'subject',
        'body_html',
        'body_text',
        'in_reply_to',
        'references',
        'received_at',
        'sent_at',
        'has_attachments',
    ];

    protected $casts = [
        'received_at' => 'datetime',
        'sent_at' => 'datetime',
        'has_attachments' => 'boolean',
    ];

    public function account()
    {
        return $this->belongsTo(EmailAccount::class, 'email_account_id');
    }

    public function lead()
    {
        return $this->hasOne(Lead::class, 'email_id');
    }

    public function attachments()
    {
        return $this->hasMany(EmailAttachment::class, 'email_id');
    }
}
