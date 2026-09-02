<?php

namespace App\Models\ShipmentLead;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailAttachment extends Model
{
    use HasFactory;

    protected $table = 'shipment_email_attachments';

    protected $fillable = [
        'email_id',
        'filename',
        'file_path',
        'mime_type',
        'file_size',
    ];

    public function email()
    {
        return $this->belongsTo(Email::class, 'email_id');
    }
}
