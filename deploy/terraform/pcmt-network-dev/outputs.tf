######################################################################
# Copyright (c) 2019, VillageReach
# Licensed under the Non-Profit Open Software License version 3.0.
# SPDX-License-Identifier: NPOSL-3.0
######################################################################

output "vpc-subnet-id" {
  value       = module.pcmt-network-dev.vpc-subnet-id
  description = "Id of the first subnet of the VPC"
}

output "security-group-id" {
  value       = module.pcmt-network-dev.security-group-id
  description = "Id of the security group"
}