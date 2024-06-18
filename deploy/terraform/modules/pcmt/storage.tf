resource "aws_s3_bucket" "backup" {
  provider = aws.compute
  bucket   = var.domain-name
  acl      = "private"

  tags = {
    Name   = "${var.tag-name}"
    BillTo = "${var.tag-bill-to}"
    Type   = "${var.tag-type}"
  }

  versioning {
    enabled = true
  }

  lifecycle_rule {
    enabled = true
    noncurrent_version_transition {
      days          = var.backup-days-till-glacier
      storage_class = "GLACIER"
    }

    noncurrent_version_expiration {
      days = var.backup-days-till-expire
    }
  }
}