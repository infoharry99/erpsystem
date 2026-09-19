<?php

namespace App\Services\Email;

use App\Models\ShipmentLead\EmailAccount;
use Webklex\PHPIMAP\Client;
use Webklex\PHPIMAP\ClientManager;
use Illuminate\Support\Facades\Log;

class ImapConnectionService
{
    public function getClient(EmailAccount $account): Client
    {
        $cm = new ClientManager([
            'options' => [
                'debug' => false,
                'version' => '1.0.0',
                'fetch' => \Webklex\PHPIMAP\IMAP::FT_PEEK,
                'sequence' => \Webklex\PHPIMAP\IMAP::ST_UID,
                'fetch_body' => false,
                'fetch_flags' => true,
                'soft_fail' => true,
                'rfc822' => true,
            ],
            'accounts' => [
                'default' => [
                    'host'          => $account->imap_host,
                    'port'          => $account->imap_port,
                    'encryption'    => $account->imap_encryption === 'none' ? false : ($account->imap_encryption ?: 'ssl'),
                    'validate_cert' => false,
                    'username'      => $account->imap_username,
                    'password'      => $account->decrypted_password,
                    'protocol'      => 'imap',
                    'options'       => [
                        'fetch' => \Webklex\PHPIMAP\IMAP::FT_PEEK,
                        'sequence' => \Webklex\PHPIMAP\IMAP::ST_UID,
                        'fetch_body' => false,
                    ],
                ]
            ]
        ]);

        return $cm->account('default');
    }

    public function testConnection(EmailAccount $account): array
    {
        try {
            if (class_exists(ClientManager::class)) {
                $client = $this->getClient($account);
                $client->connect();

                $foldersList = [];
                $folders = $client->getFolders();
                foreach ($folders as $folder) {
                    $foldersList[] = $folder->name;
                }

                $client->disconnect();

                return [
                    'success' => true,
                    'message' => 'IMAP Connection Successful! Found ' . count($foldersList) . ' folders.',
                    'folders' => $foldersList,
                ];
            }

            if (function_exists('imap_open')) {
                $flags = '/' . ($account->imap_encryption ?: 'ssl') . '/novalidate-cert';
                $mailboxStr = '{' . $account->imap_host . ':' . $account->imap_port . '/imap' . $flags . '}INBOX';
                $readonlyFlag = defined('OP_READONLY') ? OP_READONLY : 0;
                $imap = @imap_open($mailboxStr, $account->imap_username, $account->decrypted_password, $readonlyFlag);

                if ($imap) {
                    @imap_close($imap);
                    return [
                        'success' => true,
                        'message' => 'IMAP Connection Successful via native PHP IMAP!',
                        'folders' => ['INBOX', 'Sent'],
                    ];
                }

                return [
                    'success' => false,
                    'message' => 'IMAP Connection Failed: ' . imap_last_error(),
                    'folders' => [],
                ];
            }

            return [
                'success' => false,
                'message' => 'IMAP client library is not available.',
                'folders' => [],
            ];
        } catch (\Exception $e) {
            Log::error("IMAP Connection test error for {$account->email}: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Connection Exception: ' . $e->getMessage(),
                'folders' => [],
            ];
        }
    }
}
