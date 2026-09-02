<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Email Accounts Table
        Schema::create('shipment_email_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('imap_host');
            $table->integer('imap_port')->default(993);
            $table->string('imap_encryption')->nullable()->default('ssl'); // ssl, tls, null
            $table->string('imap_username');
            $table->text('imap_password'); // encrypted
            $table->string('smtp_host')->nullable();
            $table->integer('smtp_port')->nullable()->default(587);
            $table->string('smtp_encryption')->nullable()->default('tls');
            $table->string('inbox_folder')->default('INBOX');
            $table->string('sent_folder')->default('Sent');
            $table->string('status')->default('active'); // active, inactive
            $table->timestamp('last_sync_at')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamps();
        });

        // 2. Shipment Emails Table
        Schema::create('shipment_emails', function (Blueprint $table) {
            $table->id();
            $table->foreignId('email_account_id')->constrained('shipment_email_accounts')->cascadeOnDelete();
            $table->string('message_id', 255)->nullable()->index();
            $table->unsignedBigInteger('imap_uid')->nullable()->index();
            $table->string('thread_id', 255)->nullable()->index();
            $table->string('direction', 20)->default('incoming')->index(); // incoming, outgoing
            $table->string('from_name')->nullable();
            $table->string('from_email')->index();
            $table->string('to_email')->nullable();
            $table->text('cc')->nullable();
            $table->text('bcc')->nullable();
            $table->text('subject')->nullable();
            $table->longText('body_html')->nullable();
            $table->longText('body_text')->nullable();
            $table->string('in_reply_to', 255)->nullable()->index();
            $table->text('references')->nullable();
            $table->timestamp('received_at')->nullable()->index();
            $table->timestamp('sent_at')->nullable();
            $table->boolean('has_attachments')->default(false);
            $table->timestamps();

            $table->unique(['email_account_id', 'message_id'], 'unique_acc_msg_id');
        });

        // 3. Shipment Leads Table
        Schema::create('shipment_leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('email_id')->nullable()->constrained('shipment_emails')->cascadeOnDelete();
            $table->foreignId('email_account_id')->constrained('shipment_email_accounts')->cascadeOnDelete();
            $table->string('customer_name')->nullable();
            $table->string('customer_email')->index();
            $table->string('customer_phone')->nullable();
            $table->string('company_name')->nullable();
            $table->string('email_subject')->nullable();
            $table->longText('original_content')->nullable();
            $table->timestamp('received_date')->nullable()->index();
            $table->string('shipment_type', 50)->default('unknown')->index(); // sea_fcl, sea_lcl, air_freight, road_freight, reefer, express, unknown
            $table->string('origin')->nullable();
            $table->string('destination')->nullable();
            $table->string('pol')->nullable();
            $table->string('pod')->nullable();
            $table->text('pickup_address')->nullable();
            $table->text('delivery_address')->nullable();
            $table->string('commodity')->nullable();
            $table->string('weight')->nullable();
            $table->string('dimensions')->nullable();
            $table->string('quantity')->nullable();
            $table->string('pallets')->nullable();
            $table->string('container_type')->nullable();
            $table->string('shipment_date')->nullable();
            $table->string('incoterms', 50)->nullable(); // EXW, DDP, FOB, etc.
            $table->string('lead_status', 50)->default('new')->index(); // new, not_replied, replied, follow_up, quotation_sent, negotiation, booked, won, lost, spam, closed
            $table->string('reply_status', 50)->default('not_replied')->index(); // not_replied, replied
            $table->timestamp('replied_at')->nullable();
            $table->foreignId('replied_by_email_account_id')->nullable()->constrained('shipment_email_accounts')->nullOnDelete();
            $table->string('reply_message_id')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 4. Attachments Table
        Schema::create('shipment_email_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('email_id')->constrained('shipment_emails')->cascadeOnDelete();
            $table->string('filename');
            $table->string('file_path')->nullable();
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->default(0);
            $table->timestamps();
        });

        // 5. Lead Notes Table
        Schema::create('shipment_lead_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained('shipment_leads')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('note');
            $table->timestamps();
        });

        // 6. Email Sync Logs Table
        Schema::create('shipment_email_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('email_account_id')->constrained('shipment_email_accounts')->cascadeOnDelete();
            $table->timestamp('sync_started_at')->nullable();
            $table->timestamp('sync_finished_at')->nullable();
            $table->integer('emails_checked')->default(0);
            $table->integer('emails_imported')->default(0);
            $table->integer('leads_created')->default(0);
            $table->integer('replies_detected')->default(0);
            $table->integer('skipped_duplicates')->default(0);
            $table->string('status', 20)->default('success'); // success, partial, failed
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipment_email_sync_logs');
        Schema::dropIfExists('shipment_lead_notes');
        Schema::dropIfExists('shipment_email_attachments');
        Schema::dropIfExists('shipment_leads');
        Schema::dropIfExists('shipment_emails');
        Schema::dropIfExists('shipment_email_accounts');
    }
};
