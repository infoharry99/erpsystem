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
        if (Schema::hasTable('shipment_emails') && !Schema::hasColumn('shipment_emails', 'is_read')) {
            Schema::table('shipment_emails', function (Blueprint $table) {
                $table->boolean('is_read')->default(false)->index()->after('has_attachments');
            });
        }

        if (Schema::hasTable('shipment_leads') && !Schema::hasColumn('shipment_leads', 'is_read')) {
            Schema::table('shipment_leads', function (Blueprint $table) {
                $table->boolean('is_read')->default(false)->index()->after('reply_status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('shipment_emails') && Schema::hasColumn('shipment_emails', 'is_read')) {
            Schema::table('shipment_emails', function (Blueprint $table) {
                $table->dropColumn('is_read');
            });
        }

        if (Schema::hasTable('shipment_leads') && Schema::hasColumn('shipment_leads', 'is_read')) {
            Schema::table('shipment_leads', function (Blueprint $table) {
                $table->dropColumn('is_read');
            });
        }
    }
};
