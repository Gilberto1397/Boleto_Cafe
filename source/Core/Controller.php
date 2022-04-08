<?php

namespace Source\Core;

use Source\Support\Message;
use Source\Support\Seo;

class Controller
{
    protected $view;
    protected $seo;
    protected $message;

    public function __construct(string $pathToViews = null) // o controller irá precisa de um caminho de view
    {
        $this->view = new View($pathToViews);
        $this->seo = new Seo();
        $this->message = new Message(); // nativo ao controlador
    }

}