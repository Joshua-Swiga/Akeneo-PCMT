resource "aws_route53_zone" "main" {
  name = var.hosted-zone-domain-name

  tags = {
    BillTo = "${var.tag-bill-to}"
  }
}

resource "aws_route53_record" "main" {
  zone_id = aws_route53_zone.main.id
  name    = var.hosted-zone-domain-name
  type    = "A"
  ttl     = 300
  records = ["35.185.44.232"]
}

resource "aws_route53_record" "www" {
  zone_id = aws_route53_zone.main.id
  name    = "www"
  type    = "CNAME"
  ttl     = 300
  records = ["pcmt.gitlab.io."]
}

resource "aws_route53_record" "main-verify" {
  zone_id = aws_route53_zone.main.id
  name    = "_gitlab-pages-verification-code.productcatalog.io"
  type    = "TXT"
  ttl     = 300
  records = ["gitlab-pages-verification-code=34a73686aba7e04b65ee5bf6893634a2"]
}

resource "aws_route53_record" "www-verify" {
  zone_id = aws_route53_zone.main.id
  name    = "_gitlab-pages-verification-code.www.productcatalog.io"
  type    = "TXT"
  ttl     = 300
  records = ["gitlab-pages-verification-code=5fe6181a4855a9d0275e8d949d702e6b"]
}