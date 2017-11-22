UPGRADE from 2.0 to 2.1
=======================

ConfigProvider
--------------

 * Old way to keep configuration from request is still valid. But with new `setConfigProvider` method is now possible
   to load both request and user.

 * Now is possibile to send ConfigProvider to BaseRepository and give him the control of current request and current
   user. This will allow QueryBundle to know user's acl, roles and so on.

Before:

```php
$this->getDoctrine()
     ->getRepository('AppBundle:User')
     ->setRequest($request)
     ->findAllPaginated();
```

After:

```php
$this->getDoctrine()
     ->getRepository('AppBundle:User')
     ->setConfigProvider($this->get('mado.query-bundle.config.provider'))
     ->findAllPaginated();
```
