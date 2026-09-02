<?php

namespace Tests\Feature;

use App\Models\ShipmentLead\Email;
use App\Models\ShipmentLead\EmailAccount;
use App\Models\ShipmentLead\Lead;
use App\Models\User;
use App\Services\Email\ReplyDetectionService;
use App\Services\Lead\LeadService;
use App\Services\Lead\ShipmentExtractionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShipmentLeadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_shipment_extraction_service_parses_real_emails(): void
    {
        $extractor = new ShipmentExtractionService();

        // Sample 1: Darlington UK - Nhava Sheva
        $email1 = "Subject: Ocean Freight from Darlington UK- Nhava Sheva\n" .
            "Commodity: Nutritional Supplements\n" .
            "No. of Cases : 1540 Pcs.\n" .
            "Gross weight : 1.99ton.\n" .
            "Cargo Voluminous : 8 Standard UK pallets, Height – 1300 to 1500.\n" .
            "Equipment Required : 1 x 20' GP (Non-HAZ)\n" .
            "Place of pick up : George Allinson Transport Ltd, Faverdale Industrial Estate, Darlington DL3 0PH\n" .
            "Delivery Term: Ex-W\n" .
            "Port of Delivery : (Nhava Sheva)";

        $res1 = $extractor->extract("Ocean Freight from Darlington UK- Nhava Sheva", $email1);
        $this->assertEquals('EXW', $res1['incoterms']);
        $this->assertEquals('Nutritional Supplements', $res1['commodity']);
        $this->assertEquals('1.99ton.', $res1['weight']);
        $this->assertEquals('1 x 20\' GP (Non-HAZ)', $res1['container_type']);
        $this->assertStringContainsString('Darlington', $res1['pickup_address']);
        $this->assertEquals('(Nhava Sheva)', $res1['pod']);

        // Sample 2: Germany-Sri Lanka Reefer
        $email2 = "Subject: Globetrotters // QR26774: Quotation request , Germany-Sri Lanka/Nisrine\n" .
            "Pickup address: Marbacher Straße 12, 71364 Winnenden, Germany\n" .
            "POD: Colombo - Sri Lanka\n" .
            "Commodity: Apple Juice Concentrates\n" .
            "Volume: 1x20 Reefer (14.3MT)\n" .
            "Cargo Temperature: Deep Frozen (-180C)\n" .
            "Pallet Configuration: Totally 14 pallets with 54 drums = € 14.310 kg net, 15.228 kg gross approximately.";

        $res2 = $extractor->extract("Quotation request , Germany-Sri Lanka", $email2);
        $this->assertEquals('reefer', $res2['shipment_type']);
        $this->assertEquals('Apple Juice Concentrates', $res2['commodity']);
        $this->assertEquals('Colombo - Sri Lanka', $res2['pod']);
        $this->assertEquals('1x20 Reefer (14.3MT)', $res2['container_type']);

        // Sample 3: EXW Denmark to Hyderabad
        $email3 = "Subject: AI/26-27/GML-DEL/02748 EXW DENMARK TO HYDERBAD // 1680 KG\n" .
            "Please quote freight charges from Denmark Ex-Works to Hyderabad airport .\n" .
            "12 pallets. All are size: 120lx80wx87h cm\n" .
            "Approx weight 140 kg each pallet.\n" .
            "Total Material weight: 1680kg*\n" .
            "Shipper Address: ScanBelt A/S - Læsøvej 12 - DK-9800 Hjørring - Denmark";

        $res3 = $extractor->extract("EXW DENMARK TO HYDERBAD", $email3);
        $this->assertEquals('EXW', $res3['incoterms']);
        $this->assertEquals('air_freight', $res3['shipment_type']);
        $this->assertEquals('1680kg*', $res3['weight']);
        $this->assertEquals('120lx80wx87h cm', $res3['dimensions']);
        $this->assertEquals('ScanBelt A/S', $res3['company_name']);
    }

    public function test_lead_service_creates_lead_from_email(): void
    {
        $account = EmailAccount::create([
            'name' => 'Sales Team',
            'email' => 'sales@company.com',
            'imap_host' => 'imap.company.com',
            'imap_port' => 993,
            'imap_username' => 'sales@company.com',
            'imap_password' => 'secret123',
            'inbox_folder' => 'INBOX',
            'sent_folder' => 'Sent',
            'status' => 'active',
        ]);

        $email = Email::create([
            'email_account_id' => $account->id,
            'message_id' => '<test-msg-001@customer.com>',
            'direction' => 'incoming',
            'from_name' => 'John Cargo',
            'from_email' => 'john@cargo.com',
            'to_email' => 'sales@company.com',
            'subject' => 'Quote Request EXW UK to Nhava Sheva 1x20GP',
            'body_text' => 'Commodity: Machinery\nEquipment: 1x20 GP\nGross weight: 2.5 MT\nPickup: London UK',
            'received_at' => now(),
        ]);

        $leadService = app(LeadService::class);
        $lead = $leadService->createLeadFromEmail($email);

        $this->assertDatabaseHas('shipment_leads', [
            'id' => $lead->id,
            'customer_email' => 'john@cargo.com',
            'shipment_type' => 'sea_fcl',
            'reply_status' => 'not_replied',
            'lead_status' => 'new',
        ]);
    }

    public function test_reply_detection_service_detects_outgoing_reply(): void
    {
        $account = EmailAccount::create([
            'name' => 'Sales Desk',
            'email' => 'sales@company.com',
            'imap_host' => 'imap.company.com',
            'imap_port' => 993,
            'imap_username' => 'sales@company.com',
            'imap_password' => 'secret123',
            'inbox_folder' => 'INBOX',
            'sent_folder' => 'Sent',
            'status' => 'active',
        ]);

        $incomingEmail = Email::create([
            'email_account_id' => $account->id,
            'message_id' => '<inquiry-999@customer.com>',
            'direction' => 'incoming',
            'from_name' => 'Alice Customer',
            'from_email' => 'alice@customer.com',
            'subject' => 'Rate inquiry Germany to Jeddah',
            'body_text' => 'Please quote 1x20 GP Aluminum Profiles',
            'received_at' => now()->subHours(2),
        ]);

        $lead = app(LeadService::class)->createLeadFromEmail($incomingEmail);
        $this->assertEquals('not_replied', $lead->reply_status);

        $outgoingEmail = Email::create([
            'email_account_id' => $account->id,
            'message_id' => '<company-reply-100@company.com>',
            'direction' => 'outgoing',
            'from_name' => 'Sales Desk',
            'from_email' => 'sales@company.com',
            'to_email' => 'alice@customer.com',
            'subject' => 'Re: Rate inquiry Germany to Jeddah',
            'in_reply_to' => '<inquiry-999@customer.com>',
            'body_text' => 'Dear Alice, please find our proposal attached.',
            'sent_at' => now(),
        ]);

        $replyDetector = app(ReplyDetectionService::class);
        $detected = $replyDetector->processOutgoingReply($outgoingEmail);

        $this->assertTrue($detected);

        $lead->refresh();
        $this->assertEquals('replied', $lead->reply_status);
        $this->assertEquals('replied', $lead->lead_status);
        $this->assertEquals('<company-reply-100@company.com>', $lead->reply_message_id);
    }

    public function test_shipment_lead_dashboard_and_index_routes(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('shipment-leads.dashboard'));
        $response->assertStatus(200);

        $responseLeads = $this->get(route('shipment-leads.leads.index'));
        $responseLeads->assertStatus(200);

        $responseAccounts = $this->get(route('shipment-leads.accounts.index'));
        $responseAccounts->assertStatus(200);
    }
}
