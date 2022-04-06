<?php

namespace Source\Models\faq;

use Source\Core\Model;

class Question extends Model
{

    public function __construct()
    {
        parent::__construct("faq_questions", ["id"], ["channel_id", "question", "response"]); // channel_id é a ligação da pergunta a um canal
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