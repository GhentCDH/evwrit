#!/bin/bash
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
echo "Backend dependencies installed"
echo "Dumping environment variables to .env.local.php"
composer dump-env dev

# clear cache
echo "Clearing cache..."
php bin/console cache:clear

# start the symfony server
echo "Starting server..."
symfony local:server:start --port=8000 --no-tls --allow-all-ip

echo "Server started"
echo "Don't forget to execute following command"
echo "php bin/console app:elasticsearch:index <index> [limit]"