USE planilha_db;

CREATE TABLE IF NOT EXISTS `tabela_adaptada` (
  `NUMERO PEDIDO` text DEFAULT NULL,
  `PRAZO DE PRODUCAO` text DEFAULT NULL,
  `MODELO` text DEFAULT NULL,
  `COD. COR` text DEFAULT NULL,
  `Column5` text DEFAULT NULL,
  `Reformer Excellence` text DEFAULT NULL,
  `Carrinho Excellence` text DEFAULT NULL,
  `Reformer Torre` text DEFAULT NULL,
  `Carrinho Torre` text DEFAULT NULL,
  `REFORMER X` text DEFAULT NULL,
  `Cadilac Excelence` text DEFAULT NULL,
  `Step Chair Excelence` text DEFAULT NULL,
  `Lader Barrel Excelence` text DEFAULT NULL,
  `Wall Unit` text DEFAULT NULL,
  `Caixa Mini` text DEFAULT NULL,
  `Caixa do Reformer` text DEFAULT NULL,
  `P. de Molas - B R I N D E` text DEFAULT NULL,
  `P. de Molas - C O M P L E T A` text DEFAULT NULL,
  `P. de Molas - P u s h T h r u` text DEFAULT NULL,
  `Caixa da Cadeira` text DEFAULT NULL,
  `Prancha de Alongamento` text DEFAULT NULL,
  `Column20` text DEFAULT NULL,
  `REF. CLASSICO ALUMINIO` text DEFAULT NULL,
  `CARRINHO CLASSICO` text DEFAULT NULL,
  `REF. CLASSICO TORRE` text DEFAULT NULL,
  `CARRINHO CLASSICO TORRE` text DEFAULT NULL,
  `CAD. CLASSICO ALUMINIO` text DEFAULT NULL,
  `GAIOLA CLASSICO` text DEFAULT NULL,
  `REF. CLASSICO TAUARI` text DEFAULT NULL,
  `CARRINHO CLASSICO TAUARI` text DEFAULT NULL,
  `CAD. CLASSICO TAUARI` text DEFAULT NULL,
  `GAIOLA CADILCAC TAUARI` text DEFAULT NULL,
  `REFORMER HIBRIDO` text DEFAULT NULL,
  `CARRINHO CLASSICO HIBRIDO` text DEFAULT NULL,
  `WUNDA CHAIR` text DEFAULT NULL,
  `ELECTRIC CHAIR` text DEFAULT NULL,
  `ARM CHAIR` text DEFAULT NULL,
  `LADDER BARREL CLÁSS.` text DEFAULT NULL,
  `PEDI O POLE` text DEFAULT NULL,
  `WALL UNIT CLÁSSICO` text DEFAULT NULL,
  `MAT CLÁSSICO` text DEFAULT NULL,
  `MAT PORTÁTIL` text DEFAULT NULL,
  `BENCH MAT` text DEFAULT NULL,
  `GUILHOTINA` text DEFAULT NULL,
  `CAIXA DO REFORMER CLÁSSICA` text DEFAULT NULL,
  `SPINE CORRECTOR` text DEFAULT NULL,
  `SMALL BARREL` text DEFAULT NULL,
  `SUPORTE SPINE CORRECTOR` text DEFAULT NULL,
  `MINI EXTENSÃO MOVE FLOW` text DEFAULT NULL,
  `PLATAFORMA BARREL CLÁSSICO` text DEFAULT NULL,
  `BARRA PUSH TRUE (BALANÇO CLASSICO)` text DEFAULT NULL,
  `SPACER BOX` text DEFAULT NULL,
  `2 x 4 (TWO BY FOUR)` text DEFAULT NULL,
  `KUNA BOARD` text DEFAULT NULL,
  `TRAVESSEIRO BENCH MAT` text DEFAULT NULL,
  `TRAVESSEIRO RÉGUA` text DEFAULT NULL,
  `TRAVESSEIRO 1/2 LUA` text DEFAULT NULL,
  `TRAV. CILINDRICO` text DEFAULT NULL,
  `TRAV. OMBREIRA (PAR)` text DEFAULT NULL,
  `TRAV. CABEC. 30 mm` text DEFAULT NULL,
  `TRAV. CABEC. 40 mm` text DEFAULT NULL,
  `CAPA PROT. BARREL CLÁSS.` text DEFAULT NULL,
  `SHEEPSKIN COVER` text DEFAULT NULL,
  `BASTÃO ALUMÍNIO 1,5 M` text DEFAULT NULL,
  `PUXADOR DE ALUMINIO` text DEFAULT NULL,
  `ANEL DE PILATES ARCHIVE AÇO` text DEFAULT NULL,
  `MAGIC SQUARE` text DEFAULT NULL,
  `FOOT CORREC. ALUM.` text DEFAULT NULL,
  `BEAN BAG` text DEFAULT NULL,
  `BREATH A CIZER` text DEFAULT NULL,
  `NECK STRETCHER` text DEFAULT NULL,
  `HAND TENS O METER` text DEFAULT NULL,
  `TOE EXERCISER` text DEFAULT NULL,
  `AIR PLANE BOARD` text DEFAULT NULL,
  `FINGER EXERCISE` text DEFAULT NULL,
  `PUSH UP DEVICE (PAR)` text DEFAULT NULL,
  `MINI BARREL` text DEFAULT NULL,
  `MINI SPINE` text DEFAULT NULL,
  `Previsto` text DEFAULT NULL,
  `Realizado` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `itens_producao` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `numero_pedido` varchar(50) NOT NULL,
  `prazo_producao` varchar(100) DEFAULT NULL,
  `equipamento` varchar(100) NOT NULL,
  `posicao_no_pedido` int(11) NOT NULL,
  `cor` varchar(100) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Pendente',
  `status_qualidade` enum('N/A','Aguardando','Aprovado','Reprovado') NOT NULL DEFAULT 'N/A',
  `qualidade_tentativas` int(11) NOT NULL DEFAULT 0,
  `reimpressao_liberada` tinyint(1) NOT NULL DEFAULT 0,
  `data_inicio` datetime DEFAULT NULL,
  `data_fim` datetime DEFAULT NULL,
  `data_armazem` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_itens_producao` (`numero_pedido`,`equipamento`,`posicao_no_pedido`),
  KEY `idx_pedidos_numero` (`numero_pedido`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `itens_os` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `numero_pedido` varchar(50) NOT NULL,
  `prazo_producao` varchar(255) DEFAULT NULL,
  `equipamento` varchar(100) NOT NULL,
  `posicao_no_pedido` int(11) NOT NULL,
  `cor` varchar(100) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Pendente',
  `status_qualidade` enum('N/A','Aguardando','Aprovado','Reprovado') NOT NULL DEFAULT 'N/A',
  `qualidade_tentativas` int(11) NOT NULL DEFAULT 0,
  `reimpressao_liberada` tinyint(1) NOT NULL DEFAULT 0,
  `data_inicio` datetime DEFAULT NULL,
  `data_fim` datetime DEFAULT NULL,
  `data_armazem` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_itens_os` (`numero_pedido`,`equipamento`,`posicao_no_pedido`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `pedidos_prontos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `numero_pedido` varchar(50) NOT NULL,
  `prazo_producao` varchar(255) DEFAULT NULL,
  `data_conclusao` datetime DEFAULT CURRENT_TIMESTAMP,
  `status_posvenda` varchar(50) DEFAULT 'Financeiro',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `pedidos_expedidos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `numero_pedido` varchar(50) NOT NULL,
  `prazo_producao` varchar(255) DEFAULT NULL,
  `data_conclusao` datetime DEFAULT CURRENT_TIMESTAMP,
  `status_posvenda` varchar(50) DEFAULT 'Pendente',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `pedidos_reprogramados` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `numero_pedido` varchar(50) NOT NULL,
  `prazo_producao` varchar(255) DEFAULT NULL,
  `origem_tela` varchar(30) NOT NULL,
  `motivo` varchar(255) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `usuario_nome` varchar(50) DEFAULT NULL,
  `criado_em` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `observacoes_expedicao` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_pedido` int(11) NOT NULL,
  `observacao` text NOT NULL,
  `data_criacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `observacoes_financeiro` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_pedido` int(11) NOT NULL,
  `observacao` text NOT NULL,
  `data_criacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `observacoes_posvenda` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_pedido` int(11) NOT NULL,
  `observacao` text NOT NULL,
  `data_criacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `impressoes_etiquetas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_item` int(11) NOT NULL,
  `tabela_origem` varchar(20) NOT NULL,
  `tipo_etiqueta` varchar(20) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `usuario_nome` varchar(50) DEFAULT NULL,
  `motivo_reimpressao` varchar(255) DEFAULT NULL,
  `criado_em` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_item_origem_tipo` (`id_item`,`tabela_origem`,`tipo_etiqueta`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario` varchar(50) NOT NULL,
  `senha_hash` varchar(255) NOT NULL,
  `nivel_acesso` varchar(20) DEFAULT 'operador',
  `criado_em` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `usuario` (`usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `login_tentativas` (
  `usuario` varchar(50) NOT NULL,
  `tentativas` int(11) NOT NULL DEFAULT 0,
  `bloqueado_ate` datetime DEFAULT NULL,
  `ultima_tentativa` datetime DEFAULT NULL,
  PRIMARY KEY (`usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `push_subscriptions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `nivel_acesso` varchar(20) NOT NULL,
  `endpoint` varchar(500) NOT NULL,
  `p256dh` varchar(255) NOT NULL,
  `auth` varchar(255) NOT NULL,
  `criado_em` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_endpoint` (`endpoint`(255)),
  KEY `idx_nivel_acesso` (`nivel_acesso`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `qualidade_inspecoes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tabela_origem` enum('itens_producao','itens_os') NOT NULL,
  `item_id` int(11) NOT NULL,
  `tentativa` int(11) NOT NULL,
  `decisao` enum('Aprovado','Reprovado','Retrabalho') NOT NULL,
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
  `tipo` enum('qualidade','lideranca') NOT NULL DEFAULT 'qualidade',
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `chat_id` (`chat_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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

CREATE TABLE IF NOT EXISTS `gaiola_atrasos_semanais` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tabela_itens` varchar(20) NOT NULL,
  `semana_inicio` date NOT NULL,
  `semana_fim` date NOT NULL,
  `planejado` int(11) NOT NULL,
  `real` int(11) NOT NULL,
  `deficit` int(11) NOT NULL,
  `criado_em` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_semana` (`tabela_itens`,`semana_inicio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- Atualizações posteriores (documentadas em 07/10/2026; extraídas do banco de produção)
-- ============================================================

-- Colunas que existem em produção e não estavam acima
ALTER TABLE `tabela_adaptada` ADD COLUMN IF NOT EXISTS `prioridade` tinyint(1) NOT NULL DEFAULT '0';
ALTER TABLE `pedidos_prontos` ADD COLUMN IF NOT EXISTS `data_entrada_posvenda` datetime NULL;
ALTER TABLE `pedidos_prontos` ADD COLUMN IF NOT EXISTS `data_entrada_expedicao` datetime NULL;

-- Rótulo de peça nas etiquetas (Function/rotulo_peca.php)
CREATE TABLE IF NOT EXISTS `item_rotulo_peca` (
  `tabela_origem` varchar(20) NOT NULL,
  `item_id` int(11) NOT NULL,
  `rotulo` varchar(40) NOT NULL,
  `criado_em` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`tabela_origem`,`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- Linha do tempo do quadro (Function/historico_quadro.php)
-- Para um banco novo: depois de criar as tabelas acima, rodar hqInstalar() (grava o estado-base e recria os triggers).
CREATE TABLE IF NOT EXISTS `historico_quadro` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `ocorrido_utc` datetime(3) NOT NULL,
  `tabela` varchar(30) NOT NULL,
  `operacao` char(1) NOT NULL COMMENT 'S=base, I=insert, U=update, D=delete',
  `chave` varchar(80) NOT NULL,
  `numero_pedido` varchar(80) DEFAULT NULL,
  `dados` longtext DEFAULT NULL COMMENT 'linha completa DEPOIS da mudança (null no delete)',
  PRIMARY KEY (`id`),
  KEY `idx_estado` (`tabela`,`chave`,`ocorrido_utc`),
  KEY `idx_tempo` (`ocorrido_utc`)
) ENGINE=InnoDB AUTO_INCREMENT=20001 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- Triggers hq_* (12) como estão em produção; hqInstalar() é a forma oficial de recriá-los.
DELIMITER $$
DROP TRIGGER IF EXISTS `hq_itens_os_ai`$$
CREATE DEFINER=`u109029190_usr_GN6XeAZr`@`localhost` TRIGGER hq_itens_os_ai AFTER INSERT ON `itens_os` FOR EACH ROW
            INSERT INTO historico_quadro (ocorrido_utc, tabela, operacao, chave, numero_pedido, dados)
            VALUES (UTC_TIMESTAMP(3), 'itens_os', 'I', CAST(NEW.`id` AS CHAR), CAST(NEW.numero_pedido AS CHAR), JSON_OBJECT('id', NEW.`id`, 'numero_pedido', NEW.`numero_pedido`, 'prazo_producao', NEW.`prazo_producao`, 'equipamento', NEW.`equipamento`, 'posicao_no_pedido', NEW.`posicao_no_pedido`, 'cor', NEW.`cor`, 'status', NEW.`status`, 'data_inicio', NEW.`data_inicio`, 'data_fim', NEW.`data_fim`, 'data_armazem', NEW.`data_armazem`, 'status_qualidade', NEW.`status_qualidade`, 'qualidade_tentativas', NEW.`qualidade_tentativas`, 'reimpressao_liberada', NEW.`reimpressao_liberada`))$$
DROP TRIGGER IF EXISTS `hq_itens_os_au`$$
CREATE DEFINER=`u109029190_usr_GN6XeAZr`@`localhost` TRIGGER hq_itens_os_au AFTER UPDATE ON `itens_os` FOR EACH ROW
            BEGIN
              DECLARE novo LONGTEXT; DECLARE velho LONGTEXT;
              SET novo = JSON_OBJECT('id', NEW.`id`, 'numero_pedido', NEW.`numero_pedido`, 'prazo_producao', NEW.`prazo_producao`, 'equipamento', NEW.`equipamento`, 'posicao_no_pedido', NEW.`posicao_no_pedido`, 'cor', NEW.`cor`, 'status', NEW.`status`, 'data_inicio', NEW.`data_inicio`, 'data_fim', NEW.`data_fim`, 'data_armazem', NEW.`data_armazem`, 'status_qualidade', NEW.`status_qualidade`, 'qualidade_tentativas', NEW.`qualidade_tentativas`, 'reimpressao_liberada', NEW.`reimpressao_liberada`);
              SET velho = JSON_OBJECT('id', OLD.`id`, 'numero_pedido', OLD.`numero_pedido`, 'prazo_producao', OLD.`prazo_producao`, 'equipamento', OLD.`equipamento`, 'posicao_no_pedido', OLD.`posicao_no_pedido`, 'cor', OLD.`cor`, 'status', OLD.`status`, 'data_inicio', OLD.`data_inicio`, 'data_fim', OLD.`data_fim`, 'data_armazem', OLD.`data_armazem`, 'status_qualidade', OLD.`status_qualidade`, 'qualidade_tentativas', OLD.`qualidade_tentativas`, 'reimpressao_liberada', OLD.`reimpressao_liberada`);
              IF NOT (novo <=> velho) THEN
                IF NOT ((CAST(OLD.`id` AS CHAR)) <=> (CAST(NEW.`id` AS CHAR))) THEN
                  INSERT INTO historico_quadro (ocorrido_utc, tabela, operacao, chave, numero_pedido, dados)
                  VALUES (UTC_TIMESTAMP(3), 'itens_os', 'D', CAST(OLD.`id` AS CHAR), CAST(OLD.numero_pedido AS CHAR), NULL);
                END IF;
                INSERT INTO historico_quadro (ocorrido_utc, tabela, operacao, chave, numero_pedido, dados)
                VALUES (UTC_TIMESTAMP(3), 'itens_os', 'U', CAST(NEW.`id` AS CHAR), CAST(NEW.numero_pedido AS CHAR), novo);
              END IF;
            END$$
DROP TRIGGER IF EXISTS `hq_itens_os_ad`$$
CREATE DEFINER=`u109029190_usr_GN6XeAZr`@`localhost` TRIGGER hq_itens_os_ad AFTER DELETE ON `itens_os` FOR EACH ROW
            INSERT INTO historico_quadro (ocorrido_utc, tabela, operacao, chave, numero_pedido, dados)
            VALUES (UTC_TIMESTAMP(3), 'itens_os', 'D', CAST(OLD.`id` AS CHAR), CAST(OLD.numero_pedido AS CHAR), NULL)$$
DROP TRIGGER IF EXISTS `hq_itens_producao_ai`$$
CREATE DEFINER=`u109029190_usr_GN6XeAZr`@`localhost` TRIGGER hq_itens_producao_ai AFTER INSERT ON `itens_producao` FOR EACH ROW
            INSERT INTO historico_quadro (ocorrido_utc, tabela, operacao, chave, numero_pedido, dados)
            VALUES (UTC_TIMESTAMP(3), 'itens_producao', 'I', CAST(NEW.`id` AS CHAR), CAST(NEW.numero_pedido AS CHAR), JSON_OBJECT('id', NEW.`id`, 'numero_pedido', NEW.`numero_pedido`, 'prazo_producao', NEW.`prazo_producao`, 'equipamento', NEW.`equipamento`, 'posicao_no_pedido', NEW.`posicao_no_pedido`, 'cor', NEW.`cor`, 'status', NEW.`status`, 'data_inicio', NEW.`data_inicio`, 'data_fim', NEW.`data_fim`, 'data_armazem', NEW.`data_armazem`, 'status_qualidade', NEW.`status_qualidade`, 'qualidade_tentativas', NEW.`qualidade_tentativas`, 'reimpressao_liberada', NEW.`reimpressao_liberada`))$$
DROP TRIGGER IF EXISTS `hq_itens_producao_au`$$
CREATE DEFINER=`u109029190_usr_GN6XeAZr`@`localhost` TRIGGER hq_itens_producao_au AFTER UPDATE ON `itens_producao` FOR EACH ROW
            BEGIN
              DECLARE novo LONGTEXT; DECLARE velho LONGTEXT;
              SET novo = JSON_OBJECT('id', NEW.`id`, 'numero_pedido', NEW.`numero_pedido`, 'prazo_producao', NEW.`prazo_producao`, 'equipamento', NEW.`equipamento`, 'posicao_no_pedido', NEW.`posicao_no_pedido`, 'cor', NEW.`cor`, 'status', NEW.`status`, 'data_inicio', NEW.`data_inicio`, 'data_fim', NEW.`data_fim`, 'data_armazem', NEW.`data_armazem`, 'status_qualidade', NEW.`status_qualidade`, 'qualidade_tentativas', NEW.`qualidade_tentativas`, 'reimpressao_liberada', NEW.`reimpressao_liberada`);
              SET velho = JSON_OBJECT('id', OLD.`id`, 'numero_pedido', OLD.`numero_pedido`, 'prazo_producao', OLD.`prazo_producao`, 'equipamento', OLD.`equipamento`, 'posicao_no_pedido', OLD.`posicao_no_pedido`, 'cor', OLD.`cor`, 'status', OLD.`status`, 'data_inicio', OLD.`data_inicio`, 'data_fim', OLD.`data_fim`, 'data_armazem', OLD.`data_armazem`, 'status_qualidade', OLD.`status_qualidade`, 'qualidade_tentativas', OLD.`qualidade_tentativas`, 'reimpressao_liberada', OLD.`reimpressao_liberada`);
              IF NOT (novo <=> velho) THEN
                IF NOT ((CAST(OLD.`id` AS CHAR)) <=> (CAST(NEW.`id` AS CHAR))) THEN
                  INSERT INTO historico_quadro (ocorrido_utc, tabela, operacao, chave, numero_pedido, dados)
                  VALUES (UTC_TIMESTAMP(3), 'itens_producao', 'D', CAST(OLD.`id` AS CHAR), CAST(OLD.numero_pedido AS CHAR), NULL);
                END IF;
                INSERT INTO historico_quadro (ocorrido_utc, tabela, operacao, chave, numero_pedido, dados)
                VALUES (UTC_TIMESTAMP(3), 'itens_producao', 'U', CAST(NEW.`id` AS CHAR), CAST(NEW.numero_pedido AS CHAR), novo);
              END IF;
            END$$
DROP TRIGGER IF EXISTS `hq_itens_producao_ad`$$
CREATE DEFINER=`u109029190_usr_GN6XeAZr`@`localhost` TRIGGER hq_itens_producao_ad AFTER DELETE ON `itens_producao` FOR EACH ROW
            INSERT INTO historico_quadro (ocorrido_utc, tabela, operacao, chave, numero_pedido, dados)
            VALUES (UTC_TIMESTAMP(3), 'itens_producao', 'D', CAST(OLD.`id` AS CHAR), CAST(OLD.numero_pedido AS CHAR), NULL)$$
DROP TRIGGER IF EXISTS `hq_pedidos_prontos_ai`$$
CREATE DEFINER=`u109029190_usr_GN6XeAZr`@`localhost` TRIGGER hq_pedidos_prontos_ai AFTER INSERT ON `pedidos_prontos` FOR EACH ROW
            INSERT INTO historico_quadro (ocorrido_utc, tabela, operacao, chave, numero_pedido, dados)
            VALUES (UTC_TIMESTAMP(3), 'pedidos_prontos', 'I', CAST(NEW.`id` AS CHAR), CAST(NEW.numero_pedido AS CHAR), JSON_OBJECT('id', NEW.`id`, 'numero_pedido', NEW.`numero_pedido`, 'prazo_producao', NEW.`prazo_producao`, 'data_conclusao', NEW.`data_conclusao`, 'status_posvenda', NEW.`status_posvenda`, 'data_entrada_posvenda', NEW.`data_entrada_posvenda`, 'data_entrada_expedicao', NEW.`data_entrada_expedicao`))$$
DROP TRIGGER IF EXISTS `hq_pedidos_prontos_au`$$
CREATE DEFINER=`u109029190_usr_GN6XeAZr`@`localhost` TRIGGER hq_pedidos_prontos_au AFTER UPDATE ON `pedidos_prontos` FOR EACH ROW
            BEGIN
              DECLARE novo LONGTEXT; DECLARE velho LONGTEXT;
              SET novo = JSON_OBJECT('id', NEW.`id`, 'numero_pedido', NEW.`numero_pedido`, 'prazo_producao', NEW.`prazo_producao`, 'data_conclusao', NEW.`data_conclusao`, 'status_posvenda', NEW.`status_posvenda`, 'data_entrada_posvenda', NEW.`data_entrada_posvenda`, 'data_entrada_expedicao', NEW.`data_entrada_expedicao`);
              SET velho = JSON_OBJECT('id', OLD.`id`, 'numero_pedido', OLD.`numero_pedido`, 'prazo_producao', OLD.`prazo_producao`, 'data_conclusao', OLD.`data_conclusao`, 'status_posvenda', OLD.`status_posvenda`, 'data_entrada_posvenda', OLD.`data_entrada_posvenda`, 'data_entrada_expedicao', OLD.`data_entrada_expedicao`);
              IF NOT (novo <=> velho) THEN
                IF NOT ((CAST(OLD.`id` AS CHAR)) <=> (CAST(NEW.`id` AS CHAR))) THEN
                  INSERT INTO historico_quadro (ocorrido_utc, tabela, operacao, chave, numero_pedido, dados)
                  VALUES (UTC_TIMESTAMP(3), 'pedidos_prontos', 'D', CAST(OLD.`id` AS CHAR), CAST(OLD.numero_pedido AS CHAR), NULL);
                END IF;
                INSERT INTO historico_quadro (ocorrido_utc, tabela, operacao, chave, numero_pedido, dados)
                VALUES (UTC_TIMESTAMP(3), 'pedidos_prontos', 'U', CAST(NEW.`id` AS CHAR), CAST(NEW.numero_pedido AS CHAR), novo);
              END IF;
            END$$
DROP TRIGGER IF EXISTS `hq_pedidos_prontos_ad`$$
CREATE DEFINER=`u109029190_usr_GN6XeAZr`@`localhost` TRIGGER hq_pedidos_prontos_ad AFTER DELETE ON `pedidos_prontos` FOR EACH ROW
            INSERT INTO historico_quadro (ocorrido_utc, tabela, operacao, chave, numero_pedido, dados)
            VALUES (UTC_TIMESTAMP(3), 'pedidos_prontos', 'D', CAST(OLD.`id` AS CHAR), CAST(OLD.numero_pedido AS CHAR), NULL)$$
DROP TRIGGER IF EXISTS `hq_tabela_adaptada_ai`$$
CREATE DEFINER=`u109029190_usr_GN6XeAZr`@`localhost` TRIGGER hq_tabela_adaptada_ai AFTER INSERT ON `tabela_adaptada` FOR EACH ROW
            INSERT INTO historico_quadro (ocorrido_utc, tabela, operacao, chave, numero_pedido, dados)
            VALUES (UTC_TIMESTAMP(3), 'tabela_adaptada', 'I', CONCAT(CAST(NEW.`NUMERO PEDIDO` AS CHAR), '#', MD5(JSON_OBJECT('NUMERO PEDIDO', NEW.`NUMERO PEDIDO`, 'PRAZO DE PRODUCAO', NEW.`PRAZO DE PRODUCAO`, 'MODELO', NEW.`MODELO`, 'COD. COR', NEW.`COD. COR`, 'Column5', NEW.`Column5`, 'Reformer Excellence', NEW.`Reformer Excellence`, 'Reformer Torre', NEW.`Reformer Torre`, 'REFORMER X', NEW.`REFORMER X`, 'Cadilac Excelence', NEW.`Cadilac Excelence`, 'Step Chair Excelence', NEW.`Step Chair Excelence`, 'Lader Barrel Excelence', NEW.`Lader Barrel Excelence`, 'Wall Unit', NEW.`Wall Unit`, 'Caixa Mini', NEW.`Caixa Mini`, 'Caixa do Reformer', NEW.`Caixa do Reformer`, 'P. de Molas - B R I N D E', NEW.`P. de Molas - B R I N D E`, 'P. de Molas - C O M P L E T A', NEW.`P. de Molas - C O M P L E T A`, 'P. de Molas - P u s h T h r u', NEW.`P. de Molas - P u s h T h r u`, 'Caixa da Cadeira', NEW.`Caixa da Cadeira`, 'Prancha de Alongamento', NEW.`Prancha de Alongamento`, 'Column20', NEW.`Column20`, 'REF. CLASSICO ALUMINIO', NEW.`REF. CLASSICO ALUMINIO`, 'REF. CLASSICO TORRE', NEW.`REF. CLASSICO TORRE`, 'CAD. CLASSICO ALUMINIO', NEW.`CAD. CLASSICO ALUMINIO`, 'REF. CLASSICO TAUARI', NEW.`REF. CLASSICO TAUARI`, 'CAD. CLASSICO TAUARI', NEW.`CAD. CLASSICO TAUARI`, 'REFORMER HIBRIDO', NEW.`REFORMER HIBRIDO`, 'WUNDA CHAIR', NEW.`WUNDA CHAIR`, 'ELECTRIC CHAIR', NEW.`ELECTRIC CHAIR`, 'ARM CHAIR', NEW.`ARM CHAIR`, 'LADDER BARREL CLÁSS.', NEW.`LADDER BARREL CLÁSS.`, 'PEDI O POLE', NEW.`PEDI O POLE`, 'WALL UNIT CLÁSSICO', NEW.`WALL UNIT CLÁSSICO`, 'MAT CLÁSSICO', NEW.`MAT CLÁSSICO`, 'MAT PORTÁTIL', NEW.`MAT PORTÁTIL`, 'BENCH MAT', NEW.`BENCH MAT`, 'GUILHOTINA', NEW.`GUILHOTINA`, 'CAIXA DO REFORMER CLÁSSICA', NEW.`CAIXA DO REFORMER CLÁSSICA`, 'SPINE CORRECTOR', NEW.`SPINE CORRECTOR`, 'SMALL BARREL', NEW.`SMALL BARREL`, 'SUPORTE SPINE CORRECTOR', NEW.`SUPORTE SPINE CORRECTOR`, 'MINI EXTENSÃO MOVE FLOW', NEW.`MINI EXTENSÃO MOVE FLOW`, 'PLATAFORMA BARREL CLÁSSICO', NEW.`PLATAFORMA BARREL CLÁSSICO`, 'BARRA PUSH TRUE (BALANÇO CLASSICO)', NEW.`BARRA PUSH TRUE (BALANÇO CLASSICO)`, 'SPACER BOX', NEW.`SPACER BOX`, '2 x 4 (TWO BY FOUR)', NEW.`2 x 4 (TWO BY FOUR)`, 'KUNA BOARD', NEW.`KUNA BOARD`, 'TRAVESSEIRO BENCH MAT', NEW.`TRAVESSEIRO BENCH MAT`, 'TRAVESSEIRO RÉGUA', NEW.`TRAVESSEIRO RÉGUA`, 'TRAVESSEIRO 1/2 LUA', NEW.`TRAVESSEIRO 1/2 LUA`, 'TRAV. CILINDRICO', NEW.`TRAV. CILINDRICO`, 'TRAV. OMBREIRA (PAR)', NEW.`TRAV. OMBREIRA (PAR)`, 'TRAV. CABEC. 30 mm', NEW.`TRAV. CABEC. 30 mm`, 'TRAV. CABEC. 40 mm', NEW.`TRAV. CABEC. 40 mm`, 'CAPA PROT. BARREL CLÁSS.', NEW.`CAPA PROT. BARREL CLÁSS.`, 'SHEEPSKIN COVER', NEW.`SHEEPSKIN COVER`, 'BASTÃO ALUMÍNIO 1,5 M', NEW.`BASTÃO ALUMÍNIO 1,5 M`, 'PUXADOR DE ALUMINIO', NEW.`PUXADOR DE ALUMINIO`, 'ANEL DE PILATES ARCHIVE AÇO', NEW.`ANEL DE PILATES ARCHIVE AÇO`, 'MAGIC SQUARE', NEW.`MAGIC SQUARE`, 'FOOT CORREC. ALUM.', NEW.`FOOT CORREC. ALUM.`, 'BEAN BAG', NEW.`BEAN BAG`, 'BREATH A CIZER', NEW.`BREATH A CIZER`, 'NECK STRETCHER', NEW.`NECK STRETCHER`, 'HAND TENS O METER', NEW.`HAND TENS O METER`, 'TOE EXERCISER', NEW.`TOE EXERCISER`, 'AIR PLANE BOARD', NEW.`AIR PLANE BOARD`, 'FINGER EXERCISE', NEW.`FINGER EXERCISE`, 'PUSH UP DEVICE (PAR)', NEW.`PUSH UP DEVICE (PAR)`, 'MINI BARREL', NEW.`MINI BARREL`, 'MINI SPINE', NEW.`MINI SPINE`, 'Previsto', NEW.`Previsto`, 'Realizado', NEW.`Realizado`, 'prioridade', NEW.`prioridade`))), CAST(NEW.`NUMERO PEDIDO` AS CHAR), JSON_OBJECT('NUMERO PEDIDO', NEW.`NUMERO PEDIDO`, 'PRAZO DE PRODUCAO', NEW.`PRAZO DE PRODUCAO`, 'MODELO', NEW.`MODELO`, 'COD. COR', NEW.`COD. COR`, 'Column5', NEW.`Column5`, 'Reformer Excellence', NEW.`Reformer Excellence`, 'Reformer Torre', NEW.`Reformer Torre`, 'REFORMER X', NEW.`REFORMER X`, 'Cadilac Excelence', NEW.`Cadilac Excelence`, 'Step Chair Excelence', NEW.`Step Chair Excelence`, 'Lader Barrel Excelence', NEW.`Lader Barrel Excelence`, 'Wall Unit', NEW.`Wall Unit`, 'Caixa Mini', NEW.`Caixa Mini`, 'Caixa do Reformer', NEW.`Caixa do Reformer`, 'P. de Molas - B R I N D E', NEW.`P. de Molas - B R I N D E`, 'P. de Molas - C O M P L E T A', NEW.`P. de Molas - C O M P L E T A`, 'P. de Molas - P u s h T h r u', NEW.`P. de Molas - P u s h T h r u`, 'Caixa da Cadeira', NEW.`Caixa da Cadeira`, 'Prancha de Alongamento', NEW.`Prancha de Alongamento`, 'Column20', NEW.`Column20`, 'REF. CLASSICO ALUMINIO', NEW.`REF. CLASSICO ALUMINIO`, 'REF. CLASSICO TORRE', NEW.`REF. CLASSICO TORRE`, 'CAD. CLASSICO ALUMINIO', NEW.`CAD. CLASSICO ALUMINIO`, 'REF. CLASSICO TAUARI', NEW.`REF. CLASSICO TAUARI`, 'CAD. CLASSICO TAUARI', NEW.`CAD. CLASSICO TAUARI`, 'REFORMER HIBRIDO', NEW.`REFORMER HIBRIDO`, 'WUNDA CHAIR', NEW.`WUNDA CHAIR`, 'ELECTRIC CHAIR', NEW.`ELECTRIC CHAIR`, 'ARM CHAIR', NEW.`ARM CHAIR`, 'LADDER BARREL CLÁSS.', NEW.`LADDER BARREL CLÁSS.`, 'PEDI O POLE', NEW.`PEDI O POLE`, 'WALL UNIT CLÁSSICO', NEW.`WALL UNIT CLÁSSICO`, 'MAT CLÁSSICO', NEW.`MAT CLÁSSICO`, 'MAT PORTÁTIL', NEW.`MAT PORTÁTIL`, 'BENCH MAT', NEW.`BENCH MAT`, 'GUILHOTINA', NEW.`GUILHOTINA`, 'CAIXA DO REFORMER CLÁSSICA', NEW.`CAIXA DO REFORMER CLÁSSICA`, 'SPINE CORRECTOR', NEW.`SPINE CORRECTOR`, 'SMALL BARREL', NEW.`SMALL BARREL`, 'SUPORTE SPINE CORRECTOR', NEW.`SUPORTE SPINE CORRECTOR`, 'MINI EXTENSÃO MOVE FLOW', NEW.`MINI EXTENSÃO MOVE FLOW`, 'PLATAFORMA BARREL CLÁSSICO', NEW.`PLATAFORMA BARREL CLÁSSICO`, 'BARRA PUSH TRUE (BALANÇO CLASSICO)', NEW.`BARRA PUSH TRUE (BALANÇO CLASSICO)`, 'SPACER BOX', NEW.`SPACER BOX`, '2 x 4 (TWO BY FOUR)', NEW.`2 x 4 (TWO BY FOUR)`, 'KUNA BOARD', NEW.`KUNA BOARD`, 'TRAVESSEIRO BENCH MAT', NEW.`TRAVESSEIRO BENCH MAT`, 'TRAVESSEIRO RÉGUA', NEW.`TRAVESSEIRO RÉGUA`, 'TRAVESSEIRO 1/2 LUA', NEW.`TRAVESSEIRO 1/2 LUA`, 'TRAV. CILINDRICO', NEW.`TRAV. CILINDRICO`, 'TRAV. OMBREIRA (PAR)', NEW.`TRAV. OMBREIRA (PAR)`, 'TRAV. CABEC. 30 mm', NEW.`TRAV. CABEC. 30 mm`, 'TRAV. CABEC. 40 mm', NEW.`TRAV. CABEC. 40 mm`, 'CAPA PROT. BARREL CLÁSS.', NEW.`CAPA PROT. BARREL CLÁSS.`, 'SHEEPSKIN COVER', NEW.`SHEEPSKIN COVER`, 'BASTÃO ALUMÍNIO 1,5 M', NEW.`BASTÃO ALUMÍNIO 1,5 M`, 'PUXADOR DE ALUMINIO', NEW.`PUXADOR DE ALUMINIO`, 'ANEL DE PILATES ARCHIVE AÇO', NEW.`ANEL DE PILATES ARCHIVE AÇO`, 'MAGIC SQUARE', NEW.`MAGIC SQUARE`, 'FOOT CORREC. ALUM.', NEW.`FOOT CORREC. ALUM.`, 'BEAN BAG', NEW.`BEAN BAG`, 'BREATH A CIZER', NEW.`BREATH A CIZER`, 'NECK STRETCHER', NEW.`NECK STRETCHER`, 'HAND TENS O METER', NEW.`HAND TENS O METER`, 'TOE EXERCISER', NEW.`TOE EXERCISER`, 'AIR PLANE BOARD', NEW.`AIR PLANE BOARD`, 'FINGER EXERCISE', NEW.`FINGER EXERCISE`, 'PUSH UP DEVICE (PAR)', NEW.`PUSH UP DEVICE (PAR)`, 'MINI BARREL', NEW.`MINI BARREL`, 'MINI SPINE', NEW.`MINI SPINE`, 'Previsto', NEW.`Previsto`, 'Realizado', NEW.`Realizado`, 'prioridade', NEW.`prioridade`))$$
DROP TRIGGER IF EXISTS `hq_tabela_adaptada_au`$$
CREATE DEFINER=`u109029190_usr_GN6XeAZr`@`localhost` TRIGGER hq_tabela_adaptada_au AFTER UPDATE ON `tabela_adaptada` FOR EACH ROW
            BEGIN
              DECLARE novo LONGTEXT; DECLARE velho LONGTEXT;
              SET novo = JSON_OBJECT('NUMERO PEDIDO', NEW.`NUMERO PEDIDO`, 'PRAZO DE PRODUCAO', NEW.`PRAZO DE PRODUCAO`, 'MODELO', NEW.`MODELO`, 'COD. COR', NEW.`COD. COR`, 'Column5', NEW.`Column5`, 'Reformer Excellence', NEW.`Reformer Excellence`, 'Reformer Torre', NEW.`Reformer Torre`, 'REFORMER X', NEW.`REFORMER X`, 'Cadilac Excelence', NEW.`Cadilac Excelence`, 'Step Chair Excelence', NEW.`Step Chair Excelence`, 'Lader Barrel Excelence', NEW.`Lader Barrel Excelence`, 'Wall Unit', NEW.`Wall Unit`, 'Caixa Mini', NEW.`Caixa Mini`, 'Caixa do Reformer', NEW.`Caixa do Reformer`, 'P. de Molas - B R I N D E', NEW.`P. de Molas - B R I N D E`, 'P. de Molas - C O M P L E T A', NEW.`P. de Molas - C O M P L E T A`, 'P. de Molas - P u s h T h r u', NEW.`P. de Molas - P u s h T h r u`, 'Caixa da Cadeira', NEW.`Caixa da Cadeira`, 'Prancha de Alongamento', NEW.`Prancha de Alongamento`, 'Column20', NEW.`Column20`, 'REF. CLASSICO ALUMINIO', NEW.`REF. CLASSICO ALUMINIO`, 'REF. CLASSICO TORRE', NEW.`REF. CLASSICO TORRE`, 'CAD. CLASSICO ALUMINIO', NEW.`CAD. CLASSICO ALUMINIO`, 'REF. CLASSICO TAUARI', NEW.`REF. CLASSICO TAUARI`, 'CAD. CLASSICO TAUARI', NEW.`CAD. CLASSICO TAUARI`, 'REFORMER HIBRIDO', NEW.`REFORMER HIBRIDO`, 'WUNDA CHAIR', NEW.`WUNDA CHAIR`, 'ELECTRIC CHAIR', NEW.`ELECTRIC CHAIR`, 'ARM CHAIR', NEW.`ARM CHAIR`, 'LADDER BARREL CLÁSS.', NEW.`LADDER BARREL CLÁSS.`, 'PEDI O POLE', NEW.`PEDI O POLE`, 'WALL UNIT CLÁSSICO', NEW.`WALL UNIT CLÁSSICO`, 'MAT CLÁSSICO', NEW.`MAT CLÁSSICO`, 'MAT PORTÁTIL', NEW.`MAT PORTÁTIL`, 'BENCH MAT', NEW.`BENCH MAT`, 'GUILHOTINA', NEW.`GUILHOTINA`, 'CAIXA DO REFORMER CLÁSSICA', NEW.`CAIXA DO REFORMER CLÁSSICA`, 'SPINE CORRECTOR', NEW.`SPINE CORRECTOR`, 'SMALL BARREL', NEW.`SMALL BARREL`, 'SUPORTE SPINE CORRECTOR', NEW.`SUPORTE SPINE CORRECTOR`, 'MINI EXTENSÃO MOVE FLOW', NEW.`MINI EXTENSÃO MOVE FLOW`, 'PLATAFORMA BARREL CLÁSSICO', NEW.`PLATAFORMA BARREL CLÁSSICO`, 'BARRA PUSH TRUE (BALANÇO CLASSICO)', NEW.`BARRA PUSH TRUE (BALANÇO CLASSICO)`, 'SPACER BOX', NEW.`SPACER BOX`, '2 x 4 (TWO BY FOUR)', NEW.`2 x 4 (TWO BY FOUR)`, 'KUNA BOARD', NEW.`KUNA BOARD`, 'TRAVESSEIRO BENCH MAT', NEW.`TRAVESSEIRO BENCH MAT`, 'TRAVESSEIRO RÉGUA', NEW.`TRAVESSEIRO RÉGUA`, 'TRAVESSEIRO 1/2 LUA', NEW.`TRAVESSEIRO 1/2 LUA`, 'TRAV. CILINDRICO', NEW.`TRAV. CILINDRICO`, 'TRAV. OMBREIRA (PAR)', NEW.`TRAV. OMBREIRA (PAR)`, 'TRAV. CABEC. 30 mm', NEW.`TRAV. CABEC. 30 mm`, 'TRAV. CABEC. 40 mm', NEW.`TRAV. CABEC. 40 mm`, 'CAPA PROT. BARREL CLÁSS.', NEW.`CAPA PROT. BARREL CLÁSS.`, 'SHEEPSKIN COVER', NEW.`SHEEPSKIN COVER`, 'BASTÃO ALUMÍNIO 1,5 M', NEW.`BASTÃO ALUMÍNIO 1,5 M`, 'PUXADOR DE ALUMINIO', NEW.`PUXADOR DE ALUMINIO`, 'ANEL DE PILATES ARCHIVE AÇO', NEW.`ANEL DE PILATES ARCHIVE AÇO`, 'MAGIC SQUARE', NEW.`MAGIC SQUARE`, 'FOOT CORREC. ALUM.', NEW.`FOOT CORREC. ALUM.`, 'BEAN BAG', NEW.`BEAN BAG`, 'BREATH A CIZER', NEW.`BREATH A CIZER`, 'NECK STRETCHER', NEW.`NECK STRETCHER`, 'HAND TENS O METER', NEW.`HAND TENS O METER`, 'TOE EXERCISER', NEW.`TOE EXERCISER`, 'AIR PLANE BOARD', NEW.`AIR PLANE BOARD`, 'FINGER EXERCISE', NEW.`FINGER EXERCISE`, 'PUSH UP DEVICE (PAR)', NEW.`PUSH UP DEVICE (PAR)`, 'MINI BARREL', NEW.`MINI BARREL`, 'MINI SPINE', NEW.`MINI SPINE`, 'Previsto', NEW.`Previsto`, 'Realizado', NEW.`Realizado`, 'prioridade', NEW.`prioridade`);
              SET velho = JSON_OBJECT('NUMERO PEDIDO', OLD.`NUMERO PEDIDO`, 'PRAZO DE PRODUCAO', OLD.`PRAZO DE PRODUCAO`, 'MODELO', OLD.`MODELO`, 'COD. COR', OLD.`COD. COR`, 'Column5', OLD.`Column5`, 'Reformer Excellence', OLD.`Reformer Excellence`, 'Reformer Torre', OLD.`Reformer Torre`, 'REFORMER X', OLD.`REFORMER X`, 'Cadilac Excelence', OLD.`Cadilac Excelence`, 'Step Chair Excelence', OLD.`Step Chair Excelence`, 'Lader Barrel Excelence', OLD.`Lader Barrel Excelence`, 'Wall Unit', OLD.`Wall Unit`, 'Caixa Mini', OLD.`Caixa Mini`, 'Caixa do Reformer', OLD.`Caixa do Reformer`, 'P. de Molas - B R I N D E', OLD.`P. de Molas - B R I N D E`, 'P. de Molas - C O M P L E T A', OLD.`P. de Molas - C O M P L E T A`, 'P. de Molas - P u s h T h r u', OLD.`P. de Molas - P u s h T h r u`, 'Caixa da Cadeira', OLD.`Caixa da Cadeira`, 'Prancha de Alongamento', OLD.`Prancha de Alongamento`, 'Column20', OLD.`Column20`, 'REF. CLASSICO ALUMINIO', OLD.`REF. CLASSICO ALUMINIO`, 'REF. CLASSICO TORRE', OLD.`REF. CLASSICO TORRE`, 'CAD. CLASSICO ALUMINIO', OLD.`CAD. CLASSICO ALUMINIO`, 'REF. CLASSICO TAUARI', OLD.`REF. CLASSICO TAUARI`, 'CAD. CLASSICO TAUARI', OLD.`CAD. CLASSICO TAUARI`, 'REFORMER HIBRIDO', OLD.`REFORMER HIBRIDO`, 'WUNDA CHAIR', OLD.`WUNDA CHAIR`, 'ELECTRIC CHAIR', OLD.`ELECTRIC CHAIR`, 'ARM CHAIR', OLD.`ARM CHAIR`, 'LADDER BARREL CLÁSS.', OLD.`LADDER BARREL CLÁSS.`, 'PEDI O POLE', OLD.`PEDI O POLE`, 'WALL UNIT CLÁSSICO', OLD.`WALL UNIT CLÁSSICO`, 'MAT CLÁSSICO', OLD.`MAT CLÁSSICO`, 'MAT PORTÁTIL', OLD.`MAT PORTÁTIL`, 'BENCH MAT', OLD.`BENCH MAT`, 'GUILHOTINA', OLD.`GUILHOTINA`, 'CAIXA DO REFORMER CLÁSSICA', OLD.`CAIXA DO REFORMER CLÁSSICA`, 'SPINE CORRECTOR', OLD.`SPINE CORRECTOR`, 'SMALL BARREL', OLD.`SMALL BARREL`, 'SUPORTE SPINE CORRECTOR', OLD.`SUPORTE SPINE CORRECTOR`, 'MINI EXTENSÃO MOVE FLOW', OLD.`MINI EXTENSÃO MOVE FLOW`, 'PLATAFORMA BARREL CLÁSSICO', OLD.`PLATAFORMA BARREL CLÁSSICO`, 'BARRA PUSH TRUE (BALANÇO CLASSICO)', OLD.`BARRA PUSH TRUE (BALANÇO CLASSICO)`, 'SPACER BOX', OLD.`SPACER BOX`, '2 x 4 (TWO BY FOUR)', OLD.`2 x 4 (TWO BY FOUR)`, 'KUNA BOARD', OLD.`KUNA BOARD`, 'TRAVESSEIRO BENCH MAT', OLD.`TRAVESSEIRO BENCH MAT`, 'TRAVESSEIRO RÉGUA', OLD.`TRAVESSEIRO RÉGUA`, 'TRAVESSEIRO 1/2 LUA', OLD.`TRAVESSEIRO 1/2 LUA`, 'TRAV. CILINDRICO', OLD.`TRAV. CILINDRICO`, 'TRAV. OMBREIRA (PAR)', OLD.`TRAV. OMBREIRA (PAR)`, 'TRAV. CABEC. 30 mm', OLD.`TRAV. CABEC. 30 mm`, 'TRAV. CABEC. 40 mm', OLD.`TRAV. CABEC. 40 mm`, 'CAPA PROT. BARREL CLÁSS.', OLD.`CAPA PROT. BARREL CLÁSS.`, 'SHEEPSKIN COVER', OLD.`SHEEPSKIN COVER`, 'BASTÃO ALUMÍNIO 1,5 M', OLD.`BASTÃO ALUMÍNIO 1,5 M`, 'PUXADOR DE ALUMINIO', OLD.`PUXADOR DE ALUMINIO`, 'ANEL DE PILATES ARCHIVE AÇO', OLD.`ANEL DE PILATES ARCHIVE AÇO`, 'MAGIC SQUARE', OLD.`MAGIC SQUARE`, 'FOOT CORREC. ALUM.', OLD.`FOOT CORREC. ALUM.`, 'BEAN BAG', OLD.`BEAN BAG`, 'BREATH A CIZER', OLD.`BREATH A CIZER`, 'NECK STRETCHER', OLD.`NECK STRETCHER`, 'HAND TENS O METER', OLD.`HAND TENS O METER`, 'TOE EXERCISER', OLD.`TOE EXERCISER`, 'AIR PLANE BOARD', OLD.`AIR PLANE BOARD`, 'FINGER EXERCISE', OLD.`FINGER EXERCISE`, 'PUSH UP DEVICE (PAR)', OLD.`PUSH UP DEVICE (PAR)`, 'MINI BARREL', OLD.`MINI BARREL`, 'MINI SPINE', OLD.`MINI SPINE`, 'Previsto', OLD.`Previsto`, 'Realizado', OLD.`Realizado`, 'prioridade', OLD.`prioridade`);
              IF NOT (novo <=> velho) THEN
                IF NOT ((CONCAT(CAST(OLD.`NUMERO PEDIDO` AS CHAR), '#', MD5(JSON_OBJECT('NUMERO PEDIDO', OLD.`NUMERO PEDIDO`, 'PRAZO DE PRODUCAO', OLD.`PRAZO DE PRODUCAO`, 'MODELO', OLD.`MODELO`, 'COD. COR', OLD.`COD. COR`, 'Column5', OLD.`Column5`, 'Reformer Excellence', OLD.`Reformer Excellence`, 'Reformer Torre', OLD.`Reformer Torre`, 'REFORMER X', OLD.`REFORMER X`, 'Cadilac Excelence', OLD.`Cadilac Excelence`, 'Step Chair Excelence', OLD.`Step Chair Excelence`, 'Lader Barrel Excelence', OLD.`Lader Barrel Excelence`, 'Wall Unit', OLD.`Wall Unit`, 'Caixa Mini', OLD.`Caixa Mini`, 'Caixa do Reformer', OLD.`Caixa do Reformer`, 'P. de Molas - B R I N D E', OLD.`P. de Molas - B R I N D E`, 'P. de Molas - C O M P L E T A', OLD.`P. de Molas - C O M P L E T A`, 'P. de Molas - P u s h T h r u', OLD.`P. de Molas - P u s h T h r u`, 'Caixa da Cadeira', OLD.`Caixa da Cadeira`, 'Prancha de Alongamento', OLD.`Prancha de Alongamento`, 'Column20', OLD.`Column20`, 'REF. CLASSICO ALUMINIO', OLD.`REF. CLASSICO ALUMINIO`, 'REF. CLASSICO TORRE', OLD.`REF. CLASSICO TORRE`, 'CAD. CLASSICO ALUMINIO', OLD.`CAD. CLASSICO ALUMINIO`, 'REF. CLASSICO TAUARI', OLD.`REF. CLASSICO TAUARI`, 'CAD. CLASSICO TAUARI', OLD.`CAD. CLASSICO TAUARI`, 'REFORMER HIBRIDO', OLD.`REFORMER HIBRIDO`, 'WUNDA CHAIR', OLD.`WUNDA CHAIR`, 'ELECTRIC CHAIR', OLD.`ELECTRIC CHAIR`, 'ARM CHAIR', OLD.`ARM CHAIR`, 'LADDER BARREL CLÁSS.', OLD.`LADDER BARREL CLÁSS.`, 'PEDI O POLE', OLD.`PEDI O POLE`, 'WALL UNIT CLÁSSICO', OLD.`WALL UNIT CLÁSSICO`, 'MAT CLÁSSICO', OLD.`MAT CLÁSSICO`, 'MAT PORTÁTIL', OLD.`MAT PORTÁTIL`, 'BENCH MAT', OLD.`BENCH MAT`, 'GUILHOTINA', OLD.`GUILHOTINA`, 'CAIXA DO REFORMER CLÁSSICA', OLD.`CAIXA DO REFORMER CLÁSSICA`, 'SPINE CORRECTOR', OLD.`SPINE CORRECTOR`, 'SMALL BARREL', OLD.`SMALL BARREL`, 'SUPORTE SPINE CORRECTOR', OLD.`SUPORTE SPINE CORRECTOR`, 'MINI EXTENSÃO MOVE FLOW', OLD.`MINI EXTENSÃO MOVE FLOW`, 'PLATAFORMA BARREL CLÁSSICO', OLD.`PLATAFORMA BARREL CLÁSSICO`, 'BARRA PUSH TRUE (BALANÇO CLASSICO)', OLD.`BARRA PUSH TRUE (BALANÇO CLASSICO)`, 'SPACER BOX', OLD.`SPACER BOX`, '2 x 4 (TWO BY FOUR)', OLD.`2 x 4 (TWO BY FOUR)`, 'KUNA BOARD', OLD.`KUNA BOARD`, 'TRAVESSEIRO BENCH MAT', OLD.`TRAVESSEIRO BENCH MAT`, 'TRAVESSEIRO RÉGUA', OLD.`TRAVESSEIRO RÉGUA`, 'TRAVESSEIRO 1/2 LUA', OLD.`TRAVESSEIRO 1/2 LUA`, 'TRAV. CILINDRICO', OLD.`TRAV. CILINDRICO`, 'TRAV. OMBREIRA (PAR)', OLD.`TRAV. OMBREIRA (PAR)`, 'TRAV. CABEC. 30 mm', OLD.`TRAV. CABEC. 30 mm`, 'TRAV. CABEC. 40 mm', OLD.`TRAV. CABEC. 40 mm`, 'CAPA PROT. BARREL CLÁSS.', OLD.`CAPA PROT. BARREL CLÁSS.`, 'SHEEPSKIN COVER', OLD.`SHEEPSKIN COVER`, 'BASTÃO ALUMÍNIO 1,5 M', OLD.`BASTÃO ALUMÍNIO 1,5 M`, 'PUXADOR DE ALUMINIO', OLD.`PUXADOR DE ALUMINIO`, 'ANEL DE PILATES ARCHIVE AÇO', OLD.`ANEL DE PILATES ARCHIVE AÇO`, 'MAGIC SQUARE', OLD.`MAGIC SQUARE`, 'FOOT CORREC. ALUM.', OLD.`FOOT CORREC. ALUM.`, 'BEAN BAG', OLD.`BEAN BAG`, 'BREATH A CIZER', OLD.`BREATH A CIZER`, 'NECK STRETCHER', OLD.`NECK STRETCHER`, 'HAND TENS O METER', OLD.`HAND TENS O METER`, 'TOE EXERCISER', OLD.`TOE EXERCISER`, 'AIR PLANE BOARD', OLD.`AIR PLANE BOARD`, 'FINGER EXERCISE', OLD.`FINGER EXERCISE`, 'PUSH UP DEVICE (PAR)', OLD.`PUSH UP DEVICE (PAR)`, 'MINI BARREL', OLD.`MINI BARREL`, 'MINI SPINE', OLD.`MINI SPINE`, 'Previsto', OLD.`Previsto`, 'Realizado', OLD.`Realizado`, 'prioridade', OLD.`prioridade`)))) <=> (CONCAT(CAST(NEW.`NUMERO PEDIDO` AS CHAR), '#', MD5(JSON_OBJECT('NUMERO PEDIDO', NEW.`NUMERO PEDIDO`, 'PRAZO DE PRODUCAO', NEW.`PRAZO DE PRODUCAO`, 'MODELO', NEW.`MODELO`, 'COD. COR', NEW.`COD. COR`, 'Column5', NEW.`Column5`, 'Reformer Excellence', NEW.`Reformer Excellence`, 'Reformer Torre', NEW.`Reformer Torre`, 'REFORMER X', NEW.`REFORMER X`, 'Cadilac Excelence', NEW.`Cadilac Excelence`, 'Step Chair Excelence', NEW.`Step Chair Excelence`, 'Lader Barrel Excelence', NEW.`Lader Barrel Excelence`, 'Wall Unit', NEW.`Wall Unit`, 'Caixa Mini', NEW.`Caixa Mini`, 'Caixa do Reformer', NEW.`Caixa do Reformer`, 'P. de Molas - B R I N D E', NEW.`P. de Molas - B R I N D E`, 'P. de Molas - C O M P L E T A', NEW.`P. de Molas - C O M P L E T A`, 'P. de Molas - P u s h T h r u', NEW.`P. de Molas - P u s h T h r u`, 'Caixa da Cadeira', NEW.`Caixa da Cadeira`, 'Prancha de Alongamento', NEW.`Prancha de Alongamento`, 'Column20', NEW.`Column20`, 'REF. CLASSICO ALUMINIO', NEW.`REF. CLASSICO ALUMINIO`, 'REF. CLASSICO TORRE', NEW.`REF. CLASSICO TORRE`, 'CAD. CLASSICO ALUMINIO', NEW.`CAD. CLASSICO ALUMINIO`, 'REF. CLASSICO TAUARI', NEW.`REF. CLASSICO TAUARI`, 'CAD. CLASSICO TAUARI', NEW.`CAD. CLASSICO TAUARI`, 'REFORMER HIBRIDO', NEW.`REFORMER HIBRIDO`, 'WUNDA CHAIR', NEW.`WUNDA CHAIR`, 'ELECTRIC CHAIR', NEW.`ELECTRIC CHAIR`, 'ARM CHAIR', NEW.`ARM CHAIR`, 'LADDER BARREL CLÁSS.', NEW.`LADDER BARREL CLÁSS.`, 'PEDI O POLE', NEW.`PEDI O POLE`, 'WALL UNIT CLÁSSICO', NEW.`WALL UNIT CLÁSSICO`, 'MAT CLÁSSICO', NEW.`MAT CLÁSSICO`, 'MAT PORTÁTIL', NEW.`MAT PORTÁTIL`, 'BENCH MAT', NEW.`BENCH MAT`, 'GUILHOTINA', NEW.`GUILHOTINA`, 'CAIXA DO REFORMER CLÁSSICA', NEW.`CAIXA DO REFORMER CLÁSSICA`, 'SPINE CORRECTOR', NEW.`SPINE CORRECTOR`, 'SMALL BARREL', NEW.`SMALL BARREL`, 'SUPORTE SPINE CORRECTOR', NEW.`SUPORTE SPINE CORRECTOR`, 'MINI EXTENSÃO MOVE FLOW', NEW.`MINI EXTENSÃO MOVE FLOW`, 'PLATAFORMA BARREL CLÁSSICO', NEW.`PLATAFORMA BARREL CLÁSSICO`, 'BARRA PUSH TRUE (BALANÇO CLASSICO)', NEW.`BARRA PUSH TRUE (BALANÇO CLASSICO)`, 'SPACER BOX', NEW.`SPACER BOX`, '2 x 4 (TWO BY FOUR)', NEW.`2 x 4 (TWO BY FOUR)`, 'KUNA BOARD', NEW.`KUNA BOARD`, 'TRAVESSEIRO BENCH MAT', NEW.`TRAVESSEIRO BENCH MAT`, 'TRAVESSEIRO RÉGUA', NEW.`TRAVESSEIRO RÉGUA`, 'TRAVESSEIRO 1/2 LUA', NEW.`TRAVESSEIRO 1/2 LUA`, 'TRAV. CILINDRICO', NEW.`TRAV. CILINDRICO`, 'TRAV. OMBREIRA (PAR)', NEW.`TRAV. OMBREIRA (PAR)`, 'TRAV. CABEC. 30 mm', NEW.`TRAV. CABEC. 30 mm`, 'TRAV. CABEC. 40 mm', NEW.`TRAV. CABEC. 40 mm`, 'CAPA PROT. BARREL CLÁSS.', NEW.`CAPA PROT. BARREL CLÁSS.`, 'SHEEPSKIN COVER', NEW.`SHEEPSKIN COVER`, 'BASTÃO ALUMÍNIO 1,5 M', NEW.`BASTÃO ALUMÍNIO 1,5 M`, 'PUXADOR DE ALUMINIO', NEW.`PUXADOR DE ALUMINIO`, 'ANEL DE PILATES ARCHIVE AÇO', NEW.`ANEL DE PILATES ARCHIVE AÇO`, 'MAGIC SQUARE', NEW.`MAGIC SQUARE`, 'FOOT CORREC. ALUM.', NEW.`FOOT CORREC. ALUM.`, 'BEAN BAG', NEW.`BEAN BAG`, 'BREATH A CIZER', NEW.`BREATH A CIZER`, 'NECK STRETCHER', NEW.`NECK STRETCHER`, 'HAND TENS O METER', NEW.`HAND TENS O METER`, 'TOE EXERCISER', NEW.`TOE EXERCISER`, 'AIR PLANE BOARD', NEW.`AIR PLANE BOARD`, 'FINGER EXERCISE', NEW.`FINGER EXERCISE`, 'PUSH UP DEVICE (PAR)', NEW.`PUSH UP DEVICE (PAR)`, 'MINI BARREL', NEW.`MINI BARREL`, 'MINI SPINE', NEW.`MINI SPINE`, 'Previsto', NEW.`Previsto`, 'Realizado', NEW.`Realizado`, 'prioridade', NEW.`prioridade`))))) THEN
                  INSERT INTO historico_quadro (ocorrido_utc, tabela, operacao, chave, numero_pedido, dados)
                  VALUES (UTC_TIMESTAMP(3), 'tabela_adaptada', 'D', CONCAT(CAST(OLD.`NUMERO PEDIDO` AS CHAR), '#', MD5(JSON_OBJECT('NUMERO PEDIDO', OLD.`NUMERO PEDIDO`, 'PRAZO DE PRODUCAO', OLD.`PRAZO DE PRODUCAO`, 'MODELO', OLD.`MODELO`, 'COD. COR', OLD.`COD. COR`, 'Column5', OLD.`Column5`, 'Reformer Excellence', OLD.`Reformer Excellence`, 'Reformer Torre', OLD.`Reformer Torre`, 'REFORMER X', OLD.`REFORMER X`, 'Cadilac Excelence', OLD.`Cadilac Excelence`, 'Step Chair Excelence', OLD.`Step Chair Excelence`, 'Lader Barrel Excelence', OLD.`Lader Barrel Excelence`, 'Wall Unit', OLD.`Wall Unit`, 'Caixa Mini', OLD.`Caixa Mini`, 'Caixa do Reformer', OLD.`Caixa do Reformer`, 'P. de Molas - B R I N D E', OLD.`P. de Molas - B R I N D E`, 'P. de Molas - C O M P L E T A', OLD.`P. de Molas - C O M P L E T A`, 'P. de Molas - P u s h T h r u', OLD.`P. de Molas - P u s h T h r u`, 'Caixa da Cadeira', OLD.`Caixa da Cadeira`, 'Prancha de Alongamento', OLD.`Prancha de Alongamento`, 'Column20', OLD.`Column20`, 'REF. CLASSICO ALUMINIO', OLD.`REF. CLASSICO ALUMINIO`, 'REF. CLASSICO TORRE', OLD.`REF. CLASSICO TORRE`, 'CAD. CLASSICO ALUMINIO', OLD.`CAD. CLASSICO ALUMINIO`, 'REF. CLASSICO TAUARI', OLD.`REF. CLASSICO TAUARI`, 'CAD. CLASSICO TAUARI', OLD.`CAD. CLASSICO TAUARI`, 'REFORMER HIBRIDO', OLD.`REFORMER HIBRIDO`, 'WUNDA CHAIR', OLD.`WUNDA CHAIR`, 'ELECTRIC CHAIR', OLD.`ELECTRIC CHAIR`, 'ARM CHAIR', OLD.`ARM CHAIR`, 'LADDER BARREL CLÁSS.', OLD.`LADDER BARREL CLÁSS.`, 'PEDI O POLE', OLD.`PEDI O POLE`, 'WALL UNIT CLÁSSICO', OLD.`WALL UNIT CLÁSSICO`, 'MAT CLÁSSICO', OLD.`MAT CLÁSSICO`, 'MAT PORTÁTIL', OLD.`MAT PORTÁTIL`, 'BENCH MAT', OLD.`BENCH MAT`, 'GUILHOTINA', OLD.`GUILHOTINA`, 'CAIXA DO REFORMER CLÁSSICA', OLD.`CAIXA DO REFORMER CLÁSSICA`, 'SPINE CORRECTOR', OLD.`SPINE CORRECTOR`, 'SMALL BARREL', OLD.`SMALL BARREL`, 'SUPORTE SPINE CORRECTOR', OLD.`SUPORTE SPINE CORRECTOR`, 'MINI EXTENSÃO MOVE FLOW', OLD.`MINI EXTENSÃO MOVE FLOW`, 'PLATAFORMA BARREL CLÁSSICO', OLD.`PLATAFORMA BARREL CLÁSSICO`, 'BARRA PUSH TRUE (BALANÇO CLASSICO)', OLD.`BARRA PUSH TRUE (BALANÇO CLASSICO)`, 'SPACER BOX', OLD.`SPACER BOX`, '2 x 4 (TWO BY FOUR)', OLD.`2 x 4 (TWO BY FOUR)`, 'KUNA BOARD', OLD.`KUNA BOARD`, 'TRAVESSEIRO BENCH MAT', OLD.`TRAVESSEIRO BENCH MAT`, 'TRAVESSEIRO RÉGUA', OLD.`TRAVESSEIRO RÉGUA`, 'TRAVESSEIRO 1/2 LUA', OLD.`TRAVESSEIRO 1/2 LUA`, 'TRAV. CILINDRICO', OLD.`TRAV. CILINDRICO`, 'TRAV. OMBREIRA (PAR)', OLD.`TRAV. OMBREIRA (PAR)`, 'TRAV. CABEC. 30 mm', OLD.`TRAV. CABEC. 30 mm`, 'TRAV. CABEC. 40 mm', OLD.`TRAV. CABEC. 40 mm`, 'CAPA PROT. BARREL CLÁSS.', OLD.`CAPA PROT. BARREL CLÁSS.`, 'SHEEPSKIN COVER', OLD.`SHEEPSKIN COVER`, 'BASTÃO ALUMÍNIO 1,5 M', OLD.`BASTÃO ALUMÍNIO 1,5 M`, 'PUXADOR DE ALUMINIO', OLD.`PUXADOR DE ALUMINIO`, 'ANEL DE PILATES ARCHIVE AÇO', OLD.`ANEL DE PILATES ARCHIVE AÇO`, 'MAGIC SQUARE', OLD.`MAGIC SQUARE`, 'FOOT CORREC. ALUM.', OLD.`FOOT CORREC. ALUM.`, 'BEAN BAG', OLD.`BEAN BAG`, 'BREATH A CIZER', OLD.`BREATH A CIZER`, 'NECK STRETCHER', OLD.`NECK STRETCHER`, 'HAND TENS O METER', OLD.`HAND TENS O METER`, 'TOE EXERCISER', OLD.`TOE EXERCISER`, 'AIR PLANE BOARD', OLD.`AIR PLANE BOARD`, 'FINGER EXERCISE', OLD.`FINGER EXERCISE`, 'PUSH UP DEVICE (PAR)', OLD.`PUSH UP DEVICE (PAR)`, 'MINI BARREL', OLD.`MINI BARREL`, 'MINI SPINE', OLD.`MINI SPINE`, 'Previsto', OLD.`Previsto`, 'Realizado', OLD.`Realizado`, 'prioridade', OLD.`prioridade`))), CAST(OLD.`NUMERO PEDIDO` AS CHAR), NULL);
                END IF;
                INSERT INTO historico_quadro (ocorrido_utc, tabela, operacao, chave, numero_pedido, dados)
                VALUES (UTC_TIMESTAMP(3), 'tabela_adaptada', 'U', CONCAT(CAST(NEW.`NUMERO PEDIDO` AS CHAR), '#', MD5(JSON_OBJECT('NUMERO PEDIDO', NEW.`NUMERO PEDIDO`, 'PRAZO DE PRODUCAO', NEW.`PRAZO DE PRODUCAO`, 'MODELO', NEW.`MODELO`, 'COD. COR', NEW.`COD. COR`, 'Column5', NEW.`Column5`, 'Reformer Excellence', NEW.`Reformer Excellence`, 'Reformer Torre', NEW.`Reformer Torre`, 'REFORMER X', NEW.`REFORMER X`, 'Cadilac Excelence', NEW.`Cadilac Excelence`, 'Step Chair Excelence', NEW.`Step Chair Excelence`, 'Lader Barrel Excelence', NEW.`Lader Barrel Excelence`, 'Wall Unit', NEW.`Wall Unit`, 'Caixa Mini', NEW.`Caixa Mini`, 'Caixa do Reformer', NEW.`Caixa do Reformer`, 'P. de Molas - B R I N D E', NEW.`P. de Molas - B R I N D E`, 'P. de Molas - C O M P L E T A', NEW.`P. de Molas - C O M P L E T A`, 'P. de Molas - P u s h T h r u', NEW.`P. de Molas - P u s h T h r u`, 'Caixa da Cadeira', NEW.`Caixa da Cadeira`, 'Prancha de Alongamento', NEW.`Prancha de Alongamento`, 'Column20', NEW.`Column20`, 'REF. CLASSICO ALUMINIO', NEW.`REF. CLASSICO ALUMINIO`, 'REF. CLASSICO TORRE', NEW.`REF. CLASSICO TORRE`, 'CAD. CLASSICO ALUMINIO', NEW.`CAD. CLASSICO ALUMINIO`, 'REF. CLASSICO TAUARI', NEW.`REF. CLASSICO TAUARI`, 'CAD. CLASSICO TAUARI', NEW.`CAD. CLASSICO TAUARI`, 'REFORMER HIBRIDO', NEW.`REFORMER HIBRIDO`, 'WUNDA CHAIR', NEW.`WUNDA CHAIR`, 'ELECTRIC CHAIR', NEW.`ELECTRIC CHAIR`, 'ARM CHAIR', NEW.`ARM CHAIR`, 'LADDER BARREL CLÁSS.', NEW.`LADDER BARREL CLÁSS.`, 'PEDI O POLE', NEW.`PEDI O POLE`, 'WALL UNIT CLÁSSICO', NEW.`WALL UNIT CLÁSSICO`, 'MAT CLÁSSICO', NEW.`MAT CLÁSSICO`, 'MAT PORTÁTIL', NEW.`MAT PORTÁTIL`, 'BENCH MAT', NEW.`BENCH MAT`, 'GUILHOTINA', NEW.`GUILHOTINA`, 'CAIXA DO REFORMER CLÁSSICA', NEW.`CAIXA DO REFORMER CLÁSSICA`, 'SPINE CORRECTOR', NEW.`SPINE CORRECTOR`, 'SMALL BARREL', NEW.`SMALL BARREL`, 'SUPORTE SPINE CORRECTOR', NEW.`SUPORTE SPINE CORRECTOR`, 'MINI EXTENSÃO MOVE FLOW', NEW.`MINI EXTENSÃO MOVE FLOW`, 'PLATAFORMA BARREL CLÁSSICO', NEW.`PLATAFORMA BARREL CLÁSSICO`, 'BARRA PUSH TRUE (BALANÇO CLASSICO)', NEW.`BARRA PUSH TRUE (BALANÇO CLASSICO)`, 'SPACER BOX', NEW.`SPACER BOX`, '2 x 4 (TWO BY FOUR)', NEW.`2 x 4 (TWO BY FOUR)`, 'KUNA BOARD', NEW.`KUNA BOARD`, 'TRAVESSEIRO BENCH MAT', NEW.`TRAVESSEIRO BENCH MAT`, 'TRAVESSEIRO RÉGUA', NEW.`TRAVESSEIRO RÉGUA`, 'TRAVESSEIRO 1/2 LUA', NEW.`TRAVESSEIRO 1/2 LUA`, 'TRAV. CILINDRICO', NEW.`TRAV. CILINDRICO`, 'TRAV. OMBREIRA (PAR)', NEW.`TRAV. OMBREIRA (PAR)`, 'TRAV. CABEC. 30 mm', NEW.`TRAV. CABEC. 30 mm`, 'TRAV. CABEC. 40 mm', NEW.`TRAV. CABEC. 40 mm`, 'CAPA PROT. BARREL CLÁSS.', NEW.`CAPA PROT. BARREL CLÁSS.`, 'SHEEPSKIN COVER', NEW.`SHEEPSKIN COVER`, 'BASTÃO ALUMÍNIO 1,5 M', NEW.`BASTÃO ALUMÍNIO 1,5 M`, 'PUXADOR DE ALUMINIO', NEW.`PUXADOR DE ALUMINIO`, 'ANEL DE PILATES ARCHIVE AÇO', NEW.`ANEL DE PILATES ARCHIVE AÇO`, 'MAGIC SQUARE', NEW.`MAGIC SQUARE`, 'FOOT CORREC. ALUM.', NEW.`FOOT CORREC. ALUM.`, 'BEAN BAG', NEW.`BEAN BAG`, 'BREATH A CIZER', NEW.`BREATH A CIZER`, 'NECK STRETCHER', NEW.`NECK STRETCHER`, 'HAND TENS O METER', NEW.`HAND TENS O METER`, 'TOE EXERCISER', NEW.`TOE EXERCISER`, 'AIR PLANE BOARD', NEW.`AIR PLANE BOARD`, 'FINGER EXERCISE', NEW.`FINGER EXERCISE`, 'PUSH UP DEVICE (PAR)', NEW.`PUSH UP DEVICE (PAR)`, 'MINI BARREL', NEW.`MINI BARREL`, 'MINI SPINE', NEW.`MINI SPINE`, 'Previsto', NEW.`Previsto`, 'Realizado', NEW.`Realizado`, 'prioridade', NEW.`prioridade`))), CAST(NEW.`NUMERO PEDIDO` AS CHAR), novo);
              END IF;
            END$$
DROP TRIGGER IF EXISTS `hq_tabela_adaptada_ad`$$
CREATE DEFINER=`u109029190_usr_GN6XeAZr`@`localhost` TRIGGER hq_tabela_adaptada_ad AFTER DELETE ON `tabela_adaptada` FOR EACH ROW
            INSERT INTO historico_quadro (ocorrido_utc, tabela, operacao, chave, numero_pedido, dados)
            VALUES (UTC_TIMESTAMP(3), 'tabela_adaptada', 'D', CONCAT(CAST(OLD.`NUMERO PEDIDO` AS CHAR), '#', MD5(JSON_OBJECT('NUMERO PEDIDO', OLD.`NUMERO PEDIDO`, 'PRAZO DE PRODUCAO', OLD.`PRAZO DE PRODUCAO`, 'MODELO', OLD.`MODELO`, 'COD. COR', OLD.`COD. COR`, 'Column5', OLD.`Column5`, 'Reformer Excellence', OLD.`Reformer Excellence`, 'Reformer Torre', OLD.`Reformer Torre`, 'REFORMER X', OLD.`REFORMER X`, 'Cadilac Excelence', OLD.`Cadilac Excelence`, 'Step Chair Excelence', OLD.`Step Chair Excelence`, 'Lader Barrel Excelence', OLD.`Lader Barrel Excelence`, 'Wall Unit', OLD.`Wall Unit`, 'Caixa Mini', OLD.`Caixa Mini`, 'Caixa do Reformer', OLD.`Caixa do Reformer`, 'P. de Molas - B R I N D E', OLD.`P. de Molas - B R I N D E`, 'P. de Molas - C O M P L E T A', OLD.`P. de Molas - C O M P L E T A`, 'P. de Molas - P u s h T h r u', OLD.`P. de Molas - P u s h T h r u`, 'Caixa da Cadeira', OLD.`Caixa da Cadeira`, 'Prancha de Alongamento', OLD.`Prancha de Alongamento`, 'Column20', OLD.`Column20`, 'REF. CLASSICO ALUMINIO', OLD.`REF. CLASSICO ALUMINIO`, 'REF. CLASSICO TORRE', OLD.`REF. CLASSICO TORRE`, 'CAD. CLASSICO ALUMINIO', OLD.`CAD. CLASSICO ALUMINIO`, 'REF. CLASSICO TAUARI', OLD.`REF. CLASSICO TAUARI`, 'CAD. CLASSICO TAUARI', OLD.`CAD. CLASSICO TAUARI`, 'REFORMER HIBRIDO', OLD.`REFORMER HIBRIDO`, 'WUNDA CHAIR', OLD.`WUNDA CHAIR`, 'ELECTRIC CHAIR', OLD.`ELECTRIC CHAIR`, 'ARM CHAIR', OLD.`ARM CHAIR`, 'LADDER BARREL CLÁSS.', OLD.`LADDER BARREL CLÁSS.`, 'PEDI O POLE', OLD.`PEDI O POLE`, 'WALL UNIT CLÁSSICO', OLD.`WALL UNIT CLÁSSICO`, 'MAT CLÁSSICO', OLD.`MAT CLÁSSICO`, 'MAT PORTÁTIL', OLD.`MAT PORTÁTIL`, 'BENCH MAT', OLD.`BENCH MAT`, 'GUILHOTINA', OLD.`GUILHOTINA`, 'CAIXA DO REFORMER CLÁSSICA', OLD.`CAIXA DO REFORMER CLÁSSICA`, 'SPINE CORRECTOR', OLD.`SPINE CORRECTOR`, 'SMALL BARREL', OLD.`SMALL BARREL`, 'SUPORTE SPINE CORRECTOR', OLD.`SUPORTE SPINE CORRECTOR`, 'MINI EXTENSÃO MOVE FLOW', OLD.`MINI EXTENSÃO MOVE FLOW`, 'PLATAFORMA BARREL CLÁSSICO', OLD.`PLATAFORMA BARREL CLÁSSICO`, 'BARRA PUSH TRUE (BALANÇO CLASSICO)', OLD.`BARRA PUSH TRUE (BALANÇO CLASSICO)`, 'SPACER BOX', OLD.`SPACER BOX`, '2 x 4 (TWO BY FOUR)', OLD.`2 x 4 (TWO BY FOUR)`, 'KUNA BOARD', OLD.`KUNA BOARD`, 'TRAVESSEIRO BENCH MAT', OLD.`TRAVESSEIRO BENCH MAT`, 'TRAVESSEIRO RÉGUA', OLD.`TRAVESSEIRO RÉGUA`, 'TRAVESSEIRO 1/2 LUA', OLD.`TRAVESSEIRO 1/2 LUA`, 'TRAV. CILINDRICO', OLD.`TRAV. CILINDRICO`, 'TRAV. OMBREIRA (PAR)', OLD.`TRAV. OMBREIRA (PAR)`, 'TRAV. CABEC. 30 mm', OLD.`TRAV. CABEC. 30 mm`, 'TRAV. CABEC. 40 mm', OLD.`TRAV. CABEC. 40 mm`, 'CAPA PROT. BARREL CLÁSS.', OLD.`CAPA PROT. BARREL CLÁSS.`, 'SHEEPSKIN COVER', OLD.`SHEEPSKIN COVER`, 'BASTÃO ALUMÍNIO 1,5 M', OLD.`BASTÃO ALUMÍNIO 1,5 M`, 'PUXADOR DE ALUMINIO', OLD.`PUXADOR DE ALUMINIO`, 'ANEL DE PILATES ARCHIVE AÇO', OLD.`ANEL DE PILATES ARCHIVE AÇO`, 'MAGIC SQUARE', OLD.`MAGIC SQUARE`, 'FOOT CORREC. ALUM.', OLD.`FOOT CORREC. ALUM.`, 'BEAN BAG', OLD.`BEAN BAG`, 'BREATH A CIZER', OLD.`BREATH A CIZER`, 'NECK STRETCHER', OLD.`NECK STRETCHER`, 'HAND TENS O METER', OLD.`HAND TENS O METER`, 'TOE EXERCISER', OLD.`TOE EXERCISER`, 'AIR PLANE BOARD', OLD.`AIR PLANE BOARD`, 'FINGER EXERCISE', OLD.`FINGER EXERCISE`, 'PUSH UP DEVICE (PAR)', OLD.`PUSH UP DEVICE (PAR)`, 'MINI BARREL', OLD.`MINI BARREL`, 'MINI SPINE', OLD.`MINI SPINE`, 'Previsto', OLD.`Previsto`, 'Realizado', OLD.`Realizado`, 'prioridade', OLD.`prioridade`))), CAST(OLD.`NUMERO PEDIDO` AS CHAR), NULL)$$
DELIMITER ;

