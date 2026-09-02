<?php

namespace App\Models\ShipmentLead;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class EmailAccount extends Model
{
    use HasFactory;

    protected $table = 'shipment_email_accounts';

    protected $fillable = [
        'name',
        'email',
        'imap_host',
        'imap_port',
        'imap_encryption',
        'imap_username',
        'imap_password',
        'smtp_host',
        'smtp_port',
        'smtp_encryption',
        'inbox_folder',
        'sent_folder',
        'status',
        'last_sync_at',
        'last_error',
    ];

    protected $casts = [
        'last_sync_at' => 'datetime',
        'imap_port' => 'integer',
        'smtp_port' => 'integer',
    ];

    protected $hidden = [
        'imap_password',
    ];

    public function setImapPasswordAttribute($value)
    {
        if (!empty($value)) {
            $this->attributes['imap_password'] = Crypt::encryptString($value);
        }
    }

    public function getDecryptedPasswordAttribute(): string
    {
        if (empty($this->imap_password)) {
            return '';
        }
        try {
            return Crypt::decryptString($this->imap_password);
        } catch (\Exception $e) {
            return $this->imap_password;
        }
    }

    public function emails()
    {
        return $this->hasMany(Email::class, 'email_account_id');
    }

    public function leads()
    {
        return $this->hasMany(Lead::class, 'email_account_id');
    }

    public function syncLogs()
    {
        return $this->hasMany(EmailSyncLog::class, 'email_account_id');
    }
}
