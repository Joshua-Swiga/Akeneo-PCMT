######################################################################
# Copyright (c) 2019, VillageReach
# Licensed under the Non-Profit Open Software License version 3.0.
# SPDX-License-Identifier: NPOSL-3.0
######################################################################

resource "aws_route53_record" "main" {
  provider = aws.network
  zone_id  = var.route53-zone-id
  name     = var.domain-name
  type     = "A"
  ttl      = 300
  records  = ["${aws_instance.app.public_ip}"]
}