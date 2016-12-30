Search Bundle
=============


Installation
------------

1. First install the bundle via composer: 

    ```bash
    composer require becklyn/search-bundle
    ```
    
2. Load the bundle in your `AppKernel`


Configuration
-------------

### Entity annotations


#### Marking a class for indexing

For regular entities just mark the class with the annotation and implement the `SearchableEntityInterface` interface:

```php
use Becklyn\SearchBundle\Entity\SearchableEntityInterface;
use Becklyn\SearchBundle\Mapping as Search;

/**
 * @Search\Item()
 */
class SomeEntity implements SearchableEntityInterface
{
}
```

For localized entities, mark the class and implement the `LocalizedSearchableEntityInterface` interface:

```php
use Becklyn\SearchBundle\Entity\LocalizedSearchableEntityInterface;
use Becklyn\SearchBundle\Mapping as Search;

/**
 * @Search\Item(
 *   index="custom-index-name",
 *   loader="some.service:method",
 * )
 */
class LocalizedSomeEntity implements LocalizedSearchableEntityInterface
{
    /**
     * @return LanguageInterface
     */
    public function getLanguage ()
    {
        
    }
}
```

#### `@Search\Item()` annotation

```php
/**
 * @Search\Item(
 *   index="custom-index-name",
 *   loader="some.service:method",
 * )
 */
```

| Property | Description |
| -------- | ----------- |
| `index`  | The name of the index. If none is given, the name is automatically generated from the FQCN of the class. |
| `loader` | The custom entity loader. Please refer to the chapter about entity loaders to learn more. |


### Marking a field / getter for indexing

```php
use Becklyn\SearchBundle\Mapping as Search;

class SomeEntity
{
    /**
     * @Search\Field()
     */
    private $headline;
    
    /**
     * @Search\Field()
     */
    public function getSomeData ()
    {
    }
}
```

Please note that a `protected` / `private` property `test` needs to have a way to access it, either via getter `getTest()`, isser `isTest()` or hasser `hasTest()`. 


#### `@Search\Field()` annotation

```php
/**
 * @Search\Field(
 *   weight=1,
 *   fragments=null,
 *   format="plain",
 * )
 */
```

| Property | Description |
| -------- | ----------- |
| `weight`  | The value with which this field is boosted when searching. |
| `fragments` | The number of highlight fragments returned when searching. `null` returns the complete text of the hit. |
| `format` | The format of this field. Defines which `format_processor` is used to manipulate the property/getter value. |



### App configuration

The config belongs in `app/config.yml`.

A full configuration example:

```yml
becklyn_search:
    server: "127.0.0.1:9200"
    index: "app-index-{language}"
    format_processors:
        html: "app.content.renderer"            # short version 
        pdf:                                    # full version
            service: "pdf.ocr.text_extractor"
            html_post_process: true
    analyzers:
        analyzer_en:
            tokenizer: lowercase
            filter:
                - standard
                - lowercase
                - stemmer_en
                - asciifolding
                - default_filter_shingle
            char_filter:
                - filter_1
                - filter_2
    filters:
        stemmer_en:
            type: stemmer
            name: english
    unlocalized:
        analyzer: analyzer_default
    localized:
        de:
            analyzer: analyzer_en
```

| Key                  | Description      |
| -------------------- | ---------------- |
| `server`             | (**required**) The DSN to connect to the server. |
| `index`              | (**required**) The pattern with which the index names are generated. The `{language}` placeholder must be included and will be replace with the language code. |
| `format_processors`  | Processors for different text formats. If a field with a given processor is indexed, the processor is called and `html_post_process`ed, if it is selected. This will clean up the HTML (a better version of `strip_tags`), so that the processor can transform the format to HTML (and have it transformed to plain text afterwards). Default for `html_post_process` is `false`. |
| `analyzers`          | A list of custom analyzers. The syntax mirrors the Elasticsearch API. |
| `filters`            | A list of custom filters. The syntax mirrors the Elasticsearch API. |
| `unlocalized`        | The definition for all unlocalized entities. Currently only the selection of a custom analyzer is possible. |
| `localized`          | The configuration for localized entities in every language. Currently only the selection of a custom analyzer is possible. |


Usage
-----

### Searching

Just get the `becklyn.search.client` service and search with it:

```php
$searchResult = $this->get("becklyn.search.client")->search(
    string $query, 
    LanguageInterface $language = null, 
    array $itemClasses = []
);
```

The method has three parameters:

| Argument      | Type     | Description                                                                                                                                   |
| ------------- | ------------------------ | ----------------------------------------------------------------------------------------------------------------------------- |
| `query`       | `string`                 | The query string to search for.                                                                                               |
| `language`    | `LanguageInterface|null` | The language with which the items should be searched. If at least one item is localized, this parameter is **required**.      |
| `itemClasses` | `string[]`               | The FQCN of the entities, that should be searched. If no explicit entity class is given, all (indexed) entities are searched. |



### Indexing

You can either index all entities with the CLI command, let the doctrine event listeners index the entities automatically or index manually.

If you want to index manually, just get the `becklyn.search.client` service:

```php
$this->get("becklyn.search.indexer")->index(SearchableEntityInterface $entity);
```


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
