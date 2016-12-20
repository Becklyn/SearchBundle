<?php

namespace Becklyn\SearchBundle\Elasticsearch;

abstract class ElasticsearchRequest
{
    /**
     * @var string
     */
    private $index;


    /**
     * @var string
     */
    private $action;


    /**
     * @var string|null
     */
    private $actionNamespace;



    /**
     * @param string      $index
     * @param string      $action
     * @param string|null $actionNamespace
     */
    protected function __construct (string $index, string $action, string $actionNamespace = null)
    {
        $this->index = $index;
        $this->action = $action;
        $this->actionNamespace = $actionNamespace;
    }


    /**
     * Returns the request data
     *
     * @return array
     */
    public function getData () : array
    {
        return [
            "index" => $this->index,
        ];
    }



    /**
     * The name of the action that is called on the elastic search client
     *
     * @return string
     */
    public function getAction () : string
    {
        return $this->action;
    }



    /**
     * Returns the namespace of the action
     *
     * @return string|null
     */
    public function getActionNamespace ()
    {
        return $this->actionNamespace;
    }



    /**
     * Returns, whether a missing error should be ignored
     *
     * @return bool
     */
    public function ignoreMissing ()
    {
        return false;
    }
}
