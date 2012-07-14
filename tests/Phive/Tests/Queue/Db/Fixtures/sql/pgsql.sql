DROP TABLE IF EXISTS queue;
CREATE TABLE queue(id SERIAL, eta integer NOT NULL, item text NOT NULL);