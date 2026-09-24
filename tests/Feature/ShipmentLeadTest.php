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

    public function test_non_shipment_spam_emails_are_skipped(): void
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

        $spamEmail = Email::create([
            'email_account_id' => $account->id,
            'message_id' => '<spam-001@nasar.com>',
            'direction' => 'incoming',
            'from_name' => 'Nasar',
            'from_email' => 'nasar@thenexteck.com',
            'to_email' => 'sales@company.com',
            'subject' => 'Faltu email',
            'body_text' => 'Faltu email Faltu email Faltu email',
            'received_at' => now(),
        ]);

        $leadService = app(LeadService::class);
        $lead = $leadService->createLeadFromEmail($spamEmail);

        $this->assertNull($lead);
        $this->assertDatabaseMissing('shipment_leads', [
            'email_id' => $spamEmail->id,
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

    public function test_public_home_page_shows_inquiry_stats_without_login_and_protects_details(): void
    {
        $account = EmailAccount::create([
            'name' => 'Sales Desk',
            'email' => 'sales@company.com',
            'imap_host' => 'imap.company.com',
            'imap_port' => 993,
            'imap_username' => 'sales@company.com',
            'imap_password' => 'secret123',
            'status' => 'active',
        ]);

        $email = Email::create([
            'email_account_id' => $account->id,
            'message_id' => '<inquiry-pub-99@customer.com>',
            'direction' => 'incoming',
            'from_name' => 'John Doe Secret',
            'from_email' => 'private-john@customer.com',
            'to_email' => 'sales@company.com',
            'subject' => 'Confidential Quotation for 40ft Container',
            'body_text' => 'Secret shipment details text',
            'received_at' => now(),
        ]);

        $lead = Lead::create([
            'email_id' => $email->id,
            'email_account_id' => $account->id,
            'customer_name' => 'John Doe Secret',
            'customer_email' => 'private-john@customer.com',
            'subject' => 'Confidential Quotation for 40ft Container',
            'shipment_type' => 'sea_fcl',
            'reply_status' => 'not_replied',
            'lead_status' => 'new',
            'received_date' => now(),
        ]);

        // 1. Unauthenticated guest visits public home page
        $response = $this->get(route('home'));
        $response->assertStatus(200);
        $response->assertSee('Total Inquiries');
        $response->assertSee('Waiting For Reply');
        $response->assertSee('Replied Inquiries');
        $response->assertSee('Sign In to View Details');

        // Verify counts are shown but confidential customer body and email are NOT exposed on public home
        $response->assertDontSee('private-john@customer.com');
        $response->assertDontSee('Secret shipment details text');

        // 2. Unauthenticated guest attempts to view lead details
        $detailResponse = $this->get(route('shipment-leads.leads.show', $lead->id));
        $detailResponse->assertRedirect(route('login'));

        // 3. Authenticated user can view details
        $this->actingAs($this->user);
        $authDetailResponse = $this->get(route('shipment-leads.leads.show', $lead->id));
        $authDetailResponse->assertStatus(200);
        $authDetailResponse->assertSee('John Doe Secret');
    }

    public function test_duplicate_email_subject_only_creates_one_lead(): void
    {
        $account = EmailAccount::create([
            'name' => 'Support Desk',
            'email' => 'support@company.com',
            'imap_host' => 'imap.company.com',
            'imap_port' => 993,
            'imap_username' => 'support@company.com',
            'imap_password' => 'secret123',
            'status' => 'active',
        ]);

        $leadService = app(LeadService::class);

        // First email with subject
        $email1 = Email::create([
            'email_account_id' => $account->id,
            'message_id' => '<inquiry-001@customer.com>',
            'direction' => 'incoming',
            'from_name' => 'David Logistic',
            'from_email' => 'david@logistic.com',
            'to_email' => 'support@company.com',
            'subject' => 'FW: Urgent Freight Quotation from London to Dubai',
            'body_text' => 'Please quote air freight charges for 5 pallets 1200kg from London to Dubai airport.',
            'received_at' => now()->subHours(2),
        ]);

        $lead1 = $leadService->createLeadFromEmail($email1);
        $this->assertNotNull($lead1);
        $this->assertEquals(1, Lead::where('email_subject', 'like', '%Urgent Freight Quotation%')->count());

        // Second email with same subject (with Re: prefix)
        $email2 = Email::create([
            'email_account_id' => $account->id,
            'message_id' => '<inquiry-002@customer.com>',
            'direction' => 'incoming',
            'from_name' => 'David Logistic',
            'from_email' => 'david@logistic.com',
            'to_email' => 'support@company.com',
            'subject' => 'Re: FW: Urgent Freight Quotation from London to Dubai',
            'body_text' => 'Following up on this quotation request.',
            'received_at' => now(),
        ]);

        $lead2 = $leadService->createLeadFromEmail($email2);
        $this->assertNotNull($lead2);
        $this->assertEquals($lead1->id, $lead2->id);

        // Ensure total leads for this subject is still ONLY 1
        $this->assertEquals(1, Lead::where('email_subject', 'like', '%Urgent Freight Quotation%')->count());
    }

    public function test_incoming_email_preserves_gmail_read_and_unread_status(): void
    {
        $account = EmailAccount::create([
            'name' => 'Import Team',
            'email' => 'import@company.com',
            'imap_host' => 'imap.company.com',
            'imap_port' => 993,
            'imap_username' => 'import@company.com',
            'imap_password' => 'secret123',
            'inbox_folder' => 'INBOX',
            'status' => 'active',
        ]);

        $leadService = app(LeadService::class);

        // 1. Unread email in Gmail (is_read = false)
        $unreadEmail = Email::create([
            'email_account_id' => $account->id,
            'message_id' => '<unread-001@client.com>',
            'direction' => 'incoming',
            'from_name' => 'Alice Logistics',
            'from_email' => 'alice@client.com',
            'to_email' => 'import@company.com',
            'subject' => 'Inquiry for Sea FCL 2x40HC Shanghai to Rotterdam',
            'body_text' => 'Need ocean rates for 2x40HC containers ready next Monday.',
            'received_at' => now(),
            'is_read' => false,
        ]);

        $unreadLead = $leadService->createLeadFromEmail($unreadEmail);
        $this->assertNotNull($unreadLead);
        $this->assertFalse($unreadLead->is_read);

        // 2. Read email in Gmail (is_read = true)
        $readEmail = Email::create([
            'email_account_id' => $account->id,
            'message_id' => '<read-002@client.com>',
            'direction' => 'incoming',
            'from_name' => 'Bob Cargo',
            'from_email' => 'bob@client.com',
            'to_email' => 'import@company.com',
            'subject' => 'Quote Request Air Freight Tokyo to New York 500kg',
            'body_text' => 'Please provide air freight cost for 500kg from Tokyo to JFK.',
            'received_at' => now(),
            'is_read' => true,
        ]);

        $readLead = $leadService->createLeadFromEmail($readEmail);
        $this->assertNotNull($readLead);
        $this->assertTrue($readLead->is_read);
    }

    public function test_lead_filtering_by_gmail_read_and_unread_status(): void
    {
        $account = EmailAccount::create([
            'name' => 'Operations',
            'email' => 'ops@company.com',
            'imap_host' => 'imap.company.com',
            'imap_port' => 993,
            'imap_username' => 'ops@company.com',
            'imap_password' => 'secret123',
            'inbox_folder' => 'INBOX',
            'status' => 'active',
        ]);

        $leadService = app(LeadService::class);

        $unreadEmail = Email::create([
            'email_account_id' => $account->id,
            'message_id' => '<unread-filter@client.com>',
            'direction' => 'incoming',
            'from_name' => 'Unread Client',
            'from_email' => 'unread@client.com',
            'to_email' => 'ops@company.com',
            'subject' => 'Freight rate request Hamburg to Santos 1x20GP',
            'body_text' => 'Need 1x20GP rate from Hamburg to Santos.',
            'is_read' => false,
        ]);
        $leadService->createLeadFromEmail($unreadEmail);

        $readEmail = Email::create([
            'email_account_id' => $account->id,
            'message_id' => '<read-filter@client.com>',
            'direction' => 'incoming',
            'from_name' => 'Read Client',
            'from_email' => 'read@client.com',
            'to_email' => 'ops@company.com',
            'subject' => 'Freight rate request Antwerp to Singapore 2x40GP',
            'body_text' => 'Need 2x40GP rate from Antwerp to Singapore.',
            'is_read' => true,
        ]);
        $leadService->createLeadFromEmail($readEmail);

        // Filter unread only
        $responseUnread = $this->actingAs($this->user)->get(route('shipment-leads.leads.index', ['is_read' => 'unread']));
        $responseUnread->assertOk();
        $responseUnread->assertSee('Freight rate request Hamburg to Santos');
        $responseUnread->assertDontSee('Freight rate request Antwerp to Singapore');

        // Filter read only
        $responseRead = $this->actingAs($this->user)->get(route('shipment-leads.leads.index', ['is_read' => 'read']));
        $responseRead->assertOk();
        $responseRead->assertSee('Freight rate request Antwerp to Singapore');
        $responseRead->assertDontSee('Freight rate request Hamburg to Santos');
    }

    public function test_imap_connection_service_configures_ft_peek_fetch_option(): void
    {
        $account = new EmailAccount([
            'imap_host' => 'imap.gmail.com',
            'imap_port' => 993,
            'imap_username' => 'test@gmail.com',
            'imap_password' => 'secret',
            'imap_encryption' => 'ssl',
        ]);

        $service = new \App\Services\Email\ImapConnectionService();
        $client = $service->getClient($account);

        $this->assertEquals(\Webklex\PHPIMAP\IMAP::FT_PEEK, $client->getConfig()->get('options.fetch'));
        $this->assertFalse($client->getConfig()->get('options.fetch_body'));
    }

    public function test_internal_emails_and_billing_are_excluded_from_leads(): void
    {
        $account = EmailAccount::create([
            'name' => 'Support',
            'email' => 'sales@globetrottersltd.com',
            'imap_host' => 'imap.company.com',
            'imap_port' => 993,
            'imap_username' => 'sales@globetrottersltd.com',
            'imap_password' => 'secret123',
            'inbox_folder' => 'INBOX',
            'status' => 'active',
        ]);

        $leadService = app(LeadService::class);

        // Internal payment reminder email
        $internalEmail = Email::create([
            'email_account_id' => $account->id,
            'message_id' => '<pay-reminder-01@globetrottersltd.com>',
            'direction' => 'incoming',
            'from_name' => 'Accounts Globetrotters',
            'from_email' => 'accounts@globetrottersltd.com',
            'to_email' => 'sales@globetrottersltd.com',
            'subject' => 'RE: Payment due reminder',
            'body_text' => 'Dear Rana, Please check the attached invoice and make sure statement is cleared.',
            'is_read' => false,
        ]);

        $lead = $leadService->createLeadFromEmail($internalEmail);
        $this->assertNull($lead, 'Internal accounts payment reminder email should never be created as a lead.');
    }

    public function test_membership_and_monthly_reports_are_excluded_from_leads(): void
    {
        $account = EmailAccount::create([
            'name' => 'Main',
            'email' => 'info@globetrottersltd.com',
            'imap_host' => 'imap.company.com',
            'imap_port' => 993,
            'imap_username' => 'info@globetrottersltd.com',
            'imap_password' => 'secret123',
            'inbox_folder' => 'INBOX',
            'status' => 'active',
        ]);

        $leadService = app(LeadService::class);

        // GLA Membership Renewal
        $membershipEmail = Email::create([
            'email_account_id' => $account->id,
            'message_id' => '<gla-renewal-01@glafamily.com>',
            'direction' => 'incoming',
            'from_name' => 'GLA Family',
            'from_email' => 'member682@glafamily.com',
            'to_email' => 'info@globetrottersltd.com',
            'subject' => 'Re:Membership Renewal: GLA Account Documents Update and submission - ID 8066',
            'body_text' => 'Please upload agreement to Sign Plus and complete e-stamp.',
            'is_read' => false,
        ]);

        $lead1 = $leadService->createLeadFromEmail($membershipEmail);
        $this->assertNull($lead1, 'Membership renewal email should never be created as a lead.');

        // JCtrans Monthly Report
        $reportEmail = Email::create([
            'email_account_id' => $account->id,
            'message_id' => '<jctrans-report-01@report.ejctrans.com>',
            'direction' => 'incoming',
            'from_name' => 'JCtrans',
            'from_email' => 'customer@report.ejctrans.com',
            'to_email' => 'info@globetrottersltd.com',
            'subject' => 'JCtrans Monthly Report',
            'body_text' => 'Dear member, here is your monthly report from Shanghai port network to explore new op.',
            'is_read' => false,
        ]);

        $lead2 = $leadService->createLeadFromEmail($reportEmail);
        $this->assertNull($lead2, 'Monthly report newsletter should never be created as a lead.');
    }

    public function test_prune_non_leads_removes_mistaken_leads(): void
    {
        $account = EmailAccount::create([
            'name' => 'Main',
            'email' => 'info@globetrottersltd.com',
            'imap_host' => 'imap.company.com',
            'imap_port' => 993,
            'imap_username' => 'info@globetrottersltd.com',
            'imap_password' => 'secret123',
            'inbox_folder' => 'INBOX',
            'status' => 'active',
        ]);

        $badLead = Lead::create([
            'email_account_id' => $account->id,
            'customer_name' => 'Accounts',
            'customer_email' => 'accounts@globetrottersltd.com',
            'email_subject' => 'RE: Payment due reminder',
            'original_content' => 'Please clear the balance payable.',
            'shipment_type' => 'air_freight',
            'lead_status' => 'new',
            'reply_status' => 'pending',
        ]);

        $goodLead = Lead::create([
            'email_account_id' => $account->id,
            'customer_name' => 'Freight Customer',
            'customer_email' => 'customer@shipper.com',
            'email_subject' => 'RFQ: Ocean Freight 2x40HC Shanghai to Felixstowe',
            'original_content' => 'Please quote for 2x40HC from Shanghai to Felixstowe.',
            'shipment_type' => 'sea_fcl',
            'lead_status' => 'new',
            'reply_status' => 'pending',
        ]);

        $leadService = app(LeadService::class);
        $pruned = $leadService->pruneNonLeads();

        $this->assertGreaterThanOrEqual(1, $pruned);
        $this->assertDatabaseMissing('shipment_leads', ['id' => $badLead->id]);
        $this->assertDatabaseHas('shipment_leads', ['id' => $goodLead->id]);
    }
}

