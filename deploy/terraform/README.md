# Terraform

## Usage

1. Usage is recommended through Docker containers.  If anything in here is 
  changed it's recommended to re-build the docker container locally with 
  `./build.sh` or push to CI, wait for the image to be built and published, and 
  then pull with `docker pull pcmt/terraform`.  Better yet, prefer to run these 
  in CD pipelines.
1. See [CD Deploy](../README.md)

## Layout

The terraform layout here borrows heavily from [TERRAFORM, VPC, AND WHY YOU WANT A TFSTATE FILE PER ENV][charity-majors].

[charity-majors]: https://charity.wtf/2016/03/30/terraform-vpc-and-why-you-want-a-tfstate-file-per-env/

## Highlighted practices:

1. Separate tfstate file per environment.
1. Apply changes through CD pipelines.  Use Docker container to `plan` changes.
  This keeps things clean (e.g. use the same terraform version).
1. Shared resources (e.g. VPC, security groups, etc), should go in a separate
  environment.
1. Use remote states, especially to query other environments (e.g. for VPC).
1. Don't forget to `terraform fmt`.

## Quick Reference
Direct usage of Terraform is not advised.  Instead prefer to use the CI/CD jobs.

### Destroy instance: 

```
cd <env>
terraform destroy -target module.pcmt.aws_instance.app
```

### Taint, so that app is re-deployed (destructive):

```
cd <env>
terraform taint module.pcmt.null_resource.deploy-docker
terraform apply
```
Note:  if the module can't be found durring `terraform taint` then run
`terraform state list` and copy the one that ends in `docker-deploy`, using
it in the `terraform taint` command.


## Not used

- Not using `aws_key_pair` as using this resource in TF results in the key-pair
  either forcing the key-pair to be recreated if not manually imported, and
  will also break between different environments where taking one down will
  try to remove the key pair.  Not using until this [bug][aws_key_pair_bug] is 
  resolved.

[aws_key_pair_bug]: https://github.com/terraform-providers/terraform-provider-aws/issues/1092

---
Copyright (c) 2019, VillageReach.  Licensed CC BY-SA 4.0:  https://creativecommons.org/licenses/by-sa/4.0/
