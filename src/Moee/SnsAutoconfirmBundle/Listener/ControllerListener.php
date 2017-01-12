<?php

namespace Moee\SnsAutoconfirmBundle\Listener;

use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Moee\SnsAutoconfirmBundle\Annotation\SnsEndpoint;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Psr\Log\LoggerInterface as Logger;

class ControllerListener
{
    private $annotationReader;
    private $whitelist = [];

    public function __construct(Reader $annotationReader, Logger $log, $whitelist = [])
    {
        $this->annotationReader = $annotationReader;
        $this->log = $log;
        $this->whitelist = $whitelist;
    }


    public function onKernelController(FilterControllerEvent $event)
    {
        if (!$this->isAwsSubscriptionRequest()) {
            return;
        }

        if (!$this->hasSnsEndpointAnnotation($event->getController())) {
            return;
        }

        $this->callAwsSubscriptionUrl(
            $this->getAwsSubscriptionUrl()
        );

    }

    private function hasSnsEndpointAnnotation($controller)
    {
        list($object, $method) = $controller;
        $this->log->debug(
            sprintf(
                "hasSnsEndpointAnnotation(%s->%s)",
                get_class($object),
                $method
            )
        );


        $reflectionClass = new \ReflectionClass(get_class($object));
        $reflectionMethod = $reflectionClass->getMethod($method);
        $allAnnotations = $this->annotationReader->getMethodAnnotations($reflectionMethod);

        foreach ($allAnnotations as $a) {
            if ($a instanceof SnsEndpoint) {
                $this->log->debug("Action contains SnsEndpoint annotation");
                return true;
            }
        }

        $this->log->debug("Action does not contain SnsEndpoint annotation");
        return false;
    }

    private function getAwsSubscriptionUrl()
    {
        $this->log->debug("getAwsSubscriptionUrl()");
        $request = Request::createFromGlobals();
        $payload = json_decode($request->getContent());

        if (!$payload) {
            throw new \RuntimeException("Could not decode payload");
        }

        if (!isset($payload->SubscribeURL)) {
            throw new \RuntimeException("Payload must contain key SubscribeURL");
        }

        foreach ($this->whitelist as $whitelist) {
            if (preg_match("#$whitelist#", $payload->SubscribeURL)) {
                $this->log->debug("{$payload->SubscribeURL} matches $whitelist");
                return $payload->SubscribeURL;
            }
        }

        throw new \RuntimeException("SubscribeURL $payload->SubscribeURL is not whitelisted");

    }

    private function isAwsSubscriptionRequest()
    {
        $this->log->debug("isAwsSubscriptionRequest()");
        $request = Request::createFromGlobals();

        if ($request->getMethod() !== "POST") {
        $this->log->debug("No POST. Return false.");
        $request = Request::createFromGlobals();
            return false;
        }

        if (!($snsMessageType = $request->headers->get('x-amz-sns-message-type'))
            || ($snsMessageType !== "SubscriptionConfirmation"))
        {
            $this->log->debug("x-amz-sns-message-type is not set or not SubscriptionConfirmation.");
            return false;
        }

        $this->log->debug("isAwsSubscriptionRequest(): true");
        return true;
    }

    private function callAwsSubscriptionUrl($subscriptionUrl)
    {
        $this->log->debug(
            sprintf(
                "subscribe(%s)",
                $subscriptionUrl
            )
        );

        $context = stream_context_create(['http' => [
           'ignore_errors' => true,
           'timeout' => 0.1
        ]]);

        try {
            $this->log->debug(sprintf("calling subscription url at %s ", $subscriptionUrl));
            file_get_contents($subscriptionUrl, false, $context);
        } catch (\Exception $e) {
            $this->log->error($e);
            return false;
        }

        $this->log->debug(sprintf("got response %s", $http_response_header[0]));
        return strpos($http_response_header[0], "200") !== false;
    }
}
