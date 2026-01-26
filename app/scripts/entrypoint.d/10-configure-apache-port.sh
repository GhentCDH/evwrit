#!/bin/bash
set -e

# Default Apache HTTP port
: ${APACHE_HTTP_PORT:=80}

echo "[Apache Port Configuration] Starting..."

# Validate port number
if ! [[ "$APACHE_HTTP_PORT" =~ ^[0-9]+$ ]] || [ "$APACHE_HTTP_PORT" -lt 1 ] || [ "$APACHE_HTTP_PORT" -gt 65535 ]; then
    echo "[Apache Port Configuration] ERROR: Invalid port number: $APACHE_HTTP_PORT"
    echo "[Apache Port Configuration] Port must be between 1 and 65535"
    exit 1
fi

echo "[Apache Port Configuration] Configuring Apache to use port: $APACHE_HTTP_PORT"

# Update VirtualHost configuration
if [ -f /opt/docker/etc/httpd/vhost.conf ]; then
    sed -i "s/<VirtualHost \*:80>/<VirtualHost *:${APACHE_HTTP_PORT}>/" /opt/docker/etc/httpd/vhost.conf
    echo "[Apache Port Configuration] Updated VirtualHost in vhost.conf"
fi

# Update SSL VirtualHost if exists
if [ -f /opt/docker/etc/httpd/vhost.ssl.conf ]; then
    # Keep HTTPS on 443, only update HTTP references if any
    sed -i "s/<VirtualHost \*:80>/<VirtualHost *:${APACHE_HTTP_PORT}>/" /opt/docker/etc/httpd/vhost.ssl.conf
    echo "[Apache Port Configuration] Updated HTTP VirtualHost in vhost.ssl.conf"
fi

# Check in /etc/apache2/ports.conf if the image uses it
if [ -f /etc/apache2/ports.conf ]; then
    sed -i "s/^Listen 80$/Listen ${APACHE_HTTP_PORT}/" /etc/apache2/ports.conf
    echo "[Apache Port Configuration] Updated ports.conf"
fi

echo "[Apache Port Configuration] Configuration complete!"
echo "[Apache Port Configuration] Apache will listen on port: $APACHE_HTTP_PORT"

