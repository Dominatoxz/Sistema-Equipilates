-- Gate de qualidade via Telegram.
-- Rodar uma vez no banco existente (config/Database.sql já reflete o schema final para instalações novas).

ALTER TABLE itens_producao
  ADD COLUMN status_qualidade ENUM('N/A','Aguardando','Aprovado','Reprovado') NOT NULL DEFAULT 'N/A',
  ADD COLUMN qualidade_tentativas INT NOT NULL DEFAULT 0,
  ADD COLUMN reimpressao_liberada TINYINT(1) NOT NULL DEFAULT 0;

ALTER TABLE itens_os
  ADD COLUMN status_qualidade ENUM('N/A','Aguardando','Aprovado','Reprovado') NOT NULL DEFAULT 'N/A',
  ADD COLUMN qualidade_tentativas INT NOT NULL DEFAULT 0,
  ADD COLUMN reimpressao_liberada TINYINT(1) NOT NULL DEFAULT 0;

CREATE TABLE IF NOT EXISTS `qualidade_inspecoes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tabela_origem` enum('itens_producao','itens_os') NOT NULL,
  `item_id` int(11) NOT NULL,
  `tentativa` int(11) NOT NULL,
  `decisao` enum('Aprovado','Reprovado') NOT NULL,
  `telegram_user` varchar(100) NOT NULL,
  `telegram_chat_id` varchar(50) NOT NULL,
  `criado_em` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_item` (`tabela_origem`,`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `qualidade_telegram_chats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `chat_id` varchar(50) NOT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `chat_id` (`chat_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
