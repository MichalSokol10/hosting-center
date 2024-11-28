#!/bin/bash
set -e

psql -v ON_ERROR_STOP=1 --username "$POSTGRES_USER" --dbname "$POSTGRES_DB" <<-EOSQL
	CREATE TABLE users (
        domain VARCHAR(255),
        password VARCHAR(255)
    );

    CREATE OR REPLACE PROCEDURE create_user(
    IN domain_param VARCHAR(255),
    IN password_param VARCHAR(255)
    )
    LANGUAGE plpgsql
    AS \$\$
    BEGIN
        INSERT INTO users (domain, password) VALUES (domain_param, password_param);
    END;
    \$\$;
EOSQL