<?php

namespace Source\Models;

use Source\Core\Model;

class Category extends Model
{
    
    /**
     * __construct
     *
     */
    public function __construct()
    {
        parent::__construct("categories", ["id"], ["title", "id"]);
    }
    
    /**
     * findByUri
     *
     * @param  string $uri
     * @param  string $columns
     * @return null|Category
     */
    public function findByUri(string $uri, string $columns = "*"): ?Category // TRAZER UMA CATEGORIA ATRAVÉS DE UMA URL - MÉTODO DE ESPECIALIZAÇÃO
    {
        $find = $this->find("uri = :uri", "uri={$uri}", $columns);
        return $find->fetch();
    }
    
    /**
     * save
     *
     * @return bool
     */
    public function save(): bool
    {
        
    }

}