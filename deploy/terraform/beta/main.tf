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
    key    = "pcmt-beta.tf"
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

# s3 bucket to serve http redirect
resource "aws_s3_bucket" "redirect" {
  bucket = var.domain-name
  acl    = "public-read"

  tags = {
    Name   = "${var.tag-name}"
    BillTo = "${var.tag-bill-to}"
    Type   = "${var.tag-type}"
  }

  versioning {
    enabled = false
  }

  website {
    redirect_all_requests_to = "https://demo.productcatalog.io"
  }
}

#route 53 to s3 bucket
resource "aws_route53_record" "main" {
  zone_id = data.terraform_remote_state.pcmt-hosted-zone.outputs.main-hosted-zone-id
  name    = var.domain-name
  type    = "A"

  alias {
    name                   = aws_s3_bucket.redirect.website_domain
    zone_id                = aws_s3_bucket.redirect.hosted_zone_id
    evaluate_target_health = false
  }
}