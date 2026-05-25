-- Migration: Add chat system for friends

CREATE TABLE IF NOT EXISTS chat_message (
    pk_message_id INT AUTO_INCREMENT PRIMARY KEY,
    from_username VARCHAR(50) NOT NULL,
    to_username VARCHAR(50) NOT NULL,
    message TEXT NOT NULL,
    sent_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    read_at DATETIME,
    INDEX (from_username),
    INDEX (to_username),
    INDEX (sent_at),
    CONSTRAINT fk_chat_from_user FOREIGN KEY (from_username) REFERENCES user(pk_username) ON DELETE CASCADE,
    CONSTRAINT fk_chat_to_user FOREIGN KEY (to_username) REFERENCES user(pk_username) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
