<?php

namespace Source\App;

use Source\Core\Controller;
use Source\Models\Auth;
use Source\Models\Category;
use Source\Models\faq\Question;
use Source\Support\Pager;
use Source\Models\User;
use Source\Models\Post;
use Source\Models\Report\Access;
use Source\Models\Report\Online;
use Source\Support\Email;
use stdClass;

class web extends Controller
{
    public function __construct()
    {
        // redirect("/ops/manutencao"); DEIXA TODAS AS ROTAS EM MANUTENÇÃO
        parent::__construct(__DIR__ . "/../../themes/" . CONF_VIEW_THEME . "/"); // SERA SETADO O CAMINHO DOS TEMPLATES PARA O CONTROLLER WEB - //Pasta base de template para a aplicação

        /* $email = new Email();
        $email->bootstrap(
            "Teste de fila de email" . time(),
            "Apenas um teste de envio de email",
            "gilberto-junior@outlook.com",
            "Gilberto A.J."
        );

        var_dump($email->sendQueue()); */

        (new Access())->report();
        (new Online())->report();
        
    }

    public function home(): void
    {
        $head = $this->seo->render(
            CONF_SITE_NAME . " - " . CONF_SITE_TITLE,
            CONF_SITE_DESC,
            url(),
            theme("/assets/images/share.jpg") // está é uma imagem padrão a ser mostrada quando compartilhada (ex: whats) nos motores de busca que pode ser trabalhada dinamicamente
        ); // o ultimo prâmetro follow será informado apenas se uma pagina n precise ser encontrada por um motor de busca

        echo $this->view->render("home", [
            "head" => $head,
            "video" => "dzSnOFUyt9Q",
            "blog" => (new Post())->find()->order("post_at DESC")->limit(6)->fetch(true)
        ]);
    }

    public function error(array $data): void
    {
        $error = new stdClass();

        switch ($data["errcode"]) {
            case 'problemas':
                //PRIMEIRO MONTOU O OBJETO DE ERRO
                $error->code = "OPS";
                $error->title = "Estamos enfrentando problemas";
                $error->message = "Parece que deu erro, meu parceiro. Logo vamos resolver isso. Se precisar nos manda um e-mail";
                $error->linkTitle = "Enviar E-mail";
                $error->link = "mailto:" . CONF_MAIL_SUPPORT;

                break;

            case 'manutencao':
                //PRIMEIRO MONTOU O OBJETO DE ERRO           
                $error->code = "OPS";
                $error->title = "Volte depois! Estamos arrumando essa jossa";
                $error->message = "Deu pau no sistema, tamo tentando melhorar, mas ta dificil. Logo tu poder ver as datas dos teus boletos =)";
                $error->linkTitle = null;
                $error->link = null;

                break;

            default:
                //PRIMEIRO MONTOU O OBJETO DE ERRO
                $error->code = $data["errcode"];
                $error->title = "Ooops! Conteúdo indisponível =/";
                $error->message = "Deu mole! O conteúdo não está aqui!";
                $error->linkTitle = "Continue navegando clicando aqui!";
                $error->link = url_back();
                break;
        }



        //CRIOU O HEAD PARA O SEO 
        $head = $this->seo->render(
            "{$error->code} | {$error->title}",
            $error->message,
            url("/ops/{$error->code}"),
            theme("/assets/images/share.jpg"),
            false
        );

        //APLICOU O OBJ DE ERRO E O HEAD NO CONTROLE PARA SER APLICADO NA VIEW DE ERROR
        echo $this->view->render("error", [
            "head" => $head,
            "error" => $error
        ]);
    }

    public function about(): void
    {
        /* $model = (new Post())->findByUri("subindo-ambiente-web-na-amazon-aws-ec2-com-recursos-gratuitos");
        var_dump($model, $model->author(), $model->category()); */

        /*  $post = (new Post())->findById(1);
        $post->uri = "ULTIMO TESTE";
        $post->save();

        var_dump($post); */

        $head = $this->seo->render(
            "Descubra o" . CONF_SITE_NAME . " - " . CONF_SITE_DESC,
            CONF_SITE_DESC,
            url("/sobre"),
            theme("/assets/images/share.jpg") // está é uma imagem padrão a ser mostrada quando compartilhada (ex: whats) nos motores de busca que pode ser trabalhada dinamicamente
        ); // o ultimo prâmetro follow será informado apenas se uma pagina n precise ser encontrada por um motor de busca

        echo $this->view->render("about", [
            "head" => $head,
            "video" => "dzSnOFUyt9Q",
            "faq" => (new Question())->find("channel_id = :id", "id=1", "question, response") // ESPECIFICAR A QUESTION E RESPONSE JA QUE SAO AS UNICAS COISAS QUE QUEREMOS E VAI ALIVIAR PARA O SERVIDOR
                ->order("order_by")->fetch(true) // MOSTRANDO AS PERGUNTAS DE ACORDO COM OS PARÂMETROS DE FIND
        ]);
    }

    public function blog(?array $data)
    {
        $head = $this->seo->render(
            "Blog  - " . CONF_SITE_NAME,
            "Confira aqui como melhor controlar as suas contas",
            url("/blog"),
            theme("/assets/images/share.jpg") // está é uma imagem padrão a ser mostrada quando compartilhada (ex: whats) nos motores de busca que pode ser trabalhada dinamicamente
        ); // o ultimo prâmetro follow será informado apenas se uma pagina n precise ser encontrada por um motor de busca

        $blog = (new Post())->find(); // pegando todos os blogs do db
        $pager = new Pager(url("/blog/p/"));
        $pager->pager($blog->count(), 9, ($data['page'] ?? 1)); // blog->count na paginação

        echo $this->view->render("blog", [
            "head" => $head,
            "blog" => $blog->limit($pager->limit())->offset($pager->offset())->fetch(true),
            "paginator" => $pager->render()
        ]);
    }
    
    /**
     * Site Blog Category
     *
     * @param  array $data
     * @return void
     */
    public function blogCategory(array $data): void
    {
        //primeiro recuperar a categoria acessada pela url se não existir voltamos para a home do blog

        $categoryUri = filter_var($data["category"], FILTER_SANITIZE_STRIPPED);
        $category = (new Category())->findByUri($categoryUri);

        if (!$category) { // categoria inexistente nos volta para a home
            redirect("/blog");
        }

        $blogCategory = (new Post())->find("category = :c", "c={$category->id}");
        $page = (!empty($data["page"]) && filter_var($data["page"], FILTER_VALIDATE_INT) >= 1 ? $data["page"] : 1); // validação
        $pager = new Pager(url("/blog/em/{$category->uri}/")); // navegação - barra no final para inserção da paginação
        $pager->pager($blogCategory->count(), 9, $page);

        $head = $this->seo->render(
            "Artigos em {category->title} - " . CONF_SITE_NAME,
            $category->description,
            url("/blog/em/{category->uri}/{$page}"),
            ($category->cover ? image($category->cover, 1200, 628) : theme("/assets/images/share.jpg"))
        );

        echo $this->view->render("blog", [
            "head" => $head,
            "title" => "Artigos em {$category->title}",
            "desc" => $category->description,
            "blog" => $blogCategory
                ->limit($pager->limit())
                ->offset($pager->offset())
                ->fetch(true),
            "paginator" => $pager->render()                
        ]);
    }

    /**
     * Site Blog Search
     * @param  array $data
     * @return void
     */
    public function blogSearch(array $data): void // array ja que vamos sempre receber dados 
    {
        if (!empty($data["s"])) { // condição de existencia do termo de pesquisa que vai iniciar a função
            $search = filter_var($data["s"], FILTER_SANITIZE_STRIPPED); // PARA EVITAR INFORMAÇÕES MALICIOSAS
            echo json_encode(["redirect" => url("/blog/buscar/{$search}/1")]); // INDICE REDIRECT PRESENTE NO AJAX QUE FARA O REDIRECIONAMENTO
            return;
        }

        if (empty($data["terms"])) {
            redirect("/blog");
        }

        //TRATAMENTO ABAIXO REALIZADO PARA CASO OS TERMOS DE PESQUISA SEJAM INSERIDOS DIRETO NA URL

        $search = filter_var($data["terms"], FILTER_SANITIZE_STRIPPED);
        $page = (filter_var($data["page"], FILTER_VALIDATE_INT) >= 1 ? $data["page"] : 1);

        $head = $this->seo->render(
            "Pesquisa por {$search} - " . CONF_SITE_NAME,
            "Confira os resultados de sua pesquisa para {$search}",
            url("/blog/buscar/{$search}/{$page}"),
            theme("/assets/images/share.jpg")
        );

        $blogSearch = (new Post())->find("MATCH(title, subtitle) AGAINST(:s)", "s={$search}"); //ocorrencia de texto com match() -> neles informamos os campos que queremos encontrar / AGAINST() é o que vou pesquisar

        if (!$blogSearch->count()) {
            echo $this->view->render("blog", [
                "head" => $head,
                "title" => "Pesquisa por:",
                "search" => $search
            ]);
            return;
        }

        $pager = new Pager(url("/blog/buscar/{$search}/")); // NÃO ESQUECER A BARRA DO FINAL PARA O PAGER POR A PAGINAÇÃO
        $pager->pager($blogSearch->count(), 9, $page);

        echo $this->view->render("blog", [
            "head" => $head,
            "title" => "Pesquisa por:",
            "search" => $search,
            "blog" => $blogSearch->limit($pager->limit())->offset($pager->offset())->fetch(true),
            "paginator" => $pager->render()
        ]);
    }

    public function blogPost(array $data)
    {
        $post = (new Post())->findByUri($data["uri"]); // pega a uri passada pela rota

        if (!$post) {
            redirect("/404");
        }

        $post->views += 1;
        $post->save();

        $head = $this->seo->render(
            "{$post->title} " . CONF_SITE_NAME,
            $post->subtitle,
            url("/blog/{$post->uri}"),
            image($post->cover, 1200, 628) // está é uma imagem padrão a ser mostrada quando compartilhada (ex: whats) nos motores de busca que pode ser trabalhada dinamicamente
        ); // o ultimo prâmetro follow será informado apenas se uma pagina n precise ser encontrada por um motor de busca

        echo $this->view->render("blog-post", [
            "head" => $head,
            "post" => $post,
            "related" => (new Post())
                ->find("category = :c AND id != :i", "c={$post->category}&i={$post->id}") //COM A SINTAXE SQL BUSCANDO POSTS RELACIONADOS COM A MESMA CATEGORIA MAS ID DIFERENTE
                ->order("rand()")  // aleatório
                ->limit(3)
                ->fetch(true)
            /* "data" => $this->seo->data() ALTERAÇÃO DA AULA 07 */ // SERVE PARA TRAZER OS DADOS DE REDE DE COMPARTILHAMENTO
            //NO TEMPLATE O SEO JA ESTÁ IMPLEMENTADO E USANDO O MÉTODO DATA() DE SEO, IRÁ OBTER ESSES DADOS PARA MOSTRA-LOS CORRETAMENTE
            //SEM ESSA FUNÇÃO A PARTE DE COMPARTILHAMENTO DO BLOG ACESSADO IRÁ CRASHAR
        ]);
    }

    public function login(?array $data): void // esse dado pode ou não existir
    {
        if (!empty($data["csrf"])) { // verifica a presença do totem csrf
            if (!csrf_verify($data)) {
                $json["message"] = $this->message->error("Erro ao enviar, favor utilize o formulário")->render(); // linha que irá enviar o html do texto para o js
                echo json_encode($json);
                return;
            }

            if (empty($data["email"]) || empty($data["password"])) {
                $json["message"] = $this->message->warning("Informe seu e-mail e senha para entrar")->render(); // linha que irá enviar o html do texto para o js
                echo json_encode($json);
                return;
            }

            $save = (!empty($data["email"]) ? true : false);
            $auth = new Auth();
            $login = $auth->login($data["email"], $data["password"], $save);

            if ($login) {
                $json["redirect"] = url("/app");
            } else {
                $json["message"] = $auth->message()->before("Testando before ")->render();
            }

            echo json_encode($json);
            return;
        }

        $head = $this->seo->render(
            "Entrar " . CONF_SITE_NAME,
            CONF_SITE_DESC,
            url("/entrar"),
            theme("/assets/images/share.jpg") // está é uma imagem padrão a ser mostrada quando compartilhada (ex: whats) nos motores de busca que pode ser trabalhada dinamicamente
        ); // o ultimo prâmetro follow será informado apenas se uma pagina n precise ser encontrada por um motor de busca

        echo $this->view->render("auth-login", [
            "head" => $head,
            "cookie" => filter_input(INPUT_COOKIE, "authEmail") // verificação do check para criação do cookie
        ]);
    }

    public function forget(?array $data)  // pode ser nulo pois temos a rota de acesso via url também
    {
        if (!empty($data["csrf"])) { // verifica a presença do totem csrf
            if (!csrf_verify($data)) {
                $json["message"] = $this->message->error("Erro ao enviar, favor utilize o formulário")->render(); // linha que irá enviar o html do texto para o js
                echo json_encode($json);
                return;
            }

            if (empty($data["email"])) {
                $json["message"] = $this->message->info("Informe seu e-mail para continuar")->render();
                echo json_encode($json);
                return;
            }

            $auth = new Auth();
            if ($auth->forget($data["email"])) {
                $json["message"] = $this->message->success("Acesse seu e-mail para recuperar a senha")->render();
            } else {
                $json["message"] = $auth->message()->render();
            }
             
            echo json_encode($json);
            return;
        }    

        $head = $this->seo->render(
            "Recuperar Senha " . CONF_SITE_NAME,
            CONF_SITE_DESC,
            url("/recuperar"),
            theme("/assets/images/share.jpg") // está é uma imagem padrão a ser mostrada quando compartilhada (ex: whats) nos motores de busca que pode ser trabalhada dinamicamente
        ); // o ultimo prâmetro follow será informado apenas se uma pagina n precise ser encontrada por um motor de busca

        echo $this->view->render("auth-forget", [
            "head" => $head
        ]);
    }
    
    /**
     * Site forget reset
     * @param  array $data
     * @return void
     */
    public function reset(array $data): void
    {
        if (!empty($data['csrf'])) {
            if (!csrf_verify($data)) {
                $json['message'] = $this->message->error("Erro ao enviar, favor use o formulário")->render();
                echo json_encode($json);
                return;
            }

            if (empty($data["password"]) || empty($data["password_re"]) ) {
                $json['message'] = $this->message->info("Informe e repita a senha para continuar")->render();
                echo json_encode($json);
                return;            
            }

            list($email, $code) = explode("|", $data["code"]); // indice 0 do array é o email, indice 1 é o código
            $auth = new Auth();

            if ($auth->reset($email, $code, $data["password"], $data["password_re"])) {
                $this->message->success("Senha alterada com sucesso. Vamos controlar?")->flash();
                $json["redirect"] = url("/entrar");
            } else {
                $json["message"] = $auth->message()->render();
            }

            echo json_encode($json);
            return;
        }   

        $head = $this->seo->render(
            "Crie sua nova senha no " . CONF_SITE_NAME,
            CONF_SITE_DESC, 
            url("/recuperar"),
            theme("/assets/images/share.jpg")
        );

        echo $this->view->render("auth-reset", [
            "head" => $head,
            "code" => $data["code"]
        ]);
    }

    public function register(?array $data): void // como as duas rotas virão para cá, será array
    {
        if (!empty($data["csrf"])) { // utilização do indice csrf devido a sua sempre presença - ESSA ROTA SÓ PODE RETORNAR JSON
            if (!csrf_verify($data)) { // registro
                $json["message"] = $this->message->error("Erro ao enviar, favor utilize o formulário")->render(); // linha que irá enviar o html do texto para o js
                echo json_encode($json);
                return;
            }
            
            if (in_array("", $data)) { // verificação dos campos preenchidos
                $json["message"] = $this->message->info("Informe seus dados para criar sua conta")->render();
                echo json_encode($json);
                return;
            }
            
            $auth = new Auth();
            $user = new User();
            
            $user->bootstrap(
                $data["first_name"],
                $data["last_name"],
                $data["email"],
                $data["password"]
            );
            
            if ($auth->register($user)) {
                $json["redirect"] = url("/confirma"); //redirect la do ajax
            }else{
                $json["message"] = $auth->message()->render(); // mensagem vinda do usuario
            }
            
            echo json_encode($json);
            return;
        }

        $head = $this->seo->render(
            "Cadastrar-se " . CONF_SITE_NAME,
            CONF_SITE_DESC,
            url("/cadastrar"),
            theme("/assets/images/share.jpg") // está é uma imagem padrão a ser mostrada quando compartilhada (ex: whats) nos motores de busca que pode ser trabalhada dinamicamente
        ); // o ultimo prâmetro follow será informado apenas se uma pagina n precise ser encontrada por um motor de busca

        echo $this->view->render("auth-register", [
            "head" => $head
        ]);
    }

    public function confirm()
    {
        $head = $this->seo->render(
            "Confirme seu cadastro " . CONF_SITE_NAME,
            CONF_SITE_DESC,
            url("/confirma"),
            theme("/assets/images/share.jpg") // está é uma imagem padrão a ser mostrada quando compartilhada (ex: whats) nos motores de busca que pode ser trabalhada dinamicamente
        ); // o ultimo prâmetro follow será informado apenas se uma pagina n precise ser encontrada por um motor de busca

        echo $this->view->render("optin", [
            "head" => $head,
            "data" => (object)[ // array alimentavel em que será nativamente um objeto após a resposta do DB
                "title" => "Falta pouco! Confirme seu cadastro",
                "desc" => "Enviamos um link de confirmação para seu e-mail. Acesse e siga as instruções para concluir seu cadastro
                e comece a controlar com o CaféControl",
                "image" => theme("/assets/images/optin-confirm.jpg")
                //AQUI INSERIMOS UM ARRAY NA VARIAVEL DATA E O TRANSFORMAMOS EM OBJ, ROTINA SEMELHANTE AO DOS OUTROS CONTROLADORES QUE ENVOLVEM O DB COMO A VARIAVEL POST
            ]
        ]);
    }

    public function success(array $data): void
    {
        $email = base64_decode($data["email"]);
        $user = (new User())->findByEmail($email); // Busca o usuário através dos dados trazidos pelo $data[email] depois do decode

        if($user && $user->status != "confirmed") {
            $user->status = "confirmed";
            $user->save();
        }

        $head = $this->seo->render(
            "Bem-vindo(a) ao" . CONF_SITE_NAME,
            CONF_SITE_DESC,
            url("/obrigado"),
            theme("/assets/images/share.jpg") // está é uma imagem padrão a ser mostrada quando compartilhada (ex: whats) nos motores de busca que pode ser trabalhada dinamicamente
        ); // o ultimo prâmetro follow será informado apenas se uma pagina n precise ser encontrada por um motor de busca

        echo $this->view->render("optin", [
            "head" => $head,
            "data" => (object)[ // array alimentavel em que será nativamente um objeto após a resposta do DB
                "title" => "Tudo pronto. Você já pode controlar",
                "desc" => "Bem-vindo(a) ao seu controle de contas, vamos tomar um café?",
                "image" => theme("/assets/images/optin-success.jpg"),
                "link" => url("/entrar"),
                "linkTitle" => "Fazer Login"
                //AQUI INSERIMOS UM ARRAY NA VARIAVEL DATA E O TRANSFORMAMOS EM OBJ, ROTINA SEMELHANTE AO DOS OUTROS CONTROLADORES QUE ENVOLVEM O DB COMO A VARIAVEL POST
            ]
        ]);
    }

    public function terms()
    {
        $head = $this->seo->render(
            CONF_SITE_NAME . " - Termos de uso",
            CONF_SITE_DESC,
            url("/termos"),
            theme("/assets/images/share.jpg") // está é uma imagem padrão a ser mostrada quando compartilhada (ex: whats) nos motores de busca que pode ser trabalhada dinamicamente
        ); // o ultimo prâmetro follow será informado apenas se uma pagina n precise ser encontrada por um motor de busca

        echo $this->view->render("terms", [
            "head" => $head
        ]);
    }
}
