# Infrastructure as Code

This stack includes:
- Terraform for provisioning cloud resources (compute, network, etc)
- Ansible for configuration management (installing docker, PCMT, etc)
- Docker for using Terraform and Ansible (recommended).

## Requirements

- Docker (19.09+)
- Docker-Compose (1.23.2+)
- Terraform (optional, recommended use is through Docker)
- Ansible (optional, recommended use is through Docker)

## Settings

The following environment variables are available:

- `AWS_SHARED_CREDENTIALS_FILE`: Path to the AWS credentials file, defaults
  to `~/.aws/credentials`.
- `SSH_PRIV_KEY_PATH`: path to the SSH key that Ansible will need to configure
  the instance.
- `PCMR_PROFILE`: Profile as documented in PCMT's `settings.env`.
- `PCMT_VER`: The version of PCMT to deploy, this version will need to be
  tagged in Docker Hub.
- `PCMT_ASSET_URL`: The full, public, URL where the project's `tar.gz` file
  is found.
  `PCMT_SECRET_CONF`: The path to the pim's configuration file.
- `PCMT_MYSQL_CREDS_CONF`: The path to the mysql credentials file
- `PCMT_S3_CREDS_CONF`: The path to the AWS S3 credentials file
- `PCMT_FTP_GET_CREDS_CONF`: The path to the FTP-GET credentials file.
- `PCMT_SFTP_PRIVKEY_FILENAME`: The path to the private SFTP key file for 
  FTP-GET
- `PCMT_MYSQL_INIT_CONF`: The path to a SQL file that will be used to initalize
  a fresh install of PCMT.  See [Initalizing a fresh instance][mysql-init].
- `PCMT_MYSQL_ROOT_PASSWORD_CONF`: The path to a file that contains the MySQL
  root password.
- `PCMT_MYSQL_USERNAME_CONF`: The path to a file that contains the MySQL
  username for the Akeneo database.  Must match Akeneo configuration.
- `PCMT_MYSQL_PASSWORD_CONF`: The path to a file that contains the MySQL
  password for the Akeneo database that `PCMT_MYSQL_USERNAME_CONF` contains.
  Must match Akeneo configuration.
- `PCMT_MYSQL_SSH_AUTHORIZED_KEY_CONF`: (optional) The path to a file that
  contains the public ssh key that will be authorized to connect to the instance
  under the user `mysql_ssh`.

[mysql-init]: https://hub.docker.com/_/mysql/

## Quick Start

The recommended useage is through running docker containers which hold PCMT's
Terraform and Ansible configurations.  A helper script `run-docker.sh` is
included to streamline this interaction.

The command's format is: `./run-docker.sh <env-name> <terraform command>`

__Example__: show the plan for the `cd-test` environment:
```bash
export SSH_PRIV_KEY_PATH=$HOME/.ssh/id_rsa
export PCMT_PROFILE=production

./run-docker.sh cd-test plan
```

__Example__: create/update the `cd-test` env, and clear the database:

```bash
export PCMT_VER=0.1.0-snapshot
export PCMT_ASSET_URL="https://gitlab.com/pcmt/pcmt/-/archive/v1.0.0-beta2/pcmt-v1.0.0-beta2.tar.gz"
PCMT_PROFILE=dev ./run-docker.sh cd-test apply -auto-approve
```

__Example__: destroy the `cd-test` environment

```bash
SSH_PRIV_KEY_PATH=$HOME/.ssh/id_rsa ./run-docker.sh cd-test destroy -auto-approve
```

## Development

Development using the built-in tools can be achieved by setting the environment variable `PCMT_TF_DEV` (to anything).
This will then mount the terraform definitions directly into the terraform container, overwriting what's there,
allowing for commands such as `fmt` to modify the source on your computer, and to skip the container re-build step.

Example:
```bash
export PCMT_TF_DEV
./run-docker.sh ...
...
...
unset PCMT_TF_DEV # unset variable to exit development mode
```

---
Copyright (c) 2019, VillageReach.  Licensed CC BY-SA 4.0:  https://creativecommons.org/licenses/by-sa/4.0/
