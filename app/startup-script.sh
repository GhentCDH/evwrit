#!/bin/bash

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
# install frontend dependencies
echo "Installing frontend dependencies"
pnpm install 
# start the symfony server
echo "Starting server..."
symfony server:start -d --port=8080 --no-tls

echo "Server started"
echo "Don't forget to execute following command"
echo "php bin/console app:elasticsearch:index <index> [limit]"