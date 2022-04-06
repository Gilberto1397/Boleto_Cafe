<?php
//CONTROLE DO CAST COM OB_START E END_FLUSH
ob_start();

require __DIR__ . "/vendor/autoload.php";

/**
 * BOOTSTRAP
 * configuração inicial de rotas
 */

 use Source\Core\Session;
 use CoffeeCode\Router\Router;

 $session = new Session();
 $route = new Router(url(), ":");

 /**
  * WEB ROUTES
  */
  $route->namespace("Source\App"); // caminho dos controllers
  $route->get("/", "web:home");
  $route->get("/sobre", "web:about"); //ROTA EM PORTUGUES DEVIDO URL E MÉTODO EM INGLES POR PADRÃO DE PROJETO

  //blog - Como teremos várias páginas blog, iremos agrupa-las
  $route->group("/blog");
  $route->get("/", "web:blog");
  $route->get("/p/{page}", "web:blog"); //VARIÁVEL COM A PAGINAÇÃO DA PAG DE BLOG
  $route->get("/{uri}", "web:blogPost"); // VARIÁVEL DIZENDO QUAL O NOME DO ARTIGO SENDO ACESSADO - URL DO POST
  $route->post("/buscar", "web:blogSearch"); // rota post para enviar os dados a serem pesquisados pelo ajax
  $route->get("/buscar/{terms}/{page}", "web:blogSearch"); // rota que vai receber o RESPONSE
  $route->get("/em/{category}", "web:blogCategory"); // rota que vai receber o RESPONSE
  $route->get("/em/{category}/{page}", "web:blogCategory"); // rota que vai receber o RESPONSE

  //auth
  $route->group(null);
  $route->get("/entrar", "web:login");
  $route->post("/entrar", "web:login");
  $route->get("/cadastrar", "web:register");
  $route->post("/cadastrar", "web:register");
  $route->get("/recuperar", "web:forget");
  $route->post("/recuperar", "web:forget");
  $route->get("/recuperar/{code}", "web:reset"); // recebendo o código para poder enviar o email com o código de reset de senha
  $route->post("/recuperar/resetar", "web:reset");

  //optin
  $route->get("/confirma", "web:confirm");
  $route->get("/obrigado/{email}", "web:success"); // indice de email inserido para poder acessar essa rota de confirmação

  //APP
  $route->group("/app");
  $route->get("", "App:home"); // possível atrito com o redirecionamento do método web->login()
  $route->get("/sair", "App:logout");


  //Services
  $route->group(null);
  $route->get("/termos", "web:terms");

  /**
  * ERROR ROUTES
  */
  $route->namespace("Source\App")->group("/ops"); // BOM MANTER OS NAMESPACES PARA NÃO BUGAR CASO HAJA ROTA DE API ACIMA
  $route->get("/{errcode}", "web:error"); // O {ERRCODE} SERÁ LEVADO PARA O CONTROLADOR ATRAVÉS DA ROTA DE ERROS
 
  /**
  * ROUTE
  *dispara as rotas
  */
  $route->dispatch();
 
  /**
  * ERROR REDIRECT
   *para casos de erro de método ou rota inválida, por exemplo
  */
  if ($route->error()) {
    $route->redirect("/ops/{$route->error()}");
  }

ob_end_flush();