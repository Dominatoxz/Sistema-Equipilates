-- Guarda cada cópia da mensagem de inspeção enviada (uma por chat) pra
-- poder editar TODAS quando alguém decide, não só a de quem clicou.
CREATE TABLE IF NOT EXISTS `qualidade_mensagens_enviadas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tabela_origem` enum('itens_producao','itens_os') NOT NULL,
  `item_id` int(11) NOT NULL,
  `tentativa` int(11) NOT NULL,
  `chat_id` varchar(50) NOT NULL,
  `message_id` bigint(20) NOT NULL,
  `criado_em` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_item` (`tabela_origem`,`item_id`,`tentativa`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
