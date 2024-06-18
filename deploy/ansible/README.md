# Ansible

Ansible is used for configuration management (e.g. installing applications with
configuration), after they've been provisioned through Terraform.

## Settings.

See [CD README](../README.md).

## Usage

Preferred usage is through Docker.

1. If you change/add to the Ansible playbook, re-build the docker-container:
`./build.sh`.  Better yet push your changes to CI, wait for the image to be
built, and pull it with `docker pull pcmt/ansible`.
1. Set your settings (see above), note that you'll need to manually add your SSH
  key to a volume named helper.  e.g. 

    ```
    docker volume create pcmt-ssh-key
    docker create --name helper -v pcmt-ssh-key:/tmp/.ssh docker
    docker cp <PATH_TO_YOUR_SSH_KEY> helper:/tmp/.ssh/id_rsa
    docker rm helper
    ```
1. Run `../terraform/script/pcmt-ansible.sh <IP of target> <hostname of target>`
1. When done don't forget to remove the containers, including the one with your
  SSH key created in `pcmt-ssh-key`.


## What it does

1. Install's Docker and docker-compose.
1. Install's pip.
1. Download's PCMT's docker-compose and settings files from the `master` branch.
1. Run's `docker-compose up` to start PCMT.

## To improve

1. Install a specific version, or specific branch.
1. Use a specific configuration, perhaps a local one for testing.

---
Copyright (c) 2019, VillageReach.  Licensed CC BY-SA 4.0:  https://creativecommons.org/licenses/by-sa/4.0/
