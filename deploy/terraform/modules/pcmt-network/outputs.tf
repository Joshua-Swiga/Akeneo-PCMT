######################################################################
# Copyright (c) 2019, VillageReach
# Licensed under the Non-Profit Open Software License version 3.0.
# SPDX-License-Identifier: NPOSL-3.0
######################################################################

output "vpc-subnet-id" {
  value       = module.vpc.public_subnets[0]
  description = "Id of the first subnet of the VPC"
}

output "security-group-id" {
  value       = aws_security_group.pcmt-web.id
  description = "Id of the security group"
}

output "openhim-security-group-id" {
  value = aws_security_group.openhim-web.id
  description = "id of the security group for an instance with openhim"
}