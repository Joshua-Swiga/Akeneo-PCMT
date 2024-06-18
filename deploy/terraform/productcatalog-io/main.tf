######################################################################
# Copyright (c) 2020, VillageReach
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
    key    = "pcmt-productcatalog-io.tf"
    region = "eu-west-1"
  }
}

provider "aws" {
  region = var.aws-region
}