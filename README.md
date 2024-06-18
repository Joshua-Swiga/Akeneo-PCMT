# Product Catalog Management Tool (PCMT)

PCMT helps organizations tame their multitudes of Product definitions, 
Catalogs, and coding schemes that exist when a collaboration of many groups
are procuring, using and sharing product data.

More specifically the PCMT is aimed at global health stakeholders to ease
their interoperability and master data challenges.

## License, Copyright, Conduct and Contribution

PCMT is an open and freely available community project stewarded by 
VillageReach.  PCMT code and documentation is copyright VillageReach and
licensed under the [NP-OSL v3][np-osl] and the [CC BY-SA 4.0][cc-by-sa].

- [PCMT License & Copyright][pcmt-license]
- [Architecture Decision](./doc/arch/adr-006.md)
- [License How-to](./doc/license-howto.md)
- [Code of Conduct](https://www.contributor-covenant.org/version/1/4/code-of-conduct/)
- TODO: Contribution guide

PCMT includes a derivative work of Akeneo PIM Community Edition which is
copyrighted and licensed from Akeneo SAS:

- [Akeneo PIM CE License & Copyright][akeneo-license]
- [Akeneo PIM Source Code][akeneo-source]

[np-osl]: https://opensource.org/licenses/NPOSL-3.0
[cc-by-sa]: https://creativecommons.org/licenses/by-sa/4.0/
[pcmt-license]: ./COPYRIGHT.md
[akeneo-license]: https://github.com/akeneo/pim-community-standard/blob/master/LICENCE.txt
[akeneo-source]: https://github.com/akeneo/pim-community-standard

## Quick Start

1. Clone Repository
1. `make up`
1. Browse to `localhost`
1. Login with `admin` / `Admin123`.

To stop & cleanup:  `make dev-clean`.

## Development

1. Clone Repository
1. `make` to build images
1. `make dev-up` to run containers. 
1. Wait for environment to start, it'll look like:
      ```
      SUCCESS: PCMT Dev now available at http://localhost
      ```

1. Browse to `localhost`
1. Login with `admin` / `Admin123`.

Notes:
- As local files in `pim/` are updated, the containers will reflect it.
- Run `make dev-assets` to regenerate web assets (e.g. less, js, etc).
- To see the vendor dependencies, run `make dev-cp-vendor` after `make dev-up`,
    which will copy these files from the container to `pim/vendor`.  These files
    are simply copied, not synchronized so don't edit them directly.
- `make dev-fpm` will start a shell with access to Akeneo tools such 
    as `bin/console`.
- `make dev-node` will start a shell with access to npm tools, such as
  `yarn add`.

### Commands

PCMT adds a number of commands that a developer may use.  Unless otherwise 
noted these commands are meant to be run in the `pcmt` container / `fpm` 
service.

Example:

```shell
make dev-fpm
bin/console <command>
```

### Configuration

PCMT secrets and traefik configuration are primarly configured through 
configuration files found in the `conf/` directory.  Examples can be found there
with the extension `.dist`.  If you'd like to change these defaults you should
copy the `.dist` file to a similarly named file in `conf/` without the `.dist`.

For example: `cp conf/parameters.yml.dist conf/parameters.yml`

Then you may point PCMT to the new configuration file by setting an environment
variable.

Example:

```shell
cp conf/paramters.yml.dist conf/parameters.yml
# edit conf/parameters.yml ...
export PCMT_SECRET_CONF=conf/parameters.yml
make up
```

The following environment variables point to their respective configuration
files:

* `PCMT_SECRET_CONF`: Points to a file with secrets used to configure Akeneo.
* `PCMT_TRAEFIK_CONF`: Points to a file with traefik static configuration.

MySQL and ElasticSearch are both configured through their respective docker
container defaults, for now.

#### SSL

The `reverse-proxy` container is responsible for TLS termination using 
[Traefik][traefik].  This repository includes a default static configuration
that's configurable via the file referenced in `$PCMT_TRAEFIK_CONF`, and a 
dynamic configuration that is based on the docker provider, a default 
configuration is included in [docker-compose.tls.yml](docker-compose.tls.yml).

The default configuration could be used by:

```shell
PCMT_PROFILE=dev docker-compose -f docker-compose.yml \
    -f docker-compose.tls.yml \
    up
```

It's recommended that:
- Make a copy of [conf/traefik.toml.dist](conf/traefik.toml.dist) as
  `conf/traefik.toml` and change:
    - Set the `email` field to a valid email.
    - Remove the line `caServer = "https://acme-staging-v02.api.letsencrypt.org/directory"`.
- Set a publicly available hostname with `PCMT_HOSTNAME` when launching, e.g.
  `PCMT_HOSTNAME=pcmt.villagereach.org docker-compose -f docker-compose.yml -f docker-compose.tls.yml up`
  as this will be used to get a certificate with [LetsEncrypt][letsencrypt].

Note that if you re-launch and change `PCMT_HOSTNAME` that you may need to
remove the existing certs in the docker volume `traefikdata`.

[traefik]: https://docs.traefik.io
[letsencrypt]: https://letsencrypt.org

#### Reference Data

`bin/console pcmt:handler:download_reference_data` - Downloads the latest  
reference data from the Internet and stores them alongside the source code.
Run this to update these codes and commit to source control.

`bin/console pcmt:handler:import_reference_data` - Imports the reference data
downloaded in the previous command into the database, potentially overwriting  
any referencedata already there.  Use this in development or testing contexts  
so that the reference data is available in the UI, but beware of running this  
in production.

## Production

This section covers the additional services added with `docker-compose.tls.yml`
and `docker-compose.prod.yml`.

### Production Profile

The `production` profile ensures that PCMT doesn't wipe and re-install the
demo-data in the database - which is the default behavior.

To set this profile set the environment variable `PCMT_PROFILE` to `production`
before starting PCMT.

An example of start PCMT with the demo data, stopping it, and then starting
with the `production` profile would look roughly like this (with a bash shell):

```shell
# start in dev profile to get demo-data and initial db config
export PCMT_PROFILE=dev
docker-compose -f docker-compose.yml -f docker-compose.prod.yml up -d

#  wait for PCMT to start in your browser

# stop with the demo data, and re-start in production.
docker-compose down
export PCMT_PROFILE=production
docker-compose -f docker-compose.yml -f docker-compose.prod.yml up -d
```

This example is meant to give a rough idea.  A production-ready deployment
description is captured in our deployment [readme](deploy/README.md).

### Backup and Restore

Restore:

1. With a working instance deployed.
1. Download the backup desired from the instance's S3 bucket.
1. Transfer the backup to the instance.
1. SSH to instance
1. Unpack the backup into pcmt.sql file
1. Run `make dev-import-sql` 

### Logs

Most logs can be accessed through the typical docker logging mechanism:  `docker log <container name>`.

There are a few logs however that can only be accessed within the container:

- fpm
  - `/srv/pim/var/logs`
    - `dev.log` & `prod.log`: Symfony logs, including from Akeneo and extensions (e.g. PCMT)
- httpd
  - `/var/log`
    - `akeneo_access.log` & `akeneo_error.log`:  apache access and error log
  
### Centralized monitoring w/ Grafana Cloud

Centralized logging and monitoring is done using Grafana Agent pushing to
Grafana Cloud (Loki, Prometheus, Grafana, etc).  Either you can setup your own
Grafana Cloud account, or you can mimic that open-source stack with your own
self-hosted instances of those services.  This configuration is only available
if you include the [docker-compose.prod.yml](./docker-compose.prod.yml) file
while deploying with `docker compose`.

To use the environment variables should be set:

```sh
export PCMT_HOSTNAME=http://yourPcmtInstance # used to delineate environments
export PCMT_GRAFANA_CREDS_CONF=/some/path/to/file/with/grafana/credentials # defaults to ./conf/grafana-creds.json.dist
```

And your appropriate Basic Auth credentials to
[grafana credentials](./conf/grafana-creds.json.dist).

Agent configuration is in [./conf/grafana-agent.river.dist](./conf/grafana-agent.river.dist)

### Migrations

PCMT is using standard Doctrine migrations mechanism, same as Akeneo. 
PCMT migrations configuration is different than the Akeneo migrations configuration
(it has a separate directory for migration files and separate table for migrations already run) -
it is defined in `config/pcmt_migrations.yml` file.

PCMT migrations are run automatically each time the application is deployed.

#### Creating PCMT migration

Run `make dev-pcmt-migration-generate`. The new file will be added to folder
 `PcmtCoreBundle/upgrades/schema`.

#### Running PCMT migration manually

If you want to run PCMT migrations manually, type `make dev-pcmt-migrate`. 

### Updating Javascript's dependencies

In case of issues related to the lack of access to the Akeneo's `package.json` file, we decided to add this file to our repository. The advantage is that from now, we have possibility to control version of each library used by frontend part of the project. But on the other side, we have also a big drawback which is complex process of updating the `package.json` content.

#### How to update Akeneo PIM version?

1. Update Akeneo version in `composer.json`
1. Go to container with `make dev-fpm` and update dependencies with `COMPOSER_MEMORY_LIMIT=-1 composer update`
1. Update `AKENEO_VER` in `pim/build-images.sh`
1. In `pim/Dockerfile` comment out the `ADD --chown=docker:docker package.json /srv/pim/` line.
1. Run `make dev-clean` command, which will clean up your local environment (so be sure if you are able to do it, and that you have all your changes saved).
1. Run `make` command to build the newest version of the PCMT Docker image.
1. Run `make dev-up` and wait until your environment will be ready.
1. Run `make dev-cp-package-json` command. This command is responsible for copying the `package.json` file from running fpm Docker container to your local codebase.
1. Uncomment the `ADD --chown=docker:docker package.json /srv/pim/` line in `pim/Dockerfile`.
1. Commit your changes and push them to the remote repository.
  
---
Copyright (c) 2024, VillageReach.  Licensed CC BY-SA 4.0:  https://creativecommons.org/licenses/by-sa/4.0/