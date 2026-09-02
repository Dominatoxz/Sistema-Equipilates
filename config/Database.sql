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
