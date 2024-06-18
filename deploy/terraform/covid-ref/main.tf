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
    profile = "chai"
    bucket  = "chai-pcmt-terraform-states"
    key     = "pcmt-covid-ref.tf"
    region  = "us-east-1"
  }
}

provider "aws" {
  alias   = "villagereach"
  profile = "villagereach"
  region  = var.aws-region
}

provider "aws" {
  alias   = "chai"
  profile = "chai"
  region  = var.aws-region
}

data "terraform_remote_state" "pcmt-network" {
  backend = "s3"
  config = {
    profile = "chai"
    bucket  = "chai-pcmt-terraform-states"
    key     = "chai-network-useast.tf"
    region  = "us-east-1"
  }
}

data "terraform_remote_state" "pcmt-hosted-zone" {
  backend = "s3"
  config = {
    profile = "villagereach"
    bucket  = "pcmt-terraform-states"
    key     = "pcmt-productcatalog-io.tf"
    region  = "eu-west-1"
  }
}

module "covid-ref" {
  source = "../modules/pcmt"
  providers = {
    aws.compute = aws.chai
    aws.network = aws.villagereach
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
  subnet-id         = data.terraform_remote_state.pcmt-network.outputs.vpc-subnet-id
  security-group-id = data.terraform_remote_state.pcmt-network.outputs.security-group-id
  route53-zone-id   = data.terraform_remote_state.pcmt-hosted-zone.outputs.main-hosted-zone-id
}
