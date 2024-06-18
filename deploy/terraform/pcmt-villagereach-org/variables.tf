variable "aws-region" {
  type        = string
  description = "AWS Region to use"
}

variable "tag-bill-to" {
  type        = string
  description = "Which project to bill the provisioned resources"
  default     = "PCMT"
}

variable "hosted-zone-domain-name" {
  type        = string
  description = "AWS Route53 Hosted Zone Domain Name"
}