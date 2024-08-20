<?php

namespace Silex\Bundle\EventListener;

use Silex\EventListener\ConverterListener as BaseListener;
use Symfony\Component\HttpKernel\Event\ControllerEvent;

class ConverterListener extends BaseListener
{
    public function onKernelController(ControllerEvent $event)
    {
        $request = $event->getRequest();
        if (!$request->attributes->has('_route')) {
            return;
        }
        parent::onKernelController($event);
    }
}
