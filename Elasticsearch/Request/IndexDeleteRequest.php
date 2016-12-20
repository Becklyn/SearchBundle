<?php

namespace Becklyn\SearchBundle\Elasticsearch\Request;

use Becklyn\SearchBundle\Elasticsearch\ElasticsearchRequest;


/**
 * A request that deletes the given index
 */
class IndexDeleteRequest extends ElasticsearchRequest
{
    /**
     * @inheritdoc
     */
    public function __construct ($index)
    {
        parent::__construct($index, "delete", "indices");
    }



    /**
     * @inheritdoc
     */
    public function ignoreMissing ()
    {
        return true;
    }
}
