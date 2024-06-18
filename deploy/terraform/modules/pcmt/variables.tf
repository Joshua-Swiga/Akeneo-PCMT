variable "aws-region" {
  type        = string
  description = "AWS Region to use"
}

variable "tag-name" {
  type        = string
  description = "Name of the environment"
}

variable "tag-type" {
  type        = string
  description = "Type of deployment, dev, staging, producting, buildinf, etc"
}

variable "tag-bill-to" {
  type        = string
  description = "Which project to bill the provisioned resources"
  default     = "PCMT"
}

variable "root-volume-size" {
  type        = number
  description = "Size in GB of root volume"
  default     = "20"
}

variable "instance-type" {
  type        = string
  description = "Size in GB of root volume"
  default     = "t3a.small"
}

variable "app-deploy-group" {
  type        = string
  description = "Tags ec2 app instance for use by Ansible Playbooks."
  default     = "docker-hosts"
}

variable "ec2-key-pair" {
  type        = string
  description = "Name of EC2 key-pair for instance"
  default     = "pcmt-ec2"
}

variable "domain-name" {
  type        = string
  description = "AWS Route53 Domain Name"
}

variable "subnet-id" {
  type        = string
  description = "ID of subnet to launch EC2 into"
}

variable "route53-zone-id" {
  type        = string
  description = "ID of zone to insert record into"
}

variable "security-group-id" {
  type        = string
  description = "ID of security group to use for instance"
}

variable "backup-days-till-glacier" {
  type        = number
  description = "Days until backups are moved to glacier"
  default     = 15
}

variable "backup-days-till-expire" {
  type        = number
  description = "Days until backups expire - are deleted"
  default     = 30
}

variable "route53-provider" {
  type        = string
  description = "AWS provider to use for the DNS entry, only needed when >1 aws provider is in use"
  default     = "default"
}