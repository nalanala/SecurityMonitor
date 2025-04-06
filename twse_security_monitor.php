<?php
/**
 * TWSE Security Announcement Monitor
 * 
 * This script checks the TWSE RSS feed for security announcements
 * and sends email notifications when relevant announcements are found.
 */

// Configuration
$rssUrl = 'https://mopsov.twse.com.tw/nas/rss/mopsrss201001.xml';
$emailRecipient = 'seiyalee@gmail.com';
$logDir = __DIR__ . '/log';
$logFile = $logDir . '/monitor_' . date('Y-m-d') . '.log';

// Ensure log directory exists
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}

// Initialize log
function writeLog($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents(
        $logFile, 
        "[$timestamp] $message" . PHP_EOL, 
        FILE_APPEND
    );
}

writeLog("Script started");

// Keywords to look for in announcements
$securityKeywords = [
    '資安重大訊息',
    '網路攻擊',
    '資安事件',
    '資訊安全',
    '駭客',
    '資料外洩',
    '資安漏洞'
];

/**
 * Fetch and parse the RSS feed
 * 
 * @param string $url RSS feed URL
 * @return SimpleXMLElement|false SimpleXML object or false on failure
 */
function fetchRssFeed($url) {
    try {
        $context = stream_context_create([
            'http' => [
                'timeout' => 30,
                'user_agent' => 'Mozilla/5.0 TWSE Security Monitor'
            ]
        ]);
        
        $xmlContent = file_get_contents($url, false, $context);
        if ($xmlContent === false) {
            writeLog("Failed to fetch RSS feed: Unable to connect to the server");
            return false;
        }
        
        $xmlObj = simplexml_load_string($xmlContent);
        if ($xmlObj === false) {
            writeLog("Failed to parse XML content");
            return false;
        }
        
        return $xmlObj;
    } catch (Exception $e) {
        writeLog("Exception occurred while fetching RSS feed: " . $e->getMessage());
        return false;
    }
}

/**
 * Check if the item contains security-related keywords
 * 
 * @param SimpleXMLElement $item RSS item to check
 * @param array $keywords Keywords to look for
 * @return bool True if the item contains any of the keywords
 */
function isSecurityAnnouncement($item, $keywords) {
    $title = (string)$item->title;
    $description = (string)$item->description;
    $content = $title . ' ' . $description;
    
    foreach ($keywords as $keyword) {
        if (mb_stripos($content, $keyword, 0, 'UTF-8') !== false) {
            return true;
        }
    }
    
    return false;
}

/**
 * Send email notification about security announcements
 * 
 * @param array $announcements Array of security announcements
 * @param string $recipient Email recipient
 * @return bool True if email was sent successfully
 */
function sendEmailNotification($announcements, $recipient) {
    $subject = '【TWSE】資安重大訊息通知';
    
    $body = "台灣證券交易所有以下資安相關公告：\n\n";
    foreach ($announcements as $announcement) {
        $body .= "標題: " . $announcement['title'] . "\n";
        $body .= "日期: " . $announcement['date'] . "\n";
        $body .= "內容: " . $announcement['description'] . "\n";
        $body .= "連結: " . $announcement['link'] . "\n\n";
        $body .= "------------------------------------------------------\n\n";
    }
    
    $body .= "此郵件為系統自動發送，請勿直接回覆。";
    
    $headers = "From: TWSE Security Monitor <noreply@twsemonitor.local>\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    
    return mail($recipient, $subject, $body, $headers);
}

// Main execution
try {
    writeLog("Fetching RSS feed from: $rssUrl");
    $rss = fetchRssFeed($rssUrl);
    
    if ($rss === false) {
        writeLog("Failed to fetch or parse RSS feed. Exiting.");
        exit(1);
    }
    
    writeLog("RSS feed fetched successfully");
    
    $securityAnnouncements = [];
    $itemsChecked = 0;
    
    // Process RSS items
    if (isset($rss->channel->item)) {
        foreach ($rss->channel->item as $item) {
            $itemsChecked++;
            
            if (isSecurityAnnouncement($item, $securityKeywords)) {
                $pubDate = isset($item->pubDate) ? date('Y-m-d H:i:s', strtotime((string)$item->pubDate)) : date('Y-m-d H:i:s');
                
                $securityAnnouncements[] = [
                    'title' => (string)$item->title,
                    'description' => (string)$item->description,
                    'link' => (string)$item->link,
                    'date' => $pubDate
                ];
                
                writeLog("Security announcement found: " . (string)$item->title);
            }
        }
    }
    
    writeLog("Checked $itemsChecked items in the RSS feed");
    
    // Send email if security announcements were found
    if (!empty($securityAnnouncements)) {
        writeLog("Found " . count($securityAnnouncements) . " security announcements. Sending email notification.");
        
        $emailSent = sendEmailNotification($securityAnnouncements, $emailRecipient);
        
        if ($emailSent) {
            writeLog("Email notification sent successfully to $emailRecipient");
        } else {
            writeLog("Failed to send email notification");
        }
    } else {
        writeLog("No security announcements found");
    }
    
    writeLog("Script completed successfully");
} catch (Exception $e) {
    writeLog("Unexpected error occurred: " . $e->getMessage());
    exit(1);
}
?>
