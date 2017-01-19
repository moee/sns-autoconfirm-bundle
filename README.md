# sns-autoconfirm-bundle
Automatic Confirmation of AWS SNS HTTP(s) subscription messages with Symfony

# Warning: Don't use this in production (yet).

This is a proof of concept in an alpha stage. It is [lacking signature verification](https://github.com/moee/sns-autoconfirm-bundle/issues/1) and so it is not secure. Please feel free to experiement with it and expand it, but **don't use this in production**.

# Usage

## 1. Add the package:

`composer require moee/sns-autoconfirm-bundle`

## 2. Add the Bundle to your application

`new Moee\SnsAutoconfirmBundle\MoeeSnsAutoconfirmBundle()`


## 3. Annotate Endpoints

```
  use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
  use Moee\SnsAutoconfirmBundle\Annotation\SnsEndpoint;
  
  class ExampleController
  {
      /**
       * @Route("/", name="index")
       * @SnsEndpoint
       */
      public function indexAction()
      {
          /* ... */
      }
 } 
```

If you now add this route as SNS HTTP(s) endpoint on an endpoint that is accessible for AWS, then it will automatically confirm the subscription.
