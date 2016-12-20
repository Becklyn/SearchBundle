Search Bundle
=============


Entity Loader
-------------

A search item can define a custom entity loader:

```php
use Becklyn\SearchBundle\Mapping as Search;

/**
 * @Search\Item(loader="custom.service:method")
 */
class Example
{
}
```

If a custom loader is defined, the service is fetched and the method called. The loader syntax translates to a call like this:

```php
/**
 * @Search\Item(loader="custom.service:method")
 */ 
 
// --> will load the entities using
$container->get("custom.service")->method(int[] $ids = null);
```
The method must have an optional array parameter.
If the loader is called with `null`, all entities should be returned.
If the loader is called with `int[]`, only the entities with an id in the `int` array have to be loaded.

It is not required to load entities for *all* provided ids, as the missing search results will just be removed from the result list.
