DROP TABLE IF EXISTS {{table_name}} CASCADE;
CREATE TABLE {{table_name}}(id SERIAL, eta integer NOT NULL, item text NOT NULL) ENGINE=InnoDB;
