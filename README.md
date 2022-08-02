# Evwrit

## Requirements

- Apache2
- PHP 7.4 FPM
- Elasticsearch 7
- Postgresql 12
- Composer2


- Development tools
  - node 14
  - npm 6
  - yarn

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
    sudo ./install/php7.4-fpm.sh
    sudo ./install/postgresql-12.sh

    # install build tools
    sudo ./install/composer.sh
    sudo ./install/nodejs.sh
    sudo npm i yarn -g

    # install symfony cli
    sudo ./install/symfony-cli.sh

#### set default php version to 7.4

    sudo update-alternatives --set php /usr/bin/php7.4    

### Deploy code

    cd /vagrant/src/ 
    git clone git@github.ugent.be:GhentCDH/Evwrit-web.git evwrit
    cd evwrit
    # install php dependencies
    composer install
    # install node dependencies
    composer dump-env dev
    # dump .env.* to .env.local.php
    yarn install

### Import database

Create a new user

    sudo -u postgres psql < ./dev/create-role.sql

Download database from [data.ghentcdh.ugent.be](https://data.ghentcdh.ugent.be) and import using

    sudo -u postgres psql < evwrit.sql

Alter permissions after import

    sudo -u postgres psql < ./dev/alter-grants.sql

### Create/Update Elasticsearch index

    php bin/console app:elasticsearch:index text

### Run application

Start the back-end dev server

    symfony server:start --no-tls

Site is available on these addresses:

    http://evwrit.vagrant:8000/
    http://localhost:8000/

## Misc

### Pull qas build

    git pull
    php7.4 bin/console cache:clear --env=qas

### Pull prod build

    git pull
    php7.4 bin/console cache:clear --env=prod




