<?php

namespace Source\Models;

use Source\Core\Model;
use Source\Core\View;
use Source\Core\Session;
use Source\Support\Email;


class Auth extends Model
{

    public function __construct()
    {
        parent::__construct("user", ["id"], ["email", "password"]);
    }
    
    public function user(): ?User
    {
        $session = new Session();
        if (!$session->has("authUser")) { // significa que não está autenticado
            return null;                    // com isso ja valida e garante a restrição de acesso nas árae que precisa
        }

        return (new User())->findById($session->authUser);
    }

    public function logout(): void // matar a sessão do usuário
    {
        $session = new Session();
        $session->unset("authUser");
    }

    /**
     * register
     *
     * @param  User $user
     * @return bool
     */
    public function register(User $user): bool 
    {
        if (!$user->save()) { // caso de algum erro ao salvar, o erro ira retornar através do modelo de usuário
            $this->message = $user->message;
        }

        $view = new View(__DIR__ . "/../../shared/views/email"); // indicar apenas a pasta para carregar ela como mensagem
        $message = $view->render("confirm", [
            "first_name" => $user->first_name,
            "confirm_link" => url("/obrigado/" . base64_encode($user->email))
        ]);

        (new Email())->bootstrap(
            "Ative sua conta no " . CONF_SITE_NAME,
            $message,
            $user->email,
            "{$user->first_name} {$user->last_name}"
        )->send();

        return true;
    }

    public function login(string $email, string $password, bool $save = false): bool // o false de save é o checkbox de lembrar dados
    {
        //VERIFICAÇÃO

        if (!is_email($email)) {  // verifica se é um email
            $this->message->warning("o e-mail informado não é válido"); 
            return false;
        }

        if ($save) { // se for true cria o cookie da checkbox que salva os dados
            setcookie("authEmail", $email, time() + 604800, "/"); //criação do cookie por uma semana   
        }else{
            setcookie("authEmail", null, time() - 3600, "/");
        }

        if (!is_passwd($password)) { // posicionado aqui garante que o cookie seja criado corretamente
            $this->message->warning("A senha informada não é válida");
            return false;
        }

        $user = (new User())->findByEmail($email); // Após as verificações ai sim faremos a consulta do usuário
        //$user->findByEmail($email); // buscando o usário pelo email
        if (!$user) { // se não existir o email
            $this->message->error("O e-mail informado não está cadastrado");
            return false;
        }

        if (!passwd_verify($password, $user->password)) { // verificação da senha com hash - se a senha informada não bater com a do user informada
            $this->message->error("A senha informada não confere");
            return false;
        }

        if (passwd_rehash($user->password)) { // verifica necessidade de dar um novo hash - sem retorn, pois independente do rehash o login será feito
            $user->password = $password;
            $user->save();
        }
        // LOGIN
        (new Session())->set("authUser", $user->id); // authUser é o indice de sessão em que vamos armazenar os dados desse usuário
        $this->message->success("Login efetuado com sucesso")->flash(); // método flash para poder exibir no painel ou administrativo
        return true;
        
    }

    public function forget(string $email): bool
    {
        $user = (new User())->findByEmail($email);

        if (!$user) {
            $this->message->warning("O e-email informado não está cadastrado");
            return false;
        }

        $user->forget = md5(uniqid(rand(), true));
        $user->save();

        $view = new view(__DIR__ . "/../../shared/views/email");
        $message = $view->render("forget", [
            "first_name" => $user->first_name,
            "forget_link" => url("/recuperar/{$user->email}|{$user->forget}")
        ]);

        (new Email())->bootstrap(
            "recupere sua senha no " . CONF_SITE_NAME,
            $message,
            $user->email,
            "{$user->first_name} {$user->last_name}"  
        )->send();

        return true;
    }

    public function reset(string $email, string $code, string $password, string $passwordRe): bool
    {
        $user = (new User())->findByEmail($email);

        if (!$user) {
            $this->message->warning("A conta para recuperação não foi encontrada.");
            return false;
        }

        if ($user->forget != $code) {
            $this->message->error("Desculpe, mas o código de verificação não é válido.");
            return false;
        }

        if (!is_passwd($password)) {
            $min = CONF_PASSWD_MIN_LEN;
            $max = CONF_PASSWD_MAX_LEN;
            $this->message->info("Sua senha deve ter entre {$min} e {$max} caracteres.");
            return false;
        }

        if ($password != $passwordRe) {
            $this->message->warning("Você informou duas senhas diferentes.");
            return false;
        }

        $user->password = $password;
        $user->forget = null;
        $user->save();
        return true;
    }

}