#!/bin/sh
cd /app

bin/console app:elasticsearch:index text $1
bin/console app:elasticsearch:index level $1