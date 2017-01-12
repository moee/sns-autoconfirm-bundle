# symfony-sns-autoconfirm
Automatic Confirmation of AWS SNS HTTP(s) subscription messages with Symfony

# Warning: Don't use this in production (yet).

This is a proof of concept in an alpha stage. It is completely lacking authentication. So please feel free to experiement with it and expand it, but **don't use this in production**.

# Usage

## 1. Register the listener

```
parameters:
    sns_listener_whitelist:
      - "^https://sns.[a-z0-9-]{2,20}.amazonaws.com/"

services:
  app.sns_listener:
    class: Moee\SnsAutoconfirmBundle\Listener\ControllerListener
    arguments: ['@annotation_reader', '@logger', "%sns_listener_whitelist%"]
    tags:
      - { name: kernel.event_listener, priority: 10, event: kernel.controller, method:onKernelController }

```

## 2. Add @SnsEndpoint and let the magic happen

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
