CREATE TABLE IF NOT EXISTS itens_producao (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_pedido VARCHAR(50) NOT NULL,
    equipamento VARCHAR(100) NOT NULL,
    posicao_no_pedido INT NOT NULL, 
    status VARCHAR(50) DEFAULT 'Pendente',
    data_inicio DATETIME DEFAULT NULL,
    data_fim DATETIME DEFAULT NULL
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
    DECLARE i INT;

    DECLARE cur CURSOR FOR 
        SELECT 
            `NUMERO PEDIDO`, 
            CAST(NULLIF(`Reformer Excellence`, '') AS UNSIGNED), 
            CAST(NULLIF(`Reformer Torre`, '') AS UNSIGNED), 
            CAST(NULLIF(`Cadilac Excelence`, '') AS UNSIGNED), 
            CAST(NULLIF(`Step Chair Excelence`, '') AS UNSIGNED), 
            CAST(NULLIF(`Lader Barrel Excelence`, '') AS UNSIGNED)
        FROM tabela_adaptada;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

    OPEN cur;

    read_loop: LOOP
        FETCH cur INTO v_pedido, v_qtd_reformer, v_qtd_torre, v_qtd_cadilac, v_qtd_chair, v_qtd_barrel;
        
        IF done THEN
            LEAVE read_loop;
        END IF;

        IF v_qtd_reformer > 0 THEN
            SET i = 1;
            WHILE i <= v_qtd_reformer DO
                INSERT IGNORE INTO itens_producao (numero_pedido, equipamento, posicao_no_pedido, status) 
                VALUES (v_pedido, 'Reformer Excellence', i, 'Pendente');
                
                INSERT IGNORE INTO itens_producao (numero_pedido, equipamento, posicao_no_pedido, status) 
                VALUES (v_pedido, 'Carrinho Excellence', i, 'Pendente');
                
                SET i = i + 1;
            END WHILE;
        END IF;

        IF v_qtd_torre > 0 THEN
            SET i = 1;
            WHILE i <= v_qtd_torre DO
                INSERT IGNORE INTO itens_producao (numero_pedido, equipamento, posicao_no_pedido, status) 
                VALUES (v_pedido, 'Reformer Torre', i, 'Pendente');
                
				INSERT IGNORE INTO itens_producao (numero_pedido, equipamento, posicao_no_pedido, status) 
                VALUES (v_pedido, 'Carrinho Torre', i, 'Pendente');
                SET i = i + 1;
            END WHILE;
        END IF;

        IF v_qtd_cadilac > 0 THEN
            SET i = 1;
            WHILE i <= v_qtd_cadilac DO
                INSERT IGNORE INTO itens_producao (numero_pedido, equipamento, posicao_no_pedido, status) 
                VALUES (v_pedido, 'Cadilac Excelence', i, 'Pendente');
                
                INSERT IGNORE INTO itens_producao (numero_pedido, equipamento, posicao_no_pedido, status) 
                VALUES (v_pedido, 'Gaiola Cadilac', i, 'Pendente');
                
                SET i = i + 1;
            END WHILE;
        END IF;

          IF v_qtd_chair > 0 THEN
            SET i = 1;
            WHILE i <= v_qtd_chair DO
                INSERT IGNORE INTO itens_producao (numero_pedido, equipamento, posicao_no_pedido, status) 
                VALUES (v_pedido, 'Step Chair Excelence', i, 'Pendente');
                SET i = i + 1;
            END WHILE;
        END IF;
        
            IF v_qtd_barrel > 0 THEN
            SET i = 1;
            WHILE i <= v_qtd_barrel DO
                INSERT IGNORE INTO itens_producao (numero_pedido, equipamento, posicao_no_pedido, status) 
                VALUES (v_pedido, 'Lader Barrel Excelence', i, 'Pendente');
                SET i = i + 1;
            END WHILE;
        END IF;

    END LOOP;

    CLOSE cur;
END //

DELIMITER ;

TRUNCATE TABLE itens_producao;

CALL gerar_unidades_producao();