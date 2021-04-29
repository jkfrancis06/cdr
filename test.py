#!/usr/bin/python

from __future__ import print_function

import pg

conn = pg.DB( host="localhost", user="admin", passwd="sql", dbname="cdr" )

result = conn.query( "SELECT num_a, num_b FROM public.trecord" )

for num_a, num_b in result.getresult() :
    print( num_a, num_b )

conn.close()