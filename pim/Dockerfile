######################################################################
# Copyright (c) 2019, VillageReach
# Licensed under the Non-Profit Open Software License version 3.0.
# SPDX-License-Identifier: NPOSL-3.0
######################################################################

#--- Akeneo base ---
FROM pcmt/fpm:php-7.2 as akeneo
LABEL maintainer="josh.zamor@villagereach.org"
ARG AKENEO_URL=https://github.com/akeneo/pim-community-standard/archive/
ARG AKENEO_VER

USER root
RUN mkdir -p /srv/pim && \
    chown docker:docker /srv/pim && \
    sed -i 's#mozilla/DST_Root_CA_X3.crt#!mozilla/DST_Root_CA_X3.crt#g' /etc/ca-certificates.conf && \
    update-ca-certificates && \
    apt-key del 95BD4743 && \
    wget -O /etc/apt/trusted.gpg.d/sury.gpg https://packages.sury.org/php/apt.gpg && \
    apt-get update && \
    apt-get install netcat cron dnsutils patch -y && \
    apt-get clean && \
    apt-get autoremove

USER docker
ADD --chown=docker:docker ${AKENEO_URL}${AKENEO_VER}.tar.gz /home/docker/pim.tar.gz
RUN tar xvzf /home/docker/pim.tar.gz -C /srv/pim --strip-components=1
WORKDIR /srv/pim

#--- PCMT PIM BUILD ---
FROM akeneo as pim-build
ARG PCMT_SEMVER
ARG COMPOSER_CACHE_PATH=/home/docker/.composer
LABEL maintainer="josh.zamor@villagereach.org"
ENV COMPOSER_CACHE_DIR ${COMPOSER_CACHE_PATH}
COPY --from=akeneo --chown=docker:docker /srv/pim /srv/pim
ADD --chown=docker:docker .cache/composer ${COMPOSER_CACHE_DIR}
ADD --chown=docker:docker composer.* /srv/pim/
ADD --chown=docker:docker package.json /srv/pim/
ADD --chown=docker:docker yarn.lock /srv/pim/
ADD --chown=docker:docker patches/ /srv/pim/patches/
ADD --chown=docker:docker app/ /srv/pim/app/
ADD --chown=docker:docker src/ /srv/pim/src/
ADD --chown=docker:docker crontab /srv/pim/crontab
ADD --chown=docker:docker docker/pim/wait.sh /srv/pim/
ADD --chown=docker:docker docker/pim/write-env-reverse-proxy.sh /srv/pim/
ADD --chown=docker:docker docker/pim/start.sh /srv/pim/
ADD --chown=docker:docker docker/pim/cpFromTmp.sh /srv/pim/
ADD --chown=docker:docker docker/pim/cronRun.sh /srv/pim/
ADD --chown=docker:docker docker/pim/pcmtMigrate.sh /srv/pim/

RUN src/cleanIfRelease.sh ${PCMT_SEMVER} && \
    php -d memory_limit=3G /usr/local/bin/composer install && \
    rm -rf var/cache/* && \
    mkdir -p /srv/pim/app/file_storage/catalog && \
    mkdir -p /srv/pim/app/uploads/product /srv/pim/app/archive && \
    mkdir -p /tmp/pim/file_storage /tmp/pim/upload_tmp_dir
CMD /srv/pim/start.sh

#--- Node ---
FROM node:10-slim AS node
USER node
ARG YARN_CACHE_PATH=/home/node/.yarn-cache
ADD --chown=node:node .cache/yarn-cache ${YARN_CACHE_PATH}
COPY --from=pim-build --chown=node:node /srv/pim /srv/pim

WORKDIR /srv/pim
ENV YARN_CACHE_FOLDER ${YARN_CACHE_PATH}docker
RUN yarn install && \
    yarn run less && \
    yarn run webpack-dev

#--- Httpd ---
FROM httpd:2.4 as httpd
LABEL maintainer="josh.zamor@villagereach.org"
COPY --from=node --chown=root:www-data /srv/pim/docker/httpd.conf /usr/local/apache2/conf/httpd.conf
COPY --from=node --chown=root:www-data /srv/pim/docker/akeneo.conf /usr/local/apache2/conf/vhost.conf

# pim
FROM pim-build as pim

# xdebug.ini file is in some way overwritten later, so we needed to put our config directly into conf.d.
# ADD --chown=docker:docker ini/xdebug.ini /etc/php/7.2/mods-available/xdebug.ini
ADD --chown=docker:docker ini/xdebug.ini /etc/php/7.2/cli/conf.d/35-xdebug.ini

COPY --from=node --chown=docker:docker /srv/pim /srv/pim
VOLUME /srv/pim
VOLUME /srv/pim/app/file_storage
VOLUME /srv/pim/app/uploads
