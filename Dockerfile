ARG NODE_VERSION=20
ARG PHP_VERSION=8.2
# ----------------------------------------------------------
# NODE
# ----------------------------------------------------------

# NODE-BASE
FROM node:${NODE_VERSION}-slim AS node-base

RUN apt-get update -qq && \
    apt-get install -qq -y \
        openssh-client \
        curl \
        apt-transport-https \
        gnupg \
        openssh-client \
        software-properties-common \
        git && \
    apt-get clean && \
    apt-get autoclean

# Add GitHub.com to known hosts for SSH
RUN mkdir -p -m 0600 ~/.ssh && \
    ssh-keyscan github.com >> ~/.ssh/known_hosts

RUN npm install -g corepack@latest
RUN corepack enable
RUN corepack prepare pnpm@latest

# NODE-PROD
FROM node-base AS node-prod

WORKDIR "/app"
COPY ./app /app

# add ssh key (add context .ssh default in docker compose)
RUN --mount=type=ssh pnpm install && \
    pnpm encore production

# NODE-DEV
FROM node-base AS node-dev

WORKDIR "/app"

ENV PNPM_HOME="/pnpm"
ENV PATH="$PNPM_HOME:$PATH"

CMD pnpm install; pnpm encore dev --watch

# ----------------------------------------------------------
# PHP/SYMFONY
# ----------------------------------------------------------

FROM webdevops/php-dev:${PHP_VERSION} AS symfony-base

WORKDIR "/app"

RUN set -eux && \
    apt-get update -qq && \
    apt-get install -qq -y \
        curl \
        apt-transport-https \
        gnupg \
        openssh-client \
        software-properties-common \
        git && \
    curl -1sLf 'https://dl.cloudsmith.io/public/symfony/stable/setup.deb.sh' | bash - && \
    apt-get install symfony-cli && \
    apt-get clean && \
    apt-get autoclean

ENV COMPOSER_ALLOW_SUPERUSER=1

# ----------------------------------------------------------
# DEVELOPMENT
# ----------------------------------------------------------

FROM symfony-base AS dev
USER root
WORKDIR "/app"

ENV APP_ENV=dev
ENV PHP_MEMORY_LIMIT=1024M
ENV PHP_DISMOD=ioncube,opcache

CMD /app/scripts/startup-script.sh

# ----------------------------------------------------------
# BASE-PRODUCTION
# ----------------------------------------------------------

FROM symfony-base AS symfony-prod

# Add GitHub.com to known hosts for SSH
RUN mkdir -p -m 0600 ~/.ssh && \
    ssh-keyscan github.com >> ~/.ssh/known_hosts

COPY ./app /app

# install composer dependencies
RUN --mount=type=ssh \
    set -eux; \
    composer install --no-scripts --no-dev --no-progress; \
    composer dump-autoload --optimize --no-dev --classmap-authoritative;

# ----------------------------------------------------------
# PRODUCTION
# ----------------------------------------------------------

FROM webdevops/php-apache:${PHP_VERSION} AS prod
# USER application

COPY --from=symfony-prod --chown=1000:1000 /app /app
COPY --from=node-prod --chown=1000:1000 /app/public/build /app/public/build

# Copy entrypoint script for Apache port configuration
COPY --chown=application:application app/scripts/entrypoint.d/10-configure-apache-port.sh /opt/docker/provision/entrypoint.d/
RUN chmod +x /opt/docker/provision/entrypoint.d/10-configure-apache-port.sh

ENV APP_ENV=prod
ENV PHP_MEMORY_LIMIT=1024M
ENV PHP_DISMOD=ioncube,xcache
ENV APACHE_HTTP_PORT=80

ENV WEB_DOCUMENT_ROOT="/app/public"
ENV WEB_DOCUMENT_INDEX="/app/public/index.php"

# Expose the Apache port (can be overridden at runtime)
EXPOSE ${APACHE_HTTP_PORT}
