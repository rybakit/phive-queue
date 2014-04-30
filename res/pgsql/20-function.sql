CREATE OR REPLACE FUNCTION {{routine_name}}(now {{table_name}}.eta%type)
RETURNS SETOF {{table_name}} AS $$
DECLARE
    r {{table_name}}%rowtype;
BEGIN
    SELECT * INTO r
    FROM {{table_name}}
    WHERE eta <= now
    ORDER BY eta
    LIMIT 1
    FOR UPDATE;

    IF FOUND THEN
        DELETE FROM {{table_name}} WHERE id = r.id;
        RETURN NEXT r;
    END IF;
END;
$$ LANGUAGE plpgsql;
