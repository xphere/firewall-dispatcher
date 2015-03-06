xPheRe/FirewallDispatcher
=========================

Throw an event when entering a Symfony2 firewall. Easy as pie.

Why would I want that?
----------------------

It's not the first time I'm working on a Symfony2 project with more than one
  firewall (e.g. customer and admin). Sometimes you want to know which one the
  current request has entered, maybe to attach custom events or starting services.

Symfony security does not support simple listeners to be attached to firewalls.
  They must implement `Symfony\Component\Security\Http\Firewall\ListenerInterface` and
  attaching them to the flow is not trivial.

That's why this compiler pass attaches a listener to every firewall and dispatches
  an event on firewall activation. [See below](#usage) for details.

Use case
--------

Say you have this security configuration:

```yml
security:
    firewalls:
        admin_area:
            pattern: ^/admin/
            form_login: ~

        store_area:
            anonymous: true
```

You want to add custom behaviour when entering `store_area`, so you start with
  a `kernel.request` listener.

```php

use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class CustomBehaviourForStoreArea
{
    public function onKernelRequest(GetResponseEvent $event)
    {
        $uri = $event->getRequest()->getRequestUri();

        if (preg_match('~^/admin/~', $uri)) {
            // This route belongs to admin_area, do nothing
            return;
        }

        ... do something
    }
}
```

Then, someday you need to add a public firewall where the custom behaviour should 
  not trigger.

```yml
security:
    firewalls:
        public_area:
            pattern:  ^/(?:css|images|js)/
            security: false

        admin_area: ...
```

Then you'll need to add detection also to the `CustomBehaviourForStoreArea`:

```php
        if (preg_match('~^/(css|images|js)/~', $uri)) {
            // This route belongs to public_area, do nothing
            return;
        }
```

This is nonsense, we're duplicating firewall detection, making it error prone.

