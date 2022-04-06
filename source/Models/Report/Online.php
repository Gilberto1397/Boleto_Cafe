<?php

namespace Source\Models\Report;

use Source\Core\Model;
use Source\Core\Session;

/**
 * Class Online
 * @package Source\Models\Report
 */
class Online extends Model
{
    /** @var int */
    private $sessionTime;

    /**
     * Online constructor.
     * @param int $sessionTime
     */
    public function __construct(int $sessionTime = 20) // a cada 20min irá rastrear inatividades
    {
        $this->sessionTime = $sessionTime; // ajuda a controlar o cookie
        parent::__construct("report_online", ["id"], ["ip", "url", "agent"]);
    }

    /**
     * @param bool $count
     * @return array|int|null
     */
    public function findByActive(bool $count = false) // trará os resultados - passando true ele apenas irá contar o n° de resultados
    {
        $find = $this->find("updated_at >= NOW() - INTERVAL {$this->sessionTime} MINUTE"); // verifica os usuários que não estiverem inativos a mais de 20min
        if ($count) {
            return $find->count();
        }

        return $find->fetch(true);
    }

    /**
     * @param bool $clear
     * @return Online
     */
    public function report(bool $clear = true): Online
    {
        $session = new Session();

        if (!$session->has("online")) { // caso não exista essa sessão
            $this->user = ($session->authUser ?? null); // se houver usuário online (autenticado), colocaremos seu id na tabela para saber quem está logado e acompanhar a estatistica, se não só colocaremos null
            $this->url = (filter_input(INPUT_GET, "route", FILTER_SANITIZE_STRIPPED) ?? "/"); // ou está em um caminho de rota ou está na ágina inicial do sistema
            $this->ip = filter_input(INPUT_SERVER, "REMOTE_ADDR");
            //$this->ip = $_SERVER["REMOTE_ADDR"]; filter_input(INPUT_SERVER, "REMOTE_ADDR"); // CASO A LINHA ANTERIOR NÃO ESTEJA FUNCIONANDO, USAR ESSA
            $this->agent = filter_input(INPUT_SERVER, "HTTP_USER_AGENT");
            //$this->agent = $_SERVER["HTTP_USER_AGENT"]; filter_input(INPUT_SERVER, "HTTP_USER_AGENT");// MESMA EXPLICAÇÃO DO COMENTÁRIO ANTERIOR

            $this->save();
            $session->set("online", $this->id);// $this->id, para consultar o registro e fazer a atualização
            return $this;
        }

        $find = $this->findById($session->online); // consulta na sessão - o id criado na report_online é criado pela sessão
        if (!$find) {
            $session->unset("online");// sessão morre
            return $this;
        }

        $find->user = ($session->authUser ?? null);
        $find->url = (filter_input(INPUT_GET, "route", FILTER_SANITIZE_STRIPPED) ?? "/");
        $find->pages += 1;
        $find->save();

        if ($clear) {
            $this->clear();
        }

        return $this;
    }

    /**
     * CLEAR ONLINE
     */
    private function clear()
    {
        $this->delete("updated_at <= NOW() - INTERVAL {$this->sessionTime} MINUTE", null);
    }

    /**
     * @return bool
     */
    public function save(): bool
    {
        /** Update Access */
        if (!empty($this->id)) {
            $onlineId = $this->id;
            $this->update($this->safe(), "id = :id", "id={$onlineId}");
            if ($this->fail()) {
                $this->message->error("Erro ao atualizar, verifique os dados");
                return false;
            }
        }

        /** Create Access */
        if (empty($this->id)) {
            $onlineId = $this->create($this->safe());
            if ($this->fail()) {
                $this->message->error("Erro ao cadastrar, verifique os dados");
                return false;
            }
        }

        $this->data = $this->findById($onlineId)->data();
        return true;
    }
}