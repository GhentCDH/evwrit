ARG PHP_VERSION=8.0

# ----------------------------------------------------------
# BASE
# ----------------------------------------------------------

FROM  webdevops/php-apache:${PHP_VERSION} AS base

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
    curl -sL https://deb.nodesource.com/setup_18.x | bash - && \
    apt-get install -y nodejs && \
    curl -1sLf 'https://dl.cloudsmith.io/public/symfony/stable/setup.deb.sh' | bash - && \
    apt-get install symfony-cli && \
    apt-get clean && \
    apt-get autoclean

ENV PNPM_HOME="/pnpm"
ENV PATH="$PNPM_HOME:$PATH"
RUN corepack enable 
ENV WEB_DOCUMENT_ROOT="/app"
ENV WEB_DOCUMENT_INDEX="/app/public/index.php"
ENV COMPOSER_ALLOW_SUPERUSER=1

# ----------------------------------------------------------
# BASE-PRODUCTION
# ----------------------------------------------------------

FROM base AS base-prod

# Add GitHub.com to known hosts for SSH
RUN mkdir -p -m 0600 ~/.ssh && \
    ssh-keyscan github.com >> ~/.ssh/known_hosts

COPY ./app /app
COPY .env.prod /app/
RUN --mount=type=ssh composer install && \
    composer dump-env prod && \
    # install node dependencies
    pnpm install && \
    pnpm encore production

# ----------------------------------------------------------
# PRODUCTION
# ----------------------------------------------------------

FROM base-prod AS prod
USER application

COPY --from=base-prod --chown=1000:1000 /app /app
ENV APP_ENV=prod
ENV PHP_MEMORY_LIMIT=1024M


CMD ["symfony", "server:start", "--port=8080"]

# ----------------------------------------------------------
# DEVELOPMENT
# ----------------------------------------------------------

FROM base AS dev
USER root
ENV APP_ENV=dev
