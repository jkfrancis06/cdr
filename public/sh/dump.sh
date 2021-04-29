#!/bin/sh

psql postgresql://admin:sql@localhost:5432/cdr

COPY zip_codes FROM '/path/to/csv/ZIP_CODES.txt' WITH (FORMAT csv);


status=$?

[ $status -eq 0 ] && echo "$cmd command was successful" || echo "$cmd failed"