output "main-hosted-zone-id" {
  value       = aws_route53_zone.main.zone_id
  description = "Id of the main hosted zone"
}