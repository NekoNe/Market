#!/bin/bash
set -e

psql -v ON_ERROR_STOP=1 --username "$POSTGRES_USER" --dbname "$POSTGRES_DB" <<-EOSQL
    CREATE USER market WITH ENCRYPTED PASSWORD '123';
    CREATE DATABASE customers;
    CREATE DATABASE executors;
    CREATE DATABASE tasks;
    GRANT ALL PRIVILEGES ON DATABASE customers TO market;
    GRANT ALL PRIVILEGES ON DATABASE executors TO market;
    GRANT ALL PRIVILEGES ON DATABASE tasks TO market;
EOSQL