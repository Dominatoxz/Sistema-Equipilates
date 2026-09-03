-- Segunda aprovação (impressão da etiqueta de reimpressão) vai pras lideranças,
-- não pra quem decidiu a inspeção de qualidade -- precisa separar os dois papéis
-- na mesma tabela de chats cadastrados.

ALTER TABLE qualidade_telegram_chats
  ADD COLUMN tipo ENUM('qualidade','lideranca') NOT NULL DEFAULT 'qualidade' AFTER chat_id;
