# TWSE 資安重大訊息監控系統

這個專案是一個自動化工具，用於監控台灣證券交易所(TWSE)的資安重大訊息RSS訊息來源，並在發現相關公告時發送電子郵件通知。

## 功能特色

- 每12小時定期檢查台灣證券交易所的RSS訊息來源
- 自動識別與資安相關的公告
- 發現重要資安公告時自動發送電子郵件通知
- 詳細的活動記錄系統

## 系統需求

- PHP 7.0 或更高版本
- 有效的郵件發送設定（系統郵件或SMTP伺服器）

## 安裝步驟

1. 複製此專案到您的伺服器：
   ```
   git clone https://github.com/your-username/twse-security-monitor.git
   cd twse-security-monitor
   ```

2. 確保`log`目錄可寫入：
   ```
   chmod 755 log
   ```

3. 設置排程任務，每12小時執行一次：
   ```
   crontab -e
   ```
   
   添加以下行：
   ```
   0 6,18 * * * php /path/to/twse_security_monitor.php
   ```
   
   請確保將`/path/to/`替換為實際的路徑。

## 配置

您可以在`twse_security_monitor.php`文件中修改以下設定：

- `$rssUrl`: TWSE RSS訊息來源的URL
- `$emailRecipient`: 接收通知的電子郵件地址
- `$securityKeywords`: 用於識別資安相關公告的關鍵字列表

## 使用方法

腳本設計為透過排程任務自動運行，但您也可以手動執行：

```
php twse_security_monitor.php
```

腳本執行後，將在`log`目錄中創建日誌文件，記錄執行情況。如果發現資安相關公告，會自動發送電子郵件到配置的地址。

## 日誌文件

日誌文件存儲在`log`目錄中，每天創建一個新的日誌文件，命名格式為`monitor_YYYY-MM-DD.log`。

## 授權條款

[請在此添加您的授權條款]

## 作者

[請在此添加您的聯繫信息]
