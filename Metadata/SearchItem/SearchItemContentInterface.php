<?php

namespace Becklyn\SearchBundle\Metadata\SearchItem;


interface SearchItemContentInterface
{
    /**
     * Returns the name of the accessor to the content field
     *
     * @return string
     */
    public function getAccessorName () : string;


    /**
     * Returns the field name for elastic search
     *
     * @return string
     */
    public function getElasticsearchFieldName () : string;



    /**
     * Returns the accessor type for this field
     *
     * @return string
     */
    public function getAccessorType () : string;
}
