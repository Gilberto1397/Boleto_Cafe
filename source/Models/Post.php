<?php

namespace Source\Models;

use Source\Core\Model;

class Post extends Model
{      
    /**
     * all
     * @var bool
     */
    private $all; // Propriedade que trará todos os posts dando ignore em status and post_at
    
   
    /**
     * __construct
     *
     * @param  bool $all
     * @return void
     */
    public function __construct(bool $all = false) // propriedade setada diretamente do constructo de maneira sem alterar a aplicação
    {
        $this->all = $all;
        parent::__construct("posts", ["id"], ["title", "id", "subtitle", "content"]);
    }
        
    /**
     * find
     *
     * @param  string $terms
     * @param  string $params
     * @param  string $columns
     * @return Model
     */
    public function find(?string $terms = null, ?string $params = null, string $columns = "*"): Model // polimórfica
    {
        if ($this->all) { // a presença da propriedade $all mostra todos os posts   
            $terms = "status = :status AND post_at <= NOW()" . ($terms ? " AND {$terms}" : "");
            $params = "status=post" . ($params ? "&{$params}" : "");
        }
        return parent::find($terms, $params, $columns);
    }
    
    /**
     * findByUri
     *
     * @param  string $uri
     * @param  string $columns
     * @return null|Post
     */
    public function findByUri(string $uri, string $columns = "*"): ?Post // TRAZER UMA CATEGORIA ATRAVÉS DE UMA URL - MÉTODO DE ESPECIALIZAÇÃO
    {
        $find = $this->find("uri = :uri", "uri={$uri}", $columns);
        return $find->fetch();
    }
    
    /**
     * author
     *
     * @return null|User
     */
    public function author(): ?User // como o author representa o id, trataremos ele assim para buscar os dados do autor
    {
        if ($this->author) {
            return (new User())->findById($this->author);
        }
        return null;
    }
    
    /**
     * category
     *
     * @return null|Category
     */
    public function category(): ?Category
    {
        if ($this->category) {
            return (new Category())->findById($this->category);
        }
        return null;
    }

    public function save(): bool //ATUALIZA OU FALHA
    {
        /** Post Update */
        if (!empty($this->id)) {
            $postId = $this->id;

            $this->update($this->safe(), "id = :id", "id={$postId}");
            
            if ($this->fail()) {
                $this->message->error("Erro ao atualizar, verifique os dados");
                return false;
            }
        }

        /** Post create */

        $this->data = $this->findById($postId)->data(); //ACREDITO QUE SEJA PARA APLICAR AS NOVAS INFORMAÇÃOES A VARIÁVEL DATA
        $this->create($this->data);
        return true;
    }
}