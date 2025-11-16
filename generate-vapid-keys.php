<?php
/**
 * Generate VAPID Keys untuk Push Notification
 * 
 * Install dulu: composer require minishlink/web-push
 * Run: php generate-vapid-keys.php
 */

// Check if web-push library is available
if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    echo "❌ Error: Composer dependencies not installed.\n";
    echo "\nInstall dengan perintah:\n";
    echo "composer require minishlink/web-push\n\n";
    exit(1);
}

require_once __DIR__ . '/vendor/autoload.php';

use Minishlink\WebPush\VAPID;

echo "🔐 Generating VAPID Keys...\n\n";

$keys = VAPID::createVapidKeys();

echo "✅ VAPID Keys Generated!\n\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Public Key:\n";
echo $keys['publicKey'] . "\n\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Private Key:\n";
echo $keys['privateKey'] . "\n\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "📝 Copy keys di atas ke:\n";
echo "1. File .env:\n";
echo "   VAPID_PUBLIC_KEY={$keys['publicKey']}\n";
echo "   VAPID_PRIVATE_KEY={$keys['privateKey']}\n\n";
echo "2. File config/config.php:\n";
echo "   define('VAPID_PUBLIC_KEY', '{$keys['publicKey']}');\n";
echo "   define('VAPID_PRIVATE_KEY', '{$keys['privateKey']}');\n\n";

// Save to file
$output = [
    'generated_at' => date('Y-m-d H:i:s'),
    'public_key' => $keys['publicKey'],
    'private_key' => $keys['privateKey']
];

file_put_contents(__DIR__ . '/vapid_keys.json', json_encode($output, JSON_PRETTY_PRINT));

echo "💾 Keys juga disimpan di: vapid_keys.json\n";
echo "⚠️  JANGAN commit vapid_keys.json ke Git!\n\n";
echo "✅ Done!\n";