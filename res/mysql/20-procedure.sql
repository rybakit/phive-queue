DROP PROCEDURE IF EXISTS {{routine_name}};
CREATE PROCEDURE {{routine_name}}(IN now int)
BEGIN
    DECLARE found_id int;
    DECLARE found_item text;

    START TRANSACTION;

    SELECT id, item INTO found_id, found_item
    FROM {{table_name}}
    WHERE eta <= now
    ORDER BY eta
    LIMIT 1
    FOR UPDATE;

    IF found_id IS NOT NULL THEN
        DELETE FROM {{table_name}}
        WHERE id = found_id;
        SELECT found_item;
    ELSE
        SELECT NULL LIMIT 0;
    END IF;

    COMMIT;
END
