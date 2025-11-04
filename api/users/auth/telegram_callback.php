<?php
header('Content-Type: application/json');

use Config\Utility_Functions;

require_once '../../../config/bootstrap_file.php';

// Replace this with your real bot token or use env
$botToken = $_ENV['TELEGRAM_KYC_BOT_TOKEN'] ?? '8123455910:AAFpus_vOT6zRYfYhHV1oZ1QW9nCO2dxQkI';

// Read Telegram update
$update = json_decode(file_get_contents('php://input'), true);

// === 1. HANDLE NORMAL MESSAGES (e.g. /updates) ===
if (isset($update['message'])) {
    $chatId = $update['message']['chat']['id'] ?? null;
    $text = $update['message']['text'] ?? null;

    if ($chatId && $text === '/updates') {
        $message = "✅ Bot is working. No new updates.";

        // Respond to user
        file_get_contents("https://api.telegram.org/bot$botToken/sendMessage?" . http_build_query([
            'chat_id' => $chatId,
            'text' => $message
        ]));

        echo json_encode(['status' => 'message_handled']);
        exit;
    }
}

// === 2. HANDLE CALLBACK QUERIES (e.g. Approve/Reject KYC) ===
if (isset($update['callback_query'])) {
    $callback_query = $update['callback_query'];
    $callback_data = $callback_query['data'] ?? '';
    $callback_id = $callback_query['id'] ?? '';
    $chat_id = $callback_query['message']['chat']['id'] ?? null;
    $message_id = $callback_query['message']['message_id'] ?? null;

    // Extract action and username from callback_data like: approve|john_doe
    if (strpos($callback_data, '|') !== false) {
        list($action, $username) = explode('|', $callback_data);
    } else {
        file_get_contents("https://api.telegram.org/bot$botToken/answerCallbackQuery?" . http_build_query([
            'callback_query_id' => $callback_id,
            'text' => "❌ Invalid callback format.",
            'show_alert' => true
        ]));
        exit;
    }

    // Handle approve/reject
    $response = Utility_Functions::handleKYCApprovalRejection($action, $username);

    // Answer callback to stop spinner
    file_get_contents("https://api.telegram.org/bot$botToken/answerCallbackQuery?" . http_build_query([
        'callback_query_id' => $callback_id,
        'text' => $response['message'],
        'show_alert' => true
    ]));

    // Remove inline buttons
    if ($chat_id && $message_id) {
        file_get_contents("https://api.telegram.org/bot$botToken/editMessageReplyMarkup?" . http_build_query([
            'chat_id' => $chat_id,
            'message_id' => $message_id,
            'reply_markup' => json_encode(['inline_keyboard' => []])
        ]));
    }

    echo json_encode(['status' => 'callback_handled']);
    exit;
}

echo json_encode(['status' => 'ignored', 'message' => 'Update not handled.']);
exit;
