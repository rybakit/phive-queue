DROP TABLE IF EXISTS {{table_name}};
CREATE TABLE {{table_name}}(id INTEGER PRIMARY KEY AUTOINCREMENT, eta integer NOT NULL, item text NOT NULL);
