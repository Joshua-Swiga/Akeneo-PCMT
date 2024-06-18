######################################################################
# Copyright (c) 2019, VillageReach
# Licensed under the Non-Profit Open Software License version 3.0.
# SPDX-License-Identifier: NPOSL-3.0
######################################################################

terraform {
  required_version = "~> 0.12.29"
  
  required_providers {
    aws = {
      version = "~> 2.70"
    }
  }
  
  backend "s3" {
    bucket  = "chai-pcmt-terraform-states"
    key     = "chai-network-useast.tf"
    region  = "us-east-1"
    profile = "chai"
  }
}

provider "aws" {
  region  = var.aws-region
  profile = "chai"
}

module "chai-network-useast" {
  source = "../modules/pcmt-network"

  aws-region  = var.aws-region
  tag-type    = var.tag-type
  tag-bill-to = var.tag-bill-to
}
