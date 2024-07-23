#!/bin/bash

# Start SSH agent
eval "$(ssh-agent -s)"
echo "$SSH_AUTH_SOCK"
# # Add all SSH keys in /home/.ssh directory
for key in ~/.ssh/*; do
    ssh-add "$key"
done