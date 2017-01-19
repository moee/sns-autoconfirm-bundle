# symfony-sns-autoconfirm
Automatic Confirmation of AWS SNS HTTP(s) subscription messages with Symfony

# Usage

1. Add the package: `composer require moee/sns-autoconfirm-bundle`
2. Add the Bundle to your application `new Moee\SnsAutoconfirmBundle\MoeeSnsAutoconfirmBundle()`
3. Annotate Endpoints with `Moee\SnsAutoconfirmBundle\Annotation\SnsEndpoint`
