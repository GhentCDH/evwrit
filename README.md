# Evwrit

## Requirements

- Apache2
- PHP 7.4 FPM
- Elasticsearch 7
- Postgresql 12
- Composer2


- Development tools
  - node 12
  - npm 6

## Install development version 

### Vagrant setup

    [TODO] git clone git@github.ugent.be:GhentCDH/Evwrit-vagrant.git evwrit_vagrant
    cd evwrit_vagrant

Start virtual machine

    vagrant up

SSH to vm

    vagrant ssh

### Install server packages 

    sudo ./install/elasticsearch7.sh
    sudo ./install/apache.sh
    sudo ./install/php7.4-fpm.sh
    sudo ./install/postgresql-12.sh
    # install build tools
    sudo ./install/composer.sh
    sudo ./install/nodejs.sh

#### set default php version to 7.4

    sudo update-alternatives --set php /usr/bin/php7.4    

### Deploy code

    git clone git@github.ugent.be:GhentCDH/Evwrit-web.git evwrit


Add `.env.dev.local` to project root and set APP_SECRET variable

Dump environment

    composer dump-env dev

### Import database

Create a new user

    sudo -u postgres psql < ./dev/create-role.sql

Download database from [data.ghentcdh.ugent.be](https://data.ghentcdh.ugent.be) and import using

    sudo -u postgres psql < evwrit.sql

Alter permissions after import

    sudo -u postgres psql < alter-grants.sql

### Create/Update Elasticsearch index

    php bin/console app:elasticsearch:index text

## Misc

### Pull production build

    git pull
    php7.4 bin/console cache:clear --env=prod




