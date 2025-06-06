-- docker/postgres/init/01-create-databases.sql
-- Erstellt die Testdatenbank, falls sie nicht existiert

CREATE DATABASE cashbox_test WITH OWNER postgres;

-- Aktiviere UUID-Erweiterung für beide Datenbanken
\c cashbox_db
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

\c cashbox_test
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
