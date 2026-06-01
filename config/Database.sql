USE planilha_db;

CREATE TABLE IF NOT EXISTS itens_producao (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_pedido VARCHAR(50) NOT NULL,
    prazo_producao VARCHAR(255),
    equipamento VARCHAR(100) NOT NULL,
    posicao_no_pedido INT NOT NULL, 
    cor VARCHAR(100),
    status VARCHAR(50) DEFAULT 'Pendente',
    data_inicio DATETIME DEFAULT NULL,
    data_fim DATETIME DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS itens_os (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_pedido VARCHAR(50) NOT NULL,
    prazo_producao VARCHAR(255),
    equipamento VARCHAR(100) NOT NULL,
    posicao_no_pedido INT NOT NULL, 
    cor VARCHAR(100),
    status VARCHAR(50) DEFAULT 'Pendente',
    data_inicio DATETIME DEFAULT NULL,
    data_fim DATETIME DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS pedidos_prontos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_pedido VARCHAR(50) NOT NULL,
    prazo_producao VARCHAR(255),
	data_conclusao DATETIME DEFAULT CURRENT_TIMESTAMP,
    status_posvenda VARCHAR(50) DEFAULT 'Pós-venda'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS pedidos_expedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_pedido VARCHAR(50) NOT NULL,
    prazo_producao VARCHAR(255),
	data_conclusao DATETIME DEFAULT CURRENT_TIMESTAMP,
    status_posvenda VARCHAR(50) DEFAULT 'Pendente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    senha_hash VARCHAR(255) NOT NULL,
    nivel_acesso VARCHAR(20) DEFAULT 'operador',
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

DROP PROCEDURE IF EXISTS gerar_unidades_producao;

DELIMITER //

CREATE PROCEDURE gerar_unidades_producao() 
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE v_pedido VARCHAR(50);
    DECLARE v_qtd_reformer INT; 
    DECLARE v_qtd_torre INT;  
    DECLARE v_qtd_cadilac INT;
    DECLARE v_qtd_chair INT;
    DECLARE v_qtd_barrel INT;
    DECLARE v_qtd_wall INT;
    DECLARE v_qtd_mini INT;
    DECLARE v_qtd_caixa_ref INT;
    DECLARE v_qtd_pmb INT;
    DECLARE v_qtd_pmc INT;
    DECLARE v_qtd_pmp INT;
    DECLARE v_qtd_caixa_cadeira INT;
    DECLARE v_qtd_prancha INT;
    DECLARE v_cor_planilha VARCHAR(100);
    DECLARE v_prazo VARCHAR(100);
    DECLARE i INT;
    DECLARE v_os INT;

    DECLARE cur CURSOR FOR 
        SELECT 
            `NUMERO PEDIDO`, 
            `PRAZO DE PRODUÇÃO`,
            CAST(NULLIF(`Reformer Excellence`, '') AS UNSIGNED), 
            CAST(NULLIF(`Reformer Torre`, '') AS UNSIGNED), 
            CAST(NULLIF(`Cadilac Excelence`, '') AS UNSIGNED), 
            CAST(NULLIF(`Step Chair Excelence`, '') AS UNSIGNED), 
            CAST(NULLIF(`Lader Barrel Excelence`, '') AS UNSIGNED),
            CAST(NULLIF(`Wall Unit`, '') AS UNSIGNED),
            CAST(NULLIF(`Caixa Mini`, '') AS UNSIGNED),
            CAST(NULLIF(`Caixa do Reformer`, '') AS UNSIGNED),
            CAST(NULLIF(`P. de Molas - B R I N D E`, '') AS UNSIGNED),
            CAST(NULLIF(`P. de Molas - C O M P L E T A`, '') AS UNSIGNED),
            CAST(NULLIF(`P. de Molas - P u s h T h r u`, '') AS UNSIGNED),
            CAST(NULLIF(`Caixa da Cadeira`, '') AS UNSIGNED),
            CAST(NULLIF(`Prancha de Alongamento`, '') AS UNSIGNED),
            `COD. COR`
        FROM tabela_adaptada;
        

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

    OPEN cur;

    read_loop: LOOP
        FETCH cur INTO v_pedido, v_prazo, v_qtd_reformer, v_qtd_torre, v_qtd_cadilac, v_qtd_chair, v_qtd_barrel, v_qtd_wall,
        v_qtd_mini, v_qtd_caixa_ref, v_qtd_pmb, v_qtd_pmc, v_qtd_pmp, v_qtd_caixa_cadeira, v_qtd_prancha, v_cor_planilha;
        
        IF done THEN
            LEAVE read_loop;
        END IF;
        
        IF v_pedido = 'NUMERO PEDIDO' THEN
            ITERATE read_loop;
        END IF;
        
        IF LOWER(TRIM(v_pedido)) LIKE 'os%' OR LOWER(TRIM(v_pedido)) LIKE '%os%' THEN
			SET v_os = 1;
		ELSE
			SET v_os = 0;
		END IF;
        
	SET v_qtd_reformer = CAST(NULLIF(TRIM(v_qtd_reformer), '') AS UNSIGNED);
        IF v_qtd_reformer > 0 THEN
            SET i = 1;
            WHILE i <= v_qtd_reformer DO
                IF v_os = 1 THEN
                    INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Reformer Excellence', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. Reformer Excellence', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Carrinho Excellence', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. Carrinho Excellence', i, v_cor_planilha, 'Pendente');
                ELSE
                    INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Reformer Excellence', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. Reformer Excellence', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Carrinho Excellence', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. Carrinho Excellence', i, v_cor_planilha, 'Pendente');
                END IF;
                SET i = i + 1;
            END WHILE;
        END IF;
        
       SET v_qtd_torre = CAST(NULLIF(TRIM(v_qtd_torre), '') AS UNSIGNED);
        IF v_qtd_torre > 0 THEN
            SET i = 1;
            WHILE i <= v_qtd_torre DO
                IF v_os = 1 THEN
                    INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Reformer Torre', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. Reformer Torre', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Carrinho Torre', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. Carrinho Torre', i, v_cor_planilha, 'Pendente');
                ELSE
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Reformer Torre', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. Reformer Torre', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Carrinho Torre', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. Carrinho Torre', i, v_cor_planilha, 'Pendente');
                END IF;
                SET i = i + 1;
            END WHILE;
        END IF;
				
	SET v_qtd_cadilac = CAST(NULLIF(TRIM(v_qtd_cadilac), '')  AS UNSIGNED);
        IF v_qtd_cadilac > 0 THEN
            SET i = 1;
            WHILE i <= v_qtd_cadilac DO
				IF v_os = 1 THEN
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Cadilac Excelence', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb.Cadilac Excelence', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Gaiola Cadilac', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. Gaiola Cadilac', i, v_cor_planilha, 'Pendente');
				ELSE 
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Cadilac Excelence', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb.Cadilac Excelence', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Gaiola Cadilac', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. Gaiola Cadilac', i, v_cor_planilha, 'Pendente');
                END IF;
                SET i = i + 1;
            END WHILE;
        END IF;
	
        
		SET v_qtd_chair = CAST(NULLIF(TRIM(v_qtd_chair), '')AS UNSIGNED);
          IF v_qtd_chair > 0 THEN
            SET i = 1;
            WHILE i <= v_qtd_chair DO
				IF v_os = 1 THEN 
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Step Chair Excelence', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. Step Chair Excelence', i, v_cor_planilha, 'Pendente');
				ELSE 
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Step Chair Excelence', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. Step Chair Excelence', i, v_cor_planilha, 'Pendente');
                END IF;
                SET i = i + 1;
            END WHILE;
        END IF;
        
        SET v_qtd_barrel = CAST(NULLIF(TRIM(v_qtd_barrel),  '') AS UNSIGNED);
            IF v_qtd_barrel > 0 THEN
            SET i = 1;
            WHILE i <= v_qtd_barrel DO
				IF v_os = 1 THEN
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Lader Barrel Excelence', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. Lader Barrel Excelence', i, v_cor_planilha, 'Pendente');
                ELSE 
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Lader Barrel Excelence', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. Lader Barrel Excelence', i, v_cor_planilha, 'Pendente');
				END IF;
                SET i = i + 1;
            END WHILE;
        END IF;
        
        SET v_qtd_wall = CAST(NULLIF(TRIM(v_qtd_wall),  '') AS UNSIGNED);
            IF v_qtd_wall > 0 THEN
            SET i = 1;
            WHILE i <= v_qtd_wall DO
				IF v_os = 1 THEN
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Wall Unit', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. Wall Unit', i, v_cor_planilha, 'Pendente');
                ELSE 
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Wall Unit', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. Wall Unit', i, v_cor_planilha, 'Pendente');
				END IF;
                SET i = i + 1;
            END WHILE;
        END IF;
        
        SET v_qtd_mini = CAST(NULLIF(TRIM(v_qtd_mini),  '') AS UNSIGNED);
            IF v_qtd_mini > 0 THEN
            SET i = 1;
            WHILE i <= v_qtd_mini DO
				IF v_os = 1 THEN
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Caixa Mini', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. Caixa Mini', i, v_cor_planilha, 'Pendente');
                ELSE 
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Caixa Mini', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. Caixa Mini', i, v_cor_planilha, 'Pendente');
				END IF;
                SET i = i + 1;
            END WHILE;
        END IF;
        
        SET v_qtd_caixa_ref = CAST(NULLIF(TRIM(v_qtd_caixa_ref),  '') AS UNSIGNED);
            IF v_qtd_caixa_ref > 0 THEN
            SET i = 1;
            WHILE i <= v_qtd_caixa_ref DO
				IF v_os = 1 THEN
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Caixa do Reformer', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. Caixa do Reformer', i, v_cor_planilha, 'Pendente');
                ELSE 
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Caixa do Reformer', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. Caixa do Reformer', i, v_cor_planilha, 'Pendente');
				END IF;
                SET i = i + 1;
            END WHILE;
        END IF;
        
         SET v_qtd_pmb = CAST(NULLIF(TRIM(v_qtd_pmb),  '') AS UNSIGNED);
            IF v_qtd_pmb > 0 THEN
            SET i = 1;
            WHILE i <= v_qtd_pmb DO
				IF v_os = 1 THEN
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'P. de Molas - B R I N D E', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. P. de Molas - B R I N D E', i, v_cor_planilha, 'Pendente');
                ELSE 
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'P. de Molas - B R I N D E', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb.P. de Molas - B R I N D E', i, v_cor_planilha, 'Pendente');
				END IF;
                SET i = i + 1;
            END WHILE;
        END IF;
        
        SET v_qtd_pmc = CAST(NULLIF(TRIM(v_qtd_pmc),  '') AS UNSIGNED);
            IF v_qtd_pmc > 0 THEN
            SET i = 1;
            WHILE i <= v_qtd_pmc DO
				IF v_os = 1 THEN
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'P. de Molas - C O M P L E T A', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. P. de Molas - C O M P L E T A', i, v_cor_planilha, 'Pendente');
                ELSE 
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'P. de Molas - C O M P L E T A', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. P. de Molas - C O M P L E T A', i, v_cor_planilha, 'Pendente');
				END IF;
                SET i = i + 1;
            END WHILE;
        END IF;
        
        SET v_qtd_pmp = CAST(NULLIF(TRIM(v_qtd_pmp),  '') AS UNSIGNED);
            IF v_qtd_pmp > 0 THEN
            SET i = 1;
            WHILE i <= v_qtd_pmp DO
				IF v_os = 1 THEN
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'P. de Molas - P u s h T h r u', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. P. de Molas - P u s h T h r u', i, v_cor_planilha, 'Pendente');
                ELSE 
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'P. de Molas - P u s h T h r u', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. P. de Molas - P u s h T h r u', i, v_cor_planilha, 'Pendente');
				END IF;
                SET i = i + 1;
            END WHILE;
        END IF;
        
        SET v_qtd_caixa_cadeira = CAST(NULLIF(TRIM(v_qtd_caixa_cadeira),  '') AS UNSIGNED);
            IF v_qtd_caixa_cadeira > 0 THEN
            SET i = 1;
            WHILE i <= v_qtd_caixa_cadeira DO
				IF v_os = 1 THEN
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Caixa da Cadeira', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. Caixa da Cadeira', i, v_cor_planilha, 'Pendente');
                ELSE 
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Caixa da Cadeira', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. Caixa da Cadeira', i, v_cor_planilha, 'Pendente');
				END IF;
                SET i = i + 1;
            END WHILE;
        END IF;
        
        SET v_qtd_prancha = CAST(NULLIF(TRIM(v_qtd_prancha),  '') AS UNSIGNED);
            IF v_qtd_prancha > 0 THEN
            SET i = 1;
            WHILE i <= v_qtd_prancha DO
				IF v_os = 1 THEN
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Prancha de Alongamento', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. Prancha de Alongamento', i, v_cor_planilha, 'Pendente');
                ELSE 
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Prancha de Alongamento', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. Prancha de Alongamento', i, v_cor_planilha, 'Pendente');
				END IF;
                SET i = i + 1;
            END WHILE;
        END IF;
        
        

    END LOOP;

    CLOSE cur;
END //

DELIMITER ;


CALL gerar_unidades_producao();


DROP PROCEDURE IF EXISTS gerar_unidades_producao_classico;

DELIMITER //

CREATE PROCEDURE gerar_unidades_producao_classico() 
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE v_pedido VARCHAR(50);
    DECLARE v_qtd_ref_class_a INT; 
    DECLARE v_qtd_torre_class INT;
    DECLARE v_qtd_cadilac_class_a INT;
    DECLARE v_qtd_ref_class_t INT; 
    DECLARE v_qtd_cadilac_class_t INT;
	DECLARE v_qtd_ref_hibrido INT; 
    DECLARE v_qtd_wunda INT;
    DECLARE v_qtd_eletric INT;
    DECLARE v_qtd_arm INT;
    DECLARE v_qtd_barrel_class INT;
    DECLARE v_qtd_pedi_pole INT;
    DECLARE v_qtd_wall_class INT;
    DECLARE v_qtd_mat_class INT;
    DECLARE v_qtd_mat_port INT;
    DECLARE v_qtd_bench INT;
    DECLARE v_qtd_guilhotina INT;
    DECLARE v_qtd_caixa_ref_class INT;
    DECLARE v_qtd_spine INT;
    DECLARE v_qtd_small_barrel INT;
    DECLARE v_qtd_suporte_spine INT;
    DECLARE v_qtd_mini_ext_move INT;
    DECLARE v_qtd_plat_barrel_class INT;
    DECLARE v_qtd_barra_pt INT;
    DECLARE v_qtd_spacer INT;
    DECLARE v_qtd_two INT;
    DECLARE v_qtd_kuna INT;
    DECLARE v_qtd_travesseiro_bm INT;
    DECLARE v_qtd_travesseiro_r INT;
    DECLARE v_qtd_travesseiro_lua INT;
    DECLARE v_qtd_trav_cilindrico INT;
    DECLARE v_qtd_trav_ombreira INT;
    DECLARE v_qtd_trav_cabec3 INT;
    DECLARE v_qtd_trav_cabec4 INT;
    DECLARE v_qtd_prot_barrel INT;
    DECLARE v_qtd_sheepskin INT;
    DECLARE v_qtd_bastao_alumin INT;
    DECLARE v_qtd_puxador_alumin INT;
    DECLARE v_qtd_anel_pilates INT;
    DECLARE v_qtd_magic INT;
    DECLARE v_qtd_foot_correc INT;
    DECLARE v_qtd_bean_bag INT;
    DECLARE v_qtd_breath INT;
    DECLARE v_qtd_neck INT;
    DECLARE v_qtd_hand_tens INT;
    DECLARE v_qtd_toe INT;
    DECLARE v_qtd_air_plane INT;
    DECLARE v_qtd_finger_exc INT;
    DECLARE v_qtd_push_up INT;
    DECLARE v_qtd_mini_barrel INT;
    DECLARE v_qtd_mini_spine INT;
    DECLARE v_cor_planilha VARCHAR(100);
    DECLARE v_prazo VARCHAR(100);
    DECLARE i INT;
    DECLARE v_os INT;

    DECLARE cur CURSOR FOR 
        SELECT 
            `NUMERO PEDIDO`, 
            `PRAZO DE PRODUÇÃO`,
            CAST(NULLIF(`REF. CLASSICO ALUMINIO`, '') AS UNSIGNED), 
            CAST(NULLIF(`REF. CLASSICO TORRE`, '') AS UNSIGNED), 
            CAST(NULLIF(`CAD. CLASSICO ALUMINIO`, '') AS UNSIGNED), 
            CAST(NULLIF(`REF. CLASSICO TAUARI`, '') AS UNSIGNED), 
            CAST(NULLIF(`CAD. CLASSICO TAUARI`, '') AS UNSIGNED),
            CAST(NULLIF(`REFORMER HIBRIDO`, '') AS UNSIGNED),
            CAST(NULLIF(`WUNDA CHAIR`, '') AS UNSIGNED),
            CAST(NULLIF(`ELECTRIC CHAIR`, '') AS UNSIGNED),
            CAST(NULLIF(`ARM CHAIR`, '') AS UNSIGNED),
            CAST(NULLIF(`LADDER BARREL CLÁSS.`, '') AS UNSIGNED),
            CAST(NULLIF(`PEDI O POLE`, '') AS UNSIGNED),
            CAST(NULLIF(`WALL UNIT CLÁSSICO`, '') AS UNSIGNED),
            CAST(NULLIF(`MAT CLÁSSICO`, '') AS UNSIGNED),
            CAST(NULLIF(`MAT PORTÁTIL`, '') AS UNSIGNED),
            CAST(NULLIF(`BENCH MAT`, '') AS UNSIGNED),
            CAST(NULLIF(`GUILHOTINA`, '') AS UNSIGNED),
            CAST(NULLIF(`CAIXA DO REFORMER CLÁSSICA`, '') AS UNSIGNED),
            CAST(NULLIF(`SPINE CORRECTOR`, '') AS UNSIGNED),
            CAST(NULLIF(`SMALL BARREL`, '') AS UNSIGNED),
            CAST(NULLIF(`SUPORTE SPINE CORRECTOR`, '') AS UNSIGNED),
            CAST(NULLIF(`MINI EXTENSÃO MOVE FLOW`, '') AS UNSIGNED),
            CAST(NULLIF(`PLATAFORMA BARREL CLÁSSICO`, '') AS UNSIGNED),
            CAST(NULLIF(`BARRA PUSH TRUE (BALANÇO CLASSICO)`, '') AS UNSIGNED),
            CAST(NULLIF(`SPACER BOX`, '') AS UNSIGNED),
            CAST(NULLIF(`2 x 4 (TWO BY FOUR)`, '') AS UNSIGNED),
            CAST(NULLIF(`KUNA BOARD`, '') AS UNSIGNED),
            CAST(NULLIF(`TRAVESSEIRO BENCH MAT`, '') AS UNSIGNED),
            CAST(NULLIF(`TRAVESSEIRO RÉGUA`, '') AS UNSIGNED),
            CAST(NULLIF(`TRAVESSEIRO 1/2 LUA`, '') AS UNSIGNED),
            CAST(NULLIF(`TRAV. CILINDRICO`, '') AS UNSIGNED),
            CAST(NULLIF(`TRAV. OMBREIRA (PAR)`, '') AS UNSIGNED),
            CAST(NULLIF(`TRAV. CABEC. 30 mm`, '') AS UNSIGNED),
            CAST(NULLIF(`TRAV. CABEC. 40 mm`, '') AS UNSIGNED),
            CAST(NULLIF(`CAPA PROT. BARREL CLÁSS.`, '') AS UNSIGNED),
            CAST(NULLIF(`SHEEPSKIN COVER`, '') AS UNSIGNED),
            CAST(NULLIF(`BASTÃO ALUMÍNIO 1,5 M`, '') AS UNSIGNED),
            CAST(NULLIF(`PUXADOR DE ALUMINIO`, '') AS UNSIGNED),
            CAST(NULLIF(`ANEL DE PILATES ARCHIVE AÇO`, '') AS UNSIGNED),
            CAST(NULLIF(`MAGIC SQUARE`, '') AS UNSIGNED),
            CAST(NULLIF(`FOOT CORREC. ALUM.`, '') AS UNSIGNED),
            CAST(NULLIF(`BEAN BAG`, '') AS UNSIGNED),
            CAST(NULLIF(`BREATH A CIZER`, '') AS UNSIGNED),
            CAST(NULLIF(`NECK STRETCHER`, '') AS UNSIGNED),
            CAST(NULLIF(`HAND TENS O METER`, '') AS UNSIGNED),
            CAST(NULLIF(`TOE EXERCISER`, '') AS UNSIGNED),
            CAST(NULLIF(`AIR PLANE BOARD`, '') AS UNSIGNED),
            CAST(NULLIF(`FINGER EXERCISE`, '') AS UNSIGNED),
            CAST(NULLIF(`PUSH UP DEVICE (PAR)`, '') AS UNSIGNED),
            CAST(NULLIF(`MINI BARREL`, '') AS UNSIGNED),
            CAST(NULLIF(`MINI SPINE`, '') AS UNSIGNED),
            `COD. COR`
        FROM tabela_adaptada;
        

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

    OPEN cur;

    read_loop: LOOP
        FETCH cur INTO v_pedido, v_prazo, v_qtd_ref_class_a, v_qtd_torre_class, v_qtd_cadilac_class_a, v_qtd_ref_class_t, v_qtd_cadilac_class_t, v_qtd_ref_hibrido, 
        v_qtd_wunda, v_qtd_eletric, v_qtd_arm, v_qtd_barrel_class, v_qtd_pedi_pole, v_qtd_wall_class, v_qtd_mat_class, v_qtd_mat_port,
        v_qtd_bench, v_qtd_guilhotina, v_qtd_caixa_ref_class, v_qtd_spine, v_qtd_small_barrel, v_qtd_suporte_spine, v_qtd_mini_ext_move, v_qtd_plat_barrel_class,
        v_qtd_barra_pt, v_qtd_spacer, v_qtd_two, v_qtd_kuna, v_qtd_travesseiro_bm, v_qtd_travesseiro_r, v_qtd_travesseiro_lua, v_qtd_trav_cilindrico, v_qtd_trav_ombreira, v_qtd_trav_cabec3,
        v_qtd_trav_cabec4, v_qtd_prot_barrel, v_qtd_sheepskin, v_qtd_bastao_alumin, v_qtd_puxador_alumin, v_qtd_anel_pilates, v_qtd_magic, v_qtd_foot_correc, v_qtd_bean_bag, v_qtd_breath, v_qtd_neck,
        v_qtd_hand_tens, v_qtd_toe, v_qtd_air_plane, v_qtd_finger_exc, v_qtd_push_up, v_qtd_mini_barrel, v_qtd_mini_spine, v_cor_planilha;
        
        IF done THEN
            LEAVE read_loop;
        END IF;
        
        IF v_pedido = 'NUMERO PEDIDO' THEN
            ITERATE read_loop;
        END IF;
        
        IF LOWER(TRIM(v_pedido)) LIKE 'os%' OR LOWER(TRIM(v_pedido)) LIKE '%os%' THEN
			SET v_os = 1;
		ELSE
			SET v_os = 0;
		END IF;
        
	SET v_qtd_ref_class_a = CAST(NULLIF(TRIM(v_qtd_ref_class_a), '') AS UNSIGNED);
        IF v_qtd_ref_class_a > 0 THEN
            SET i = 1;
            WHILE i <= v_qtd_ref_class_a DO
                IF v_os = 1 THEN
                    INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'REF. CLASSICO ALUMINIO', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. REF. CLASSICO ALUMINIO', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'CARRINHO CLASSICO', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. CARRINHO CLASSICO', i, v_cor_planilha, 'Pendente');
                ELSE
                    INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'REF. CLASSICO ALUMINIO', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. REF. CLASSICO ALUMINIO', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'CARRINHO CLASSICO', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. CARRINHO CLASSICO', i, v_cor_planilha, 'Pendente');
                END IF;
                SET i = i + 1;
            END WHILE;
        END IF;
        
	SET v_qtd_torre_class = CAST(NULLIF(TRIM(v_qtd_torre_class), '') AS UNSIGNED);
        IF v_qtd_torre_class > 0 THEN
            SET i = 1;
            WHILE i <= v_qtd_torre_class DO
                IF v_os = 1 THEN
                    INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'REF. CLASSICO TORRE', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. REF. CLASSICO TORRE', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'CARRINHO CLASSICO TORRE', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. CARRINHO CLASSICO TORRE', i, v_cor_planilha, 'Pendente');
                ELSE
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'REF. CLASSICO TORRE', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. REF. CLASSICO TORRE', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'CARRINHO CLASSICO TORRE', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. CARRINHO CLASSICO TORRE', i, v_cor_planilha, 'Pendente');
                END IF;
                SET i = i + 1;
            END WHILE;
        END IF;
        
	SET v_qtd_cadilac_class_a = CAST(NULLIF(TRIM(v_qtd_cadilac_class_a), '') AS UNSIGNED);
        IF v_qtd_cadilac_class_a > 0 THEN
            SET i = 1;
            WHILE i <= v_qtd_cadilac_class_a DO
                IF v_os = 1 THEN
                    INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'CAD. CLASSICO ALUMINIO', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. CAD. CLASSICO ALUMINIO', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'GAIOLA CLASSICO', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. GAIOLA CLASSICO', i, v_cor_planilha, 'Pendente');
                ELSE
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'CAD. CLASSICO ALUMINIO', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. CAD. CLASSICO ALUMINIO', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'GAIOLA CLASSICO', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. GAIOLA CLASSICO', i, v_cor_planilha, 'Pendente');
                END IF;
                SET i = i + 1;
            END WHILE;
        END IF;
        
	SET v_qtd_ref_class_t = CAST(NULLIF(TRIM(v_qtd_ref_class_t), '') AS UNSIGNED);
        IF v_qtd_ref_class_t > 0 THEN
            SET i = 1;
            WHILE i <= v_qtd_ref_class_t DO
                IF v_os = 1 THEN
                    INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'REF. CLASSICO TAUARI', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. REF. CLASSICO TAUARI', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'CARRINHO CLASSICO TAUARI', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. CARRINHO CLASSICO TAUARI', i, v_cor_planilha, 'Pendente');
                ELSE
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'REF. CLASSICO TAUARI', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. REF. CLASSICO TAUARI', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'CARRINHO CLASSICO TAUARI', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. CARRINHO CLASSICO TAUARI', i, v_cor_planilha, 'Pendente');
                END IF;
                SET i = i + 1;
            END WHILE;
        END IF;
				
	SET v_qtd_cadilac_class_t = CAST(NULLIF(TRIM(v_qtd_cadilac_class_t), '')  AS UNSIGNED);
        IF v_qtd_cadilac_class_t > 0 THEN
            SET i = 1;
            WHILE i <= v_qtd_cadilac_class_t DO
				IF v_os = 1 THEN
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'CAD. CLASSICO TAUARI', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. CAD. CLASSICO TAUARI', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'GAIOLA CADILCAC TAUARI', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. GAIOLA CADILCAC TAUARI', i, v_cor_planilha, 'Pendente');
				ELSE 
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'CAD. CLASSICO TAUARI', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. CAD. CLASSICO TAUARI', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'GAIOLA CADILCAC TAUARI', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. GAIOLA CADILCAC TAUARI', i, v_cor_planilha, 'Pendente');
                END IF;
                SET i = i + 1;
            END WHILE;
        END IF;
        
	SET v_qtd_ref_hibrido = CAST(NULLIF(TRIM(v_qtd_ref_hibrido), '') AS UNSIGNED);
        IF v_qtd_ref_hibrido > 0 THEN
            SET i = 1;
            WHILE i <= v_qtd_ref_hibrido DO
                IF v_os = 1 THEN
                    INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'REFORMER HIBRIDO', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. REFORMER HIBRIDO', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'CARRINHO CLASSICO HIBRIDO', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. CARRINHO CLASSICO HIBRIDO', i, v_cor_planilha, 'Pendente');
                ELSE
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'REFORMER HIBRIDO', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. REFORMER HIBRIDO', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'CARRINHO CLASSICO HIBRIDO', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. CARRINHO CLASSICO HIBRIDO', i, v_cor_planilha, 'Pendente');
                END IF;
                SET i = i + 1;
            END WHILE;
        END IF;
	
        
	SET v_qtd_wunda = CAST(NULLIF(TRIM(v_qtd_wunda), '')AS UNSIGNED);
          IF v_qtd_wunda > 0 THEN
            SET i = 1;
            WHILE i <= v_qtd_wunda DO
				IF v_os = 1 THEN 
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'WUNDA CHAIR', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. WUNDA CHAIR', i, v_cor_planilha, 'Pendente');
				ELSE 
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'WUNDA CHAIR', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. WUNDA CHAIR', i, v_cor_planilha, 'Pendente');
                END IF;
                SET i = i + 1;
            END WHILE;
        END IF;
        
	SET v_qtd_eletric = CAST(NULLIF(TRIM(v_qtd_eletric),  '') AS UNSIGNED);
            IF v_qtd_eletric > 0 THEN
            SET i = 1;
            WHILE i <= v_qtd_eletric DO
				IF v_os = 1 THEN
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'ELECTRIC CHAIR', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. ELECTRIC CHAIR', i, v_cor_planilha, 'Pendente');
                ELSE 
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'ELECTRIC CHAIR', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. ELECTRIC CHAIR', i, v_cor_planilha, 'Pendente');
				END IF;
                SET i = i + 1;
            END WHILE;
        END IF;
        
	SET v_qtd_arm = CAST(NULLIF(TRIM(v_qtd_arm),  '') AS UNSIGNED);
            IF v_qtd_arm > 0 THEN
            SET i = 1;
            WHILE i <= v_qtd_arm DO
				IF v_os = 1 THEN
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'ARM CHAIR', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. ARM CHAIR', i, v_cor_planilha, 'Pendente');
                ELSE 
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'ARM CHAIR', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. ARM CHAIR', i, v_cor_planilha, 'Pendente');
				END IF;
                SET i = i + 1;
            END WHILE;
        END IF;
        
        SET v_qtd_barrel_class = CAST(NULLIF(TRIM(v_qtd_barrel_class),  '') AS UNSIGNED);
            IF v_qtd_barrel_class > 0 THEN
            SET i = 1;
            WHILE i <= v_qtd_barrel_class DO
				IF v_os = 1 THEN
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'LADDER BARREL CLÁSS.', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. LADDER BARREL CLÁSS.', i, v_cor_planilha, 'Pendente');
                ELSE 
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'LADDER BARREL CLÁSS.', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. LADDER BARREL CLÁSS.', i, v_cor_planilha, 'Pendente');
				END IF;
                SET i = i + 1;
            END WHILE;
        END IF;
        
        SET v_qtd_pedi_pole = CAST(NULLIF(TRIM(v_qtd_pedi_pole),  '') AS UNSIGNED);
            IF v_qtd_pedi_pole > 0 THEN
            SET i = 1;
            WHILE i <= v_qtd_pedi_pole DO
				IF v_os = 1 THEN
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'PEDI O POLE', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. PEDI O POLE', i, v_cor_planilha, 'Pendente');
                ELSE 
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'PEDI O POLE', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. PEDI O POLE', i, v_cor_planilha, 'Pendente');
				END IF;
                SET i = i + 1;
            END WHILE;
        END IF;
        
         SET v_qtd_wall_class = CAST(NULLIF(TRIM(v_qtd_wall_class),  '') AS UNSIGNED);
            IF v_qtd_wall_class > 0 THEN
            SET i = 1;
            WHILE i <= v_qtd_wall_class DO
				IF v_os = 1 THEN
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'WALL UNIT CLÁSSICO', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. WALL UNIT CLÁSSICO', i, v_cor_planilha, 'Pendente');
                ELSE 
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'WALL UNIT CLÁSSICO', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. WALL UNIT CLÁSSICO', i, v_cor_planilha, 'Pendente');
				END IF;
                SET i = i + 1;
            END WHILE;
        END IF;
        
        SET v_qtd_mat_class = CAST(NULLIF(TRIM(v_qtd_mat_class),  '') AS UNSIGNED);
            IF v_qtd_mat_class > 0 THEN
            SET i = 1;
            WHILE i <= v_qtd_mat_class DO
				IF v_os = 1 THEN
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'MAT CLÁSSICO', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. MAT CLÁSSICO', i, v_cor_planilha, 'Pendente');
                ELSE 
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'MAT CLÁSSICO', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. MAT CLÁSSICO', i, v_cor_planilha, 'Pendente');
				END IF;
                SET i = i + 1;
            END WHILE;
        END IF;
        
        SET v_qtd_mat_port = CAST(NULLIF(TRIM(v_qtd_mat_port),  '') AS UNSIGNED);
            IF v_qtd_mat_port > 0 THEN
            SET i = 1;
            WHILE i <= v_qtd_mat_port DO
				IF v_os = 1 THEN
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'MAT PORTÁTIL', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. MAT PORTÁTIL', i, v_cor_planilha, 'Pendente');
                ELSE 
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'MAT PORTÁTIL', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. MAT PORTÁTIL', i, v_cor_planilha, 'Pendente');
				END IF;
                SET i = i + 1;
            END WHILE;
        END IF;
        
        SET v_qtd_bench = CAST(NULLIF(TRIM(v_qtd_bench),  '') AS UNSIGNED);
            IF v_qtd_bench > 0 THEN
            SET i = 1;
            WHILE i <= v_qtd_bench DO
				IF v_os = 1 THEN
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'BENCH MAT', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. BENCH MAT', i, v_cor_planilha, 'Pendente');
                ELSE 
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'BENCH MAT', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. BENCH MAT', i, v_cor_planilha, 'Pendente');
				END IF;
                SET i = i + 1;
            END WHILE;
        END IF;
        
        SET v_qtd_guilhotina = CAST(NULLIF(TRIM(v_qtd_guilhotina),  '') AS UNSIGNED);
            IF v_qtd_guilhotina > 0 THEN
            SET i = 1;
            WHILE i <= v_qtd_guilhotina DO
				IF v_os = 1 THEN
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'GUILHOTINA', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. GUILHOTINA', i, v_cor_planilha, 'Pendente');
                ELSE 
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'GUILHOTINA', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. GUILHOTINA', i, v_cor_planilha, 'Pendente');
				END IF;
                SET i = i + 1;
            END WHILE;
        END IF;
        
        SET v_qtd_caixa_ref_class = CAST(NULLIF(TRIM(v_qtd_caixa_ref_class),  '') AS UNSIGNED);
            IF v_qtd_caixa_ref_class > 0 THEN
            SET i = 1;
            WHILE i <= v_qtd_caixa_ref_class DO
				IF v_os = 1 THEN
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'CAIXA DO REFORMER CLÁSSICA', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. CAIXA DO REFORMER CLÁSSICA', i, v_cor_planilha, 'Pendente');
                ELSE 
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'CAIXA DO REFORMER CLÁSSICA', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. CAIXA DO REFORMER CLÁSSICA', i, v_cor_planilha, 'Pendente');
				END IF;
                SET i = i + 1;
            END WHILE;
        END IF;
        
        SET v_qtd_spine = CAST(NULLIF(TRIM(v_qtd_spine),  '') AS UNSIGNED);
            IF v_qtd_spine > 0 THEN
            SET i = 1;
            WHILE i <= v_qtd_spine DO
				IF v_os = 1 THEN
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'SPINE CORRECTOR', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. SPINE CORRECTOR', i, v_cor_planilha, 'Pendente');
                ELSE 
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'SPINE CORRECTOR', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. SPINE CORRECTOR', i, v_cor_planilha, 'Pendente');
				END IF;
                SET i = i + 1;
            END WHILE;
        END IF;
        
        SET v_qtd_small_barrel = CAST(NULLIF(TRIM(v_qtd_small_barrel),  '') AS UNSIGNED);
            IF v_qtd_small_barrel > 0 THEN
            SET i = 1;
            WHILE i <= v_qtd_small_barrel DO
				IF v_os = 1 THEN
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'SMALL BARREL', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. SMALL BARREL', i, v_cor_planilha, 'Pendente');
                ELSE 
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'SMALL BARREL', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. SMALL BARREL', i, v_cor_planilha, 'Pendente');
				END IF;
                SET i = i + 1;
            END WHILE;
        END IF;
        
        SET v_qtd_suporte_spine = CAST(NULLIF(TRIM(v_qtd_suporte_spine),  '') AS UNSIGNED);
            IF v_qtd_suporte_spine > 0 THEN
            SET i = 1;
            WHILE i <= v_qtd_suporte_spine DO
				IF v_os = 1 THEN
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'SUPORTE SPINE CORRECTOR', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. SUPORTE SPINE CORRECTOR', i, v_cor_planilha, 'Pendente');
                ELSE 
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'SUPORTE SPINE CORRECTOR', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. SUPORTE SPINE CORRECTOR', i, v_cor_planilha, 'Pendente');
				END IF;
                SET i = i + 1;
            END WHILE;
        END IF;
        
        SET v_qtd_mini_ext_move = CAST(NULLIF(TRIM(v_qtd_mini_ext_move),  '') AS UNSIGNED);
            IF v_qtd_mini_ext_move > 0 THEN
            SET i = 1;
            WHILE i <= v_qtd_mini_ext_move DO
				IF v_os = 1 THEN
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'MINI EXTENSÃO MOVE FLOW', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. MINI EXTENSÃO MOVE FLOW', i, v_cor_planilha, 'Pendente');
                ELSE 
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'MINI EXTENSÃO MOVE FLOW', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. MINI EXTENSÃO MOVE FLOW', i, v_cor_planilha, 'Pendente');
				END IF;
                SET i = i + 1;
            END WHILE;
        END IF;
        
        SET v_qtd_plat_barrel_class = CAST(NULLIF(TRIM(v_qtd_plat_barrel_class),  '') AS UNSIGNED);
            IF v_qtd_plat_barrel_class > 0 THEN
            SET i = 1;
            WHILE i <= v_qtd_plat_barrel_class DO
				IF v_os = 1 THEN
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'PLATAFORMA BARREL CLÁSSICO', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. PLATAFORMA BARREL CLÁSSICO', i, v_cor_planilha, 'Pendente');
                ELSE 
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'PLATAFORMA BARREL CLÁSSICO', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. PLATAFORMA BARREL CLÁSSICO', i, v_cor_planilha, 'Pendente');
				END IF;
                SET i = i + 1;
            END WHILE;
        END IF;
        
        SET v_qtd_barra_pt = CAST(NULLIF(TRIM(v_qtd_barra_pt),  '') AS UNSIGNED);
            IF v_qtd_barra_pt > 0 THEN
            SET i = 1;
            WHILE i <= v_qtd_barra_pt DO
				IF v_os = 1 THEN
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'BARRA PUSH TRUE (BALANÇO CLASSICO)', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. BARRA PUSH TRUE (BALANÇO CLASSICO)', i, v_cor_planilha, 'Pendente');
                ELSE 
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'BARRA PUSH TRUE (BALANÇO CLASSICO)', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. BARRA PUSH TRUE (BALANÇO CLASSICO)', i, v_cor_planilha, 'Pendente');
				END IF;
                SET i = i + 1;
            END WHILE;
        END IF;
        
        SET v_qtd_spacer = CAST(NULLIF(TRIM(v_qtd_spacer),  '') AS UNSIGNED);
            IF v_qtd_spacer > 0 THEN
            SET i = 1;
            WHILE i <= v_qtd_spacer DO
				IF v_os = 1 THEN
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'SPACER BOX', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. SPACER BOX', i, v_cor_planilha, 'Pendente');
                ELSE 
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'SPACER BOX', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. SPACER BOX', i, v_cor_planilha, 'Pendente');
				END IF;
                SET i = i + 1;
            END WHILE;
        END IF;
        
        SET v_qtd_two = CAST(NULLIF(TRIM(v_qtd_two),  '') AS UNSIGNED);
            IF v_qtd_two > 0 THEN
            SET i = 1;
            WHILE i <= v_qtd_two DO
				IF v_os = 1 THEN
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, '2 x 4 (TWO BY FOUR)', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. 2 x 4 (TWO BY FOUR)', i, v_cor_planilha, 'Pendente');
                ELSE 
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, '2 x 4 (TWO BY FOUR)', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. 2 x 4 (TWO BY FOUR)', i, v_cor_planilha, 'Pendente');
				END IF;
                SET i = i + 1;
            END WHILE;
        END IF;
        
        SET v_qtd_kuna = CAST(NULLIF(TRIM(v_qtd_kuna),  '') AS UNSIGNED);
            IF v_qtd_kuna > 0 THEN
            SET i = 1;
            WHILE i <= v_qtd_kuna DO
				IF v_os = 1 THEN
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'KUNA BOARD', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. KUNA BOARD', i, v_cor_planilha, 'Pendente');
                ELSE 
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'KUNA BOARD', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. KUNA BOARD', i, v_cor_planilha, 'Pendente');
				END IF;
                SET i = i + 1;
            END WHILE;
        END IF;
        
        SET v_qtd_travesseiro_bm = CAST(NULLIF(TRIM(v_qtd_travesseiro_bm),  '') AS UNSIGNED);
            IF v_qtd_travesseiro_bm > 0 THEN
            SET i = 1;
            WHILE i <= v_qtd_travesseiro_bm DO
				IF v_os = 1 THEN
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'TRAVESSEIRO BENCH MAT', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. TRAVESSEIRO BENCH MAT', i, v_cor_planilha, 'Pendente');
                ELSE 
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'TRAVESSEIRO BENCH MAT', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. TRAVESSEIRO BENCH MAT', i, v_cor_planilha, 'Pendente');
				END IF;
                SET i = i + 1;
            END WHILE;
        END IF;
        
        SET v_qtd_travesseiro_r = CAST(NULLIF(TRIM(v_qtd_travesseiro_r),  '') AS UNSIGNED);
            IF v_qtd_travesseiro_r > 0 THEN
            SET i = 1;
            WHILE i <= v_qtd_travesseiro_r DO
				IF v_os = 1 THEN
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'TRAVESSEIRO RÉGUA', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. TRAVESSEIRO RÉGUA', i, v_cor_planilha, 'Pendente');
                ELSE 
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'TRAVESSEIRO RÉGUA', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. TRAVESSEIRO RÉGUA', i, v_cor_planilha, 'Pendente');
				END IF;
                SET i = i + 1;
            END WHILE;
        END IF;
        
        SET v_qtd_travesseiro_lua = CAST(NULLIF(TRIM(v_qtd_travesseiro_lua),  '') AS UNSIGNED);
            IF v_qtd_travesseiro_lua > 0 THEN
            SET i = 1;
            WHILE i <= v_qtd_travesseiro_lua DO
				IF v_os = 1 THEN
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'TRAVESSEIRO 1/2 LUA', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. TRAVESSEIRO 1/2 LUA', i, v_cor_planilha, 'Pendente');
                ELSE 
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'TRAVESSEIRO 1/2 LUA', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. TRAVESSEIRO 1/2 LUA', i, v_cor_planilha, 'Pendente');
				END IF;
                SET i = i + 1;
            END WHILE;
        END IF;
        
        SET v_qtd_trav_cilindrico = CAST(NULLIF(TRIM(v_qtd_trav_cilindrico),  '') AS UNSIGNED);
            IF v_qtd_trav_cilindrico > 0 THEN
            SET i = 1;
            WHILE i <= v_qtd_trav_cilindrico DO
				IF v_os = 1 THEN
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'TRAV. CILINDRICO', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. TRAV. CILINDRICO', i, v_cor_planilha, 'Pendente');
                ELSE 
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'TRAV. CILINDRICO', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. TRAV. CILINDRICO', i, v_cor_planilha, 'Pendente');
				END IF;
                SET i = i + 1;
            END WHILE;
        END IF;
        
        SET v_qtd_trav_ombreira = CAST(NULLIF(TRIM(v_qtd_trav_ombreira),  '') AS UNSIGNED);
            IF v_qtd_trav_ombreira > 0 THEN
            SET i = 1;
            WHILE i <= v_qtd_trav_ombreira DO
				IF v_os = 1 THEN
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'TRAV. OMBREIRA (PAR)', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. TRAV. OMBREIRA (PAR)', i, v_cor_planilha, 'Pendente');
                ELSE 
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'TRAV. OMBREIRA (PAR)', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. TRAV. OMBREIRA (PAR)', i, v_cor_planilha, 'Pendente');
				END IF;
                SET i = i + 1;
            END WHILE;
        END IF;
        
        SET v_qtd_trav_cabec3 = CAST(NULLIF(TRIM(v_qtd_trav_cabec3),  '') AS UNSIGNED);
            IF v_qtd_trav_cabec3 > 0 THEN
            SET i = 1;
            WHILE i <= v_qtd_trav_cabec3 DO
				IF v_os = 1 THEN
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'TRAV. CABEC. 30 mm', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. TRAV. CABEC. 30 mm', i, v_cor_planilha, 'Pendente');
                ELSE 
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'TRAV. CABEC. 30 mm', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. TRAV. CABEC. 30 mm', i, v_cor_planilha, 'Pendente');
				END IF;
                SET i = i + 1;
            END WHILE;
        END IF;
        
        SET v_qtd_trav_cabec4 = CAST(NULLIF(TRIM(v_qtd_trav_cabec4),  '') AS UNSIGNED);
            IF v_qtd_trav_cabec4 > 0 THEN
            SET i = 1;
            WHILE i <= v_qtd_trav_cabec4 DO
				IF v_os = 1 THEN
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'TRAV. CABEC. 40 mm', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. TRAV. CABEC. 40 mm', i, v_cor_planilha, 'Pendente');
                ELSE 
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'TRAV. CABEC. 40 mm', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. TRAV. CABEC. 40 mm', i, v_cor_planilha, 'Pendente');
				END IF;
                SET i = i + 1;
            END WHILE;
        END IF;
        
        SET v_qtd_prot_barrel = CAST(NULLIF(TRIM(v_qtd_prot_barrel),  '') AS UNSIGNED);
            IF v_qtd_prot_barrel > 0 THEN
            SET i = 1;
            WHILE i <= v_qtd_prot_barrel DO
				IF v_os = 1 THEN
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'CAPA PROT. BARREL CLÁSS.', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. CAPA PROT. BARREL CLÁSS.', i, v_cor_planilha, 'Pendente');
                ELSE 
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'CAPA PROT. BARREL CLÁSS.', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. CAPA PROT. BARREL CLÁSS.', i, v_cor_planilha, 'Pendente');
				END IF;
                SET i = i + 1;
            END WHILE;
        END IF;
        
        SET v_qtd_sheepskin = CAST(NULLIF(TRIM(v_qtd_sheepskin),  '') AS UNSIGNED);
            IF v_qtd_sheepskin > 0 THEN
            SET i = 1;
            WHILE i <= v_qtd_sheepskin DO
				IF v_os = 1 THEN
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'SHEEPSKIN COVER', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. SHEEPSKIN COVER', i, v_cor_planilha, 'Pendente');
                ELSE 
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'SHEEPSKIN COVER', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. SHEEPSKIN COVER', i, v_cor_planilha, 'Pendente');
				END IF;
                SET i = i + 1;
            END WHILE;
        END IF;
        
        SET v_qtd_bastao_alumin = CAST(NULLIF(TRIM(v_qtd_bastao_alumin),  '') AS UNSIGNED);
            IF v_qtd_bastao_alumin > 0 THEN
            SET i = 1;
            WHILE i <= v_qtd_bastao_alumin DO
				IF v_os = 1 THEN
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'BASTÃO ALUMÍNIO 1,5 M', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. BASTÃO ALUMÍNIO 1,5 M', i, v_cor_planilha, 'Pendente');
                ELSE 
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'BASTÃO ALUMÍNIO 1,5 M', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. BASTÃO ALUMÍNIO 1,5 M', i, v_cor_planilha, 'Pendente');
				END IF;
                SET i = i + 1;
            END WHILE;
        END IF;
        
        SET v_qtd_puxador_alumin = CAST(NULLIF(TRIM(v_qtd_puxador_alumin),  '') AS UNSIGNED);
            IF v_qtd_puxador_alumin > 0 THEN
            SET i = 1;
            WHILE i <= v_qtd_puxador_alumin DO
				IF v_os = 1 THEN
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'PUXADOR DE ALUMINIO', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. PUXADOR DE ALUMINIO', i, v_cor_planilha, 'Pendente');
                ELSE 
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'PUXADOR DE ALUMINIO', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. PUXADOR DE ALUMINIO', i, v_cor_planilha, 'Pendente');
				END IF;
                SET i = i + 1;
            END WHILE;
        END IF;
        
        SET v_qtd_anel_pilates = CAST(NULLIF(TRIM(v_qtd_anel_pilates),  '') AS UNSIGNED);
            IF v_qtd_anel_pilates > 0 THEN
            SET i = 1;
            WHILE i <= v_qtd_anel_pilates DO
				IF v_os = 1 THEN
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'ANEL DE PILATES ARCHIVE AÇO', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. ANEL DE PILATES ARCHIVE AÇO', i, v_cor_planilha, 'Pendente');
                ELSE 
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'ANEL DE PILATES ARCHIVE AÇO', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. ANEL DE PILATES ARCHIVE AÇO', i, v_cor_planilha, 'Pendente');
				END IF;
                SET i = i + 1;
            END WHILE;
        END IF;
        
        SET v_qtd_magic = CAST(NULLIF(TRIM(v_qtd_magic),  '') AS UNSIGNED);
            IF v_qtd_magic > 0 THEN
            SET i = 1;
            WHILE i <= v_qtd_magic DO
				IF v_os = 1 THEN
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'MAGIC SQUARE', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. MAGIC SQUARE', i, v_cor_planilha, 'Pendente');
                ELSE 
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'MAGIC SQUARE', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. MAGIC SQUARE', i, v_cor_planilha, 'Pendente');
				END IF;
                SET i = i + 1;
            END WHILE;
        END IF;
        
        SET v_qtd_foot_correc = CAST(NULLIF(TRIM(v_qtd_foot_correc),  '') AS UNSIGNED);
            IF v_qtd_foot_correc > 0 THEN
            SET i = 1;
            WHILE i <= v_qtd_foot_correc DO
				IF v_os = 1 THEN
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'FOOT CORREC. ALUM.', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. FOOT CORREC. ALUM.', i, v_cor_planilha, 'Pendente');
                ELSE 
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'FOOT CORREC. ALUM.', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. FOOT CORREC. ALUM.', i, v_cor_planilha, 'Pendente');
				END IF;
                SET i = i + 1;
            END WHILE;
        END IF;
        
        SET v_qtd_bean_bag = CAST(NULLIF(TRIM(v_qtd_bean_bag),  '') AS UNSIGNED);
            IF v_qtd_bean_bag > 0 THEN
            SET i = 1;
            WHILE i <= v_qtd_bean_bag DO
				IF v_os = 1 THEN
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'BEAN BAG', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. BEAN BAG', i, v_cor_planilha, 'Pendente');
                ELSE 
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'BEAN BAG', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. BEAN BAG', i, v_cor_planilha, 'Pendente');
				END IF;
                SET i = i + 1;
            END WHILE;
        END IF;
        
        SET v_qtd_breath = CAST(NULLIF(TRIM(v_qtd_breath),  '') AS UNSIGNED);
            IF v_qtd_breath > 0 THEN
            SET i = 1;
            WHILE i <= v_qtd_breath DO
				IF v_os = 1 THEN
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'BREATH A CIZER', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. BREATH A CIZER', i, v_cor_planilha, 'Pendente');
                ELSE 
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'BREATH A CIZER', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. BREATH A CIZER', i, v_cor_planilha, 'Pendente');
				END IF;
                SET i = i + 1;
            END WHILE;
        END IF;
        
        SET v_qtd_neck = CAST(NULLIF(TRIM(v_qtd_neck),  '') AS UNSIGNED);
            IF v_qtd_neck > 0 THEN
            SET i = 1;
            WHILE i <= v_qtd_neck DO
				IF v_os = 1 THEN
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'NECK STRETCHER', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. NECK STRETCHER', i, v_cor_planilha, 'Pendente');
                ELSE 
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'NECK STRETCHER', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. NECK STRETCHER', i, v_cor_planilha, 'Pendente');
				END IF;
                SET i = i + 1;
            END WHILE;
        END IF;
        
        SET v_qtd_hand_tens = CAST(NULLIF(TRIM(v_qtd_hand_tens),  '') AS UNSIGNED);
            IF v_qtd_hand_tens > 0 THEN
            SET i = 1;
            WHILE i <= v_qtd_hand_tens DO
				IF v_os = 1 THEN
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'HAND TENS O METER', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. HAND TENS O METER', i, v_cor_planilha, 'Pendente');
                ELSE 
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'HAND TENS O METER', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. HAND TENS O METER', i, v_cor_planilha, 'Pendente');
				END IF;
                SET i = i + 1;
            END WHILE;
        END IF;
        
        SET v_qtd_toe = CAST(NULLIF(TRIM(v_qtd_toe),  '') AS UNSIGNED);
            IF v_qtd_toe > 0 THEN
            SET i = 1;
            WHILE i <= v_qtd_toe DO
				IF v_os = 1 THEN
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'TOE EXERCISER', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. TOE EXERCISER', i, v_cor_planilha, 'Pendente');
                ELSE 
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'TOE EXERCISER', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. TOE EXERCISER', i, v_cor_planilha, 'Pendente');
				END IF;
                SET i = i + 1;
            END WHILE;
        END IF;
        
        SET v_qtd_air_plane = CAST(NULLIF(TRIM(v_qtd_air_plane),  '') AS UNSIGNED);
            IF v_qtd_air_plane > 0 THEN
            SET i = 1;
            WHILE i <= v_qtd_air_plane DO
				IF v_os = 1 THEN
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'AIR PLANE BOARD', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. AIR PLANE BOARD', i, v_cor_planilha, 'Pendente');
                ELSE 
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'AIR PLANE BOARD', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. AIR PLANE BOARD', i, v_cor_planilha, 'Pendente');
				END IF;
                SET i = i + 1;
            END WHILE;
        END IF;
        
        SET v_qtd_finger_exc = CAST(NULLIF(TRIM(v_qtd_finger_exc),  '') AS UNSIGNED);
            IF v_qtd_finger_exc > 0 THEN
            SET i = 1;
            WHILE i <= v_qtd_finger_exc DO
				IF v_os = 1 THEN
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'FINGER EXERCISE', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. FINGER EXERCISE', i, v_cor_planilha, 'Pendente');
                ELSE 
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'FINGER EXERCISE', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. FINGER EXERCISE', i, v_cor_planilha, 'Pendente');
				END IF;
                SET i = i + 1;
            END WHILE;
        END IF;
        
        SET v_qtd_push_up = CAST(NULLIF(TRIM(v_qtd_push_up),  '') AS UNSIGNED);
            IF v_qtd_push_up > 0 THEN
            SET i = 1;
            WHILE i <= v_qtd_push_up DO
				IF v_os = 1 THEN
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'PUSH UP DEVICE (PAR)', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. PUSH UP DEVICE (PAR)', i, v_cor_planilha, 'Pendente');
                ELSE 
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'PUSH UP DEVICE (PAR)', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. PUSH UP DEVICE (PAR)', i, v_cor_planilha, 'Pendente');
				END IF;
                SET i = i + 1;
            END WHILE;
        END IF;
        
        SET v_qtd_mini_barrel = CAST(NULLIF(TRIM(v_qtd_mini_barrel),  '') AS UNSIGNED);
            IF v_qtd_mini_barrel > 0 THEN
            SET i = 1;
            WHILE i <= v_qtd_mini_barrel DO
				IF v_os = 1 THEN
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'MINI BARREL', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. MINI BARREL', i, v_cor_planilha, 'Pendente');
                ELSE 
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'MINI BARREL', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. MINI BARREL', i, v_cor_planilha, 'Pendente');
				END IF;
                SET i = i + 1;
            END WHILE;
        END IF;
        
        SET v_qtd_mini_spine = CAST(NULLIF(TRIM(v_qtd_mini_spine),  '') AS UNSIGNED);
            IF v_qtd_mini_spine > 0 THEN
            SET i = 1;
            WHILE i <= v_qtd_mini_spine DO
				IF v_os = 1 THEN
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'MINI SPINE', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_os (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. MINI SPINE', i, v_cor_planilha, 'Pendente');
                ELSE 
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'MINI SPINE', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_producao (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Emb. MINI SPINE', i, v_cor_planilha, 'Pendente');
				END IF;
                SET i = i + 1;
            END WHILE;
        END IF;
        
    END LOOP;

    CLOSE cur;
END //

DELIMITER ;

CALL gerar_unidades_producao_classico();

TRUNCATE TABLE itens_producao;
TRUNCATE TABLE itens_os;
TRUNCATE TABLE pedidos_prontos;

SELECT * FROM pedidos_prontos;