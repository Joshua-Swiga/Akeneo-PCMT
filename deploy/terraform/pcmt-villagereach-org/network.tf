resource "aws_route53_zone" "main" {
  name = var.hosted-zone-domain-name

  tags = {
    BillTo = "${var.tag-bill-to}"
  }
}