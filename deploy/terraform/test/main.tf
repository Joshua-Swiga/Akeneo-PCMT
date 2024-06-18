######################################################################
# Copyright (c) 2019, VillageReach
# Licensed under the Non-Profit Open Software License version 3.0.
# SPDX-License-Identifier: NPOSL-3.0
######################################################################

terraform {
  required_version = "~> 0.12.29"
  
  required_providers {
    aws = {
      version = "~> 3.1.0"
    }
  }
  
  backend "s3" {
    bucket = "pcmt-terraform-states"
    key    = "pcmt-test.tf"
    region = "eu-west-1"
  }
}

provider "aws" {
  region = var.aws-region
}

data "terraform_remote_state" "pcmt-network-dev" {
  backend = "s3"
  config = {
    bucket = "pcmt-terraform-states"
    key    = "pcmt-network-dev.tf"
    region = "eu-west-1"
  }
}

data "terraform_remote_state" "pcmt-hosted-zone" {
  backend = "s3"
  config = {
    bucket = "pcmt-terraform-states"
    key    = "pcmt-villagereach-org.tf"
    region = "eu-west-1"
  }
}

module "test" {
  source = "../modules/pcmt"
  providers = {
    aws.compute = aws
    aws.network = aws
  }

  aws-region        = var.aws-region
  ec2-key-pair      = var.ec2-key-pair
  tag-name          = var.tag-name
  tag-type          = var.tag-type
  tag-bill-to       = var.tag-bill-to
  root-volume-size  = var.root-volume-size
  instance-type     = var.instance-type
  app-deploy-group  = var.app-deploy-group
  domain-name       = var.domain-name
  subnet-id         = data.terraform_remote_state.pcmt-network-dev.outputs.vpc-subnet-id
  security-group-id = data.terraform_remote_state.pcmt-network-dev.outputs.security-group-id
  route53-zone-id   = data.terraform_remote_state.pcmt-hosted-zone.outputs.main-hosted-zone-id
}
