CREATE OR REPLACE FUNCTION {{routine_name}}(now {{table_name}}.eta%TYPE)
RETURNS SETOF {{table_name}} AS $$
DECLARE
    found_id {{table_name}}.id%TYPE;
BEGIN
    SELECT id INTO found_id
    FROM {{table_name}}
    WHERE eta <= now AND pg_try_advisory_xact_lock(id)
    ORDER BY eta
    LIMIT 1
    FOR UPDATE;

    IF FOUND THEN
        RETURN QUERY EXECUTE 'DELETE FROM {{table_name}} WHERE id = $1 RETURNING *' USING found_id;
    END IF;
END;
$$ LANGUAGE plpgsql;
