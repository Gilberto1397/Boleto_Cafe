<?php

namespace Source\Models\Report;

use Source\Core\Model;
use Source\Core\Session;

/**
 * Class Access
 * @package Source\Models\Report
 */
class Access extends Model
{
    /**
     * Access constructor.
     */
    public function __construct()
    {
        parent::__construct("report_access", ["id"], ["users", "views", "pages"]);
    }

    /**
     * @return Access
     */
    public function report(): Access
    {
        $find = $this->find("DATE(created_at) = DATE(now())")->fetch(); // relatório do dia de hoje / não esquecer do fetch para puxar os dados
        $session = new Session(); // controle de vezes de acesso do user

        if (!$find) {
            $this->users = 1;
            $this->views = 1;
            $this->pages = 1;

            setcookie("access", true, time() + 86400, "/"); // criação do cookie para armazenar o user
            $session->set("access", true); // controle de visitas do user

            $this->save();
            return $this;
        }

        if (!filter_input(INPUT_COOKIE, "access")) {
            $find->users += 1; // mesmo fechando o navegador, cada usuário só vai somar 1 a cada 24h // Uso do $find ao invés do $this para uso do active record e não criar um novo usuário a cada atualização
            setcookie("access", true, time() + 86400, "/");
        }

        if (!$session->has("access")) {
            $find->views += 1; // a views irá subir a cada visita ao site // Uso do $find ao invés do $this para uso do active record e não criar um novo usuário a cada atualização
            $session->set("access", true);
        }

        $find->pages += 1;      // Uso do $find ao invés do $this para uso do active record e não criar um novo usuário a cada atualização
        $find->save();          // Uso do $find ao invés do $this para uso do active record e não criar um novo usuário a cada atualização 
        
        return $this;
    }

    /**
     * @return bool
     */
    public function save(): bool
    {
        /** Update Access */
        if (!empty($this->id)) {
            $accessId = $this->id;
            $this->update($this->safe(), "id = :id", "id={$accessId}");
            if ($this->fail()) {
                $this->message->error("Erro ao atualizar, verifique os dados");
                return false;
            }
        }

        /** Create Access */
        if (empty($this->id)) {
            $accessId = $this->create($this->safe());
            if ($this->fail()) {
                $this->message->error("Erro ao cadastrar, verifique os dados");
                return false;
            }
        }

        $this->data = $this->findById($accessId)->data();
        return true;
    }
}