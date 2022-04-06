<?php

namespace Source\Models\faq;

use Source\Core\Model;

class Channel extends Model
{
    
    /**
     * __construct
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct("faq_channels", ["id"], ["channel", "description"]);
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