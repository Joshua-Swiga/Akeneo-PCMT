######################################################################
# Copyright (c) 2019, VillageReach
# Licensed under the Non-Profit Open Software License version 3.0.
# SPDX-License-Identifier: NPOSL-3.0
######################################################################

resource "aws_instance" "app" {
  provider               = aws.compute
  ami                    = data.aws_ami.ubuntu-latest.id
  instance_type          = var.instance-type
  key_name               = var.ec2-key-pair
  subnet_id              = var.subnet-id
  vpc_security_group_ids = ["${var.security-group-id}"]

  root_block_device {
    volume_type           = "gp2"
    volume_size           = var.root-volume-size
    delete_on_termination = "true"
  }

  tags = {
    Name        = "${var.tag-name}"
    BillTo      = "${var.tag-bill-to}"
    Type        = "${var.tag-type}"
    DeployGroup = "${var.app-deploy-group}"
  }

  volume_tags = {
    BillTo = "${var.tag-bill-to}"
    Type   = "${var.tag-type}"
  }

  lifecycle {
    ignore_changes = [
      ami,
    ]
  }
}

data "aws_ami" "ubuntu-latest" {
  provider    = aws.compute
  most_recent = true
  owners      = ["099720109477"]

  filter {
    name   = "name"
    values = ["ubuntu/images/hvm-ssd/ubuntu-jammy-*"]
  }

  filter {
    name   = "architecture"
    values = ["x86_64"]
  }
}

resource "null_resource" "deploy-docker" {
  depends_on = [aws_instance.app]
  triggers = {
    build_number = "${timestamp()}"
  }

  connection {
    user = "ubuntu"
    host = aws_instance.app.public_ip
  }

  provisioner "remote-exec" {
    inline = ["ls"]

    connection {
      type = "ssh"
      user = "ubuntu"
    }
  }

  provisioner "local-exec" {
    command = "../script/pcmt-ansible.sh ${aws_instance.app.public_ip} ${var.domain-name}"
  }
}