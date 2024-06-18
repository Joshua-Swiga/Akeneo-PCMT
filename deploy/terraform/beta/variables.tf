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

variable "domain-name" {
  type        = string
  description = "AWS Route53 Domain Name"
}