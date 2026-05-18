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

ALTER TABLE itens_os ADD COLUMN cor VARCHAR(100) DEFAULT NULL AFTER posicao_no_pedido;
ALTER TABLE itens_os ADD COLUMN prazo_producao VARCHAR(100) DEFAULT NULL AFTER numero_pedido;

ALTER TABLE itens_os DROP COLUMN prazo_producao;

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
            `COD. COR`
        FROM tabela_adaptada;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

    OPEN cur;

    read_loop: LOOP
        FETCH cur INTO v_pedido, v_prazo, v_qtd_reformer, v_qtd_torre, v_qtd_cadilac, v_qtd_chair, v_qtd_barrel, v_cor_planilha;
        
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
        
       SET v_qtd_reformer = CAST(NULLIF(TRIM(v_qtd_torre), '') AS UNSIGNED);
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

    END LOOP;

    CLOSE cur;
END //

DELIMITER ;

TRUNCATE TABLE itens_os;
TRUNCATE TABLE itens_producao;

CALL gerar_unidades_producao();

SELECT * FROM itens_producao WHERE numero_pedido LIKE 'OS%';

SELECT * FROM itens_os;

SELECT DISTINCT numero_pedido FROM itens_producao WHERE numero_pedido LIKE 'OS%' OR numero_pedido LIKE '%OS%';