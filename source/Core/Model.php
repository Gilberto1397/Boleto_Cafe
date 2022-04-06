<?php

namespace Source\Core;

use Source\Support\Message;

/**
 * FSPHP | Class Model Layer Supertype Pattern
 *
 * @author Robson V. Leite <cursos@upinside.com.br>
 * @package Source\Models
 */
abstract class Model
{
    /** @var object|null|array */
    protected $data;

    /** @var \PDOException|null */
    protected $fail;

    /** @var Message|null */
    protected $message;
    
    /**
     * query
     * @var string
     */
    protected $query; // vai cuidar de criar a query para buscas no DB
    
    /**
     * params
     * @var string
     */
    protected $params; // parâmetros de filtragem
    
    /**
     * order
     *
     * @var string
     */
    protected $order; // filtrar usando o order By
    
    /**
     * limit
     *
     * @var int
     */
    protected $limit;
    
    /**
     * offset
     * @var int
     */
    protected $offset;

    /** @var string $entity database table */
    protected static $entity;

    /** @var array $protected no update or create */
    protected static $protected;

    /** @var array $entity database table */
    protected static $required;

    /**
     * Model constructor.
     * @param string $entity database table name / A TABELA
     * @param array $protected table protected columns / O QUE NÃO PODE SER ALTERADO
     * @param array $required table required columns / CAMPOS OBRIGATÓRIOS
     */
    public function __construct(string $entity, array $protected, array $required)
    {
        self::$entity = $entity;
        self::$protected = array_merge($protected, ['created_at', "updated_at"]);
        self::$required = $required;

        $this->message = new Message();
    }

    /**
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        if (empty($this->data)) {
            $this->data = new \stdClass();
        }

        $this->data->$name = $value;
    }

    /**
     * @param $name
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->data->$name);
    }

    /**
     * @param $name
     * @return null
     */
    public function __get($name)
    {
        return ($this->data->$name ?? null);
    }

    /**
     * @return null|object|array
     */
    public function data(): ?object
    {
        return $this->data;
    }

    /**
     * @return \PDOException
     */
    public function fail(): ?\PDOException
    {
        return $this->fail;
    }

    /**
     * @return Message|null
     */
    public function message(): ?Message
    {
        return $this->message;
    }
    
    /**
     * find
     *
     * @param  null|string $terms
     * @param  null|string $params
     * @param  string $columns
     * @return Model|mixed
     */
    public function find(?string $terms = null, ?string $params = null, string $columns = "*") // traz os resultados com ou sem a condição de where
    { // o retorno será dito pela doc, já que o método será usado em subclasses que n devem ter esse retorno
        if ($terms) {
            $this->query = "SELECT {$columns} FROM " . static::$entity . " WHERE {$terms}";
            parse_str($params, $this->params);
            return $this;
        }

        $this->query = "SELECT {$columns} FROM " . static::$entity;
        return $this;
    }

    /**
     * @param int $id
     * @param string $columns
     * @return null|Model|mixed
     */
    public function findById(int $id, string $columns = "*"): ?Model // retorna diretamente o usuário filtrado
    {
        $find = $this->find("id = :id", "id={$id}", $columns);
        return $find->fetch();
    }

    /**
     * @param string $email
     * @param string $columns
     * @return null|User
     */
    
    /**
     * order
     * @param  string $columnOrder
     * @return Model
     */
    public function order(string $columnOrder): Model // COMO O RETORNO É A PRÓPRIA CLASSE DEVEMOS DAR RETURN $THIS
    {
        $this->order = " ORDER BY {$columnOrder}"; //Com espaço para respeitar a query
        return $this;
    }
    
    /**
     * limit
     * @param  int $limit
     * @return Model
     */
    public function limit(int $limit): Model
    {
        $this->limit = " LIMIT {$limit}";
        return $this;
    }
    
    /**
     * offset
     * @param  int $offset
     * @return Model
     */
    public function offset(int $offset): Model
    {
        $this->offset = " OFFSET {$offset}";
        return $this;
    }
    
    /**
     * fetch
     *
     * @param  bool $all
     * @return null|array|mixed|Model 
     */
    public function fetch(bool $all = false)  // vai executar a query - vai trazer um resultado unico ou vários
    {
        try {
            $stmt = Connect::getInstance()->prepare($this->query . $this->order . $this->limit . $this->offset);
            $stmt->execute($this->params); // lendo os dados do array
            
            if (!$stmt->rowCount()) {
                return null;
            }

            if ($all) {
                return $stmt->fetchAll(\PDO::FETCH_CLASS, static::class); // Traz um array de obj da classe
            }
            return $stmt->fetchObject(static::class); // traz UM obj da classe q utilizar esse método

        } catch (\PDOException $exception) {
            $this->fail = $exception;
            return null;
        }
    }
    
    /**
     * count
     *
     * @param  string $key
     * @return int
     */
    public function count(string $key = "id"): int //  cont os resultados
    {   //USADO DENTRO DO FIND PARA TRAZER O NUMERO DE LINHAS RESULTANTES AO INVÉS DOS RESULTADOS EM SI COMO O FETCH
        $stmt =Connect::getInstance()->prepare($this->query);
        $stmt->execute($this->params);
        return $stmt->rowCount();
    }

        
    /**
     * create
     *
     * @param  array $data
     * @return int
     */
    protected function create(array $data): ?int
    {
        try {
            $columns = implode(", ", array_keys($data));
            $values = ":" . implode(", :", array_keys($data));

            $stmt = Connect::getInstance()->prepare("INSERT INTO ". static::$entity ."({$columns}) VALUES ({$values})");
            $stmt->execute($this->filter($data));

            return Connect::getInstance()->lastInsertId();
        } catch (\PDOException $exception) {
            $this->fail = $exception;
            return null;
        }
    }

        
    /**
     * update
     *
     * @param  array $data
     * @param  string $terms
     * @param  string $params
     * @return int
     */
    protected function update(array $data, string $terms, string $params): ?int
    {
        try {
            $dateSet = [];
            foreach ($data as $bind => $value) {
                $dateSet[] = "{$bind} = :{$bind}";
            }
            $dateSet = implode(", ", $dateSet);
            parse_str($params, $params);

            $stmt = Connect::getInstance()->prepare("UPDATE " . static::$entity ." SET {$dateSet} WHERE {$terms}");
            $stmt->execute($this->filter(array_merge($data, $params)));
            return ($stmt->rowCount() ?? 1);
        } catch (\PDOException $exception) {
            $this->fail = $exception;
            return null;
        }
    }

    
        
    /**
     * delete
     *
     * @param  string $terms
     * @param  null|string $params
     * @return bool
     */
    public function delete(string $terms, ?string $params): bool // AGORA É PUBLIC POIS N TEREMOS UM MÉTODO DESTROY - O TIPO NULL ACRESCENTADO É PARA OBRIGAR A PASSAR UM PARÂMETRO PARA NÃO LIMPAR GERAL
    {   // AGORA O DELETE SÓ PRECISA IDENTIFICAR O REGISTRO 
        try {
            $stmt = Connect::getInstance()->prepare("DELETE FROM ". static::$entity ."  WHERE {$terms}");
            if ($params) {
                parse_str($params, $params);
                $stmt->execute($params);
                return true;
            }
            $stmt->execute();
            return true;

        } catch (\PDOException $exception) {
            $this->fail = $exception;
            return false;
        }
    }

    /**
     * @return bool
     */
    public function destroy(): bool // método extendendo o método delete
    {
        if (empty($this->id)) {
            return false;
        }

        $destroy = $this->delete("id = :id", "id={$this->id}");
        return $destroy;
    }

    /**
     * @return array|null
     */
    protected function safe(): ?array
    {
        $safe = (array)$this->data;
        foreach (static::$protected as $unset) {
            unset($safe[$unset]);
        }
        return $safe;
    }

    /**
     * @param array $data
     * @return array|null
     */
    private function filter(array $data): ?array
    {
        $filter = [];
        foreach ($data as $key => $value) {
            $filter[$key] = (is_null($value) ? null : filter_var($value, FILTER_DEFAULT)); // FILTER DEFAULT, POIS A RESPONSABILIDADE DE FILTRAR ALGO A MAIS SERÁ DE QUEM EXTENDER O MODEL
        }
        return $filter;
    }

    /**
     * @return bool
     */
    protected function required(): bool
    {
        $data = (array)$this->data();
        foreach (static::$required as $field) {
            if (empty($data[$field])) {
                return false;
            }
        }
        return true;
    }
}