<?php

namespace CanalTP\MttBundle\Form\EventListener;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormError;

class SeasonLockedSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(FormEvents::SUBMIT => 'submit');
    }

    public function submit(FormEvent $event)
    {
        $entity = $event->getData();
        if ($entity->isLocked()) {
            $event->getForm()->addError(new FormError('error.element_locked'));
        }
    }
}