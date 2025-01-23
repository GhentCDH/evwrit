#!/bin/sh
cd /app

# Start SSH agent
eval "$(ssh-agent -s)"
echo "$SSH_AUTH_SOCK"
# Add all SSH keys in /home/.ssh directory
for key in ~/.ssh/*; do
    ssh-add "$key"
done

# install backend dependencies
echo "Installing backend dependencies"
composer install
#echo "Dumping environment variables to .env.local.php"
#composer dump-env dev

# clear cache
echo "Clearing cache..."
php bin/console cache:clear

# wait for Elasticsearch to be up
until curl -s "elasticsearch:9200" > /dev/null; do
    echo "Waiting for Elasticsearch to be up..."
    sleep 5
done

# check elasticesearch indices
curl -f -s -I "elasticsearch:9200/${ELASTICSEARCH_INDEX_PREFIX}_texts" > /dev/null
if [ $? -eq 0 ]; then
    echo "Text index already exists"
else
    echo "Creating text index (100 records max) ..."
    php bin/console app:elasticsearch:index text 100
fi

curl -f -s -I "elasticsearch:9200/${ELASTICSEARCH_INDEX_PREFIX}_level" > /dev/null
if [ $? -eq 0 ]; then
    echo "Level index already exists"
else
    echo "Creating level index (100 records max) ..."
    php bin/console app:elasticsearch:index level 100
fi

# start the symfony server
echo "Starting server..."
symfony server:stop
symfony local:server:start --port=8000 --no-tls --allow-all-ip

echo "Server started"
echo "Don't forget to execute following command"
echo "php bin/console app:elasticsearch:index <index> [limit]"