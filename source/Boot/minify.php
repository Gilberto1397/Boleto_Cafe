<?php
// como usaremos o minify no ambiente de teste precisamos verificar o ambiente
//require __DIR__ . "/vendor/autoload.php";

if (strpos(url(), "localhost")) {
   
    /**
     * CSS
     */
    //1 adicionou os arquivos a serem minificados

     $minCSS = new \MatthiasMullie\Minify\CSS();
     $minCSS->add(__DIR__ . "/../../shared/styles/styles.css");
     $minCSS->add(__DIR__ . "/../../shared/styles/boot.css");

     //theme CSS
     //pegou os arquivos da pasta e fez foreach verificando para pegar apenas o arquivo css

     $cssDir = scandir(__DIR__ . "/../../themes/" . CONF_VIEW_THEME . "/assets/css");
     foreach ($cssDir as $css) {
         $cssFile = __DIR__ . "/../../themes/" . CONF_VIEW_THEME . "/assets/css/{$css}";
         if (is_file($cssFile) && pathinfo($cssFile)["extension"] == "css") {
             $minCSS->add($cssFile);
         }
     }

     //minificar os arquivos selecionados
     $minCSS->minify(__DIR__ . "/../../themes/" . CONF_VIEW_THEME. "/assets/style.css");

    /**
     * JS
     */

    $minJS = new \MatthiasMullie\Minify\JS();
    $minJS->add(__DIR__ . "/../../shared/scripts/jquery.min.js");
    $minJS->add(__DIR__ . "/../../shared/scripts/jquery.form.js");
    $minJS->add(__DIR__ . "/../../shared/scripts/jquery-ui.js");

    //theme CSS
    //pegou os arquivos da pasta e fez foreach verificando para pegar apenas o arquivo css

    $jsDir = scandir(__DIR__ . "/../../themes/" . CONF_VIEW_THEME . "/assets/js");
    foreach ($jsDir as $js) {
        $jsFile = __DIR__ . "/../../themes/" . CONF_VIEW_THEME . "/assets/js/{$js}";
        if (is_file($jsFile) && pathinfo($jsFile)["extension"] == "js") {
            $minJS->add($jsFile);
        }
    }

    //minificar os arquivos selecionados
    $minJS->minify(__DIR__ . "/../../themes/" . CONF_VIEW_THEME. "/assets/scripts.js");
     
}