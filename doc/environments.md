# Environments

The following environments are for the PCMT's use to test changes, showcase
work and provide demos.

## Login

Every environment should have the following logins:

| User name | Password |
|-----------|----------|
| admin     | Admin123 |

## CD Environments

These may be operated from the [Environments][gitlab-env] screen.

| Name                           | When it changes              | Purpose                      |
|--------------------------------|------------------------------|------------------------------|
| [demo][demo]                   | Nightly (3am UTC)            | Latest stable public demo    |
| [test][test]                   | Every change to `master`     | CD/Test the latest           |
| [showcase][show]               | Manually, at sprint showcase | Sprint Showcases             |
| [beta][beta]                   | Alias for demo               | Alias for demo               |

[gitlab-env]: https://gitlab.com/pcmt/pcmt/environments
[demo]: https://demo.productcatalog.io
[test]: https://test.pcmt.villagereach.org
[show]: https://showcase.pcmt.villagereach.org
[beta]: http://beta.pcmt.villagereach.org