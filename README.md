PHPVine
=======

Just a simple Vine PHP class for accessing their private API.





#Oh, and btw..

It's also worth noting that if you log in with this class, you can set the vine-session-id to prevent yourself from having to login for other calls.
Said session ID may be set by just doing: 

```php
$vine = new Vine();
$vine->session_id = "SESSIONIDHERE";
$vine->..some function/call that requires an authenticated user
```
