variable "aws-region" {
  type        = string
  description = "AWS Region to use"
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