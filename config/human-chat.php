<?php
declare(strict_types=1);

function ensureHumanChatTables(PDO $db): void {
    $db->exec("CREATE TABLE IF NOT EXISTS human_chat_sessions (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, public_token CHAR(64) NOT NULL UNIQUE, visitor_name VARCHAR(80) NOT NULL DEFAULT 'Guest', status ENUM('waiting','active','closed') NOT NULL DEFAULT 'waiting', agent_name VARCHAR(80) NULL, last_activity DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, INDEX(status,last_activity)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $db->exec("CREATE TABLE IF NOT EXISTS human_chat_messages (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, session_id BIGINT UNSIGNED NOT NULL, sender ENUM('visitor','agent','system') NOT NULL, sender_name VARCHAR(80) NOT NULL, message TEXT NOT NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, INDEX(session_id,id), CONSTRAINT fk_human_chat_session FOREIGN KEY(session_id) REFERENCES human_chat_sessions(id) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
}

function cleanChatName(string $name, string $fallback): string {
    $name = trim(preg_replace('/\s+/u', ' ', strip_tags($name)));
    return mb_substr($name !== '' ? $name : $fallback, 0, 80);
}

function findHumanSession(PDO $db, string $token): ?array {
    if (!preg_match('/^[a-f0-9]{64}$/', $token)) return null;
    $stmt=$db->prepare('SELECT * FROM human_chat_sessions WHERE public_token=?');$stmt->execute([$token]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}
