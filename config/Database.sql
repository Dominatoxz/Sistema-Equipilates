USE planilha_db;

CREATE TABLE IF NOT EXISTS itens_producao (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_pedido VARCHAR(50) NOT NULL,
    prazo_producao INT,
    equipamento VARCHAR(100) NOT NULL,
    posicao_no_pedido INT NOT NULL, 
    cor VARCHAR(100),
    status VARCHAR(50) DEFAULT 'Pendente',
    data_inicio DATETIME DEFAULT NULL,
    data_fim DATETIME DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE itens_producao ADD INDEX idx_pedidos_numero (numero_pedido);

CREATE TABLE IF NOT EXISTS itens_os (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_pedido VARCHAR(50) NOT NULL,
    prazo_producao INT,
    equipamento VARCHAR(100) NOT NULL,
    posicao_no_pedido INT NOT NULL, 
    cor VARCHAR(100),
    status VARCHAR(50) DEFAULT 'Pendente',
    data_inicio DATETIME DEFAULT NULL,
    data_fim DATETIME DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS itens_os_acess (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_pedido VARCHAR(50) NOT NULL,
    prazo_producao INT,
    equipamento VARCHAR(100) NOT NULL,
    posicao_no_pedido INT NOT NULL, 
    cor VARCHAR(100),
    status VARCHAR(50) DEFAULT 'Pendente',
    data_inicio DATETIME DEFAULT NULL,
    data_fim DATETIME DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


ALTER TABLE itens_os ADD INDEX idx_pedido_equipamento (numero_pedido, equipamento);

CREATE TABLE IF NOT EXISTS pedidos_prontos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_pedido VARCHAR(50) NOT NULL,
    prazo_producao INT,
	data_conclusao DATETIME DEFAULT CURRENT_TIMESTAMP,
    status_posvenda VARCHAR(50) DEFAULT 'Pendente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
    DECLARE v_os_acess INT;

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
            SET v_os_acess = 1;
		ELSE
			SET v_os = 0;
            SET v_os_acess = 0;
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
				IF v_os_acess = 1 THEN
					INSERT IGNORE INTO itens_os_acess (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Caixa Mini', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_os_acess (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
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
				IF v_os_acess = 1 THEN
					INSERT IGNORE INTO itens_os_acess (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Caixa do Reformer', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_os_acess (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
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
				IF v_os_acess = 1 THEN
					INSERT IGNORE INTO itens_os_acess (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'P. de Molas - B R I N D E', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_os_acess (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
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
				IF v_os_acess = 1 THEN
					INSERT IGNORE INTO itens_os_acess (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'P. de Molas - C O M P L E T A', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_os_acess (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
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
				IF v_os_acess = 1 THEN
					INSERT IGNORE INTO itens_os_acess (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'P. de Molas - P u s h T h r u', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_os_acess (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
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
				IF v_os_acess = 1 THEN
					INSERT IGNORE INTO itens_os_acess (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Caixa da Cadeira', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_os_acess (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
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
				IF v_os_acess = 1 THEN
					INSERT IGNORE INTO itens_os_acess (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
					VALUES (v_pedido, v_prazo, 'Prancha de Alongamento', i, v_cor_planilha, 'Pendente');
					
					INSERT IGNORE INTO itens_os_acess (numero_pedido, prazo_producao, equipamento, posicao_no_pedido, cor, status) 
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

TRUNCATE TABLE itens_os;
TRUNCATE TABLE itens_os_acess;
TRUNCATE TABLE itens_producao;
TRUNCATE TABLE pedidos_prontos;

CALL gerar_unidades_producao();

SELECT * FROM itens_os_acess;
