# Evwrit

### Pull production build

    git pull
    php7.4 bin/console cache:clear --env=qas

### Import database

    sudo -u postgres psql evwrit < dump-evwrit-xxxxxxxx.sql

### Update Elasticsearch index

    php bin/console app:elasticsearch:index text
    php bin/console app:elasticsearch:index text_materiality

