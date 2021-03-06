<?php
/**
 * DATABASE
 */
define("CONF_DB_HOST", "localhost");
define("CONF_DB_USER", "root");
define("CONF_DB_PASS", "");
define("CONF_DB_NAME", "boletocafe");

/**
 * PROJECT URLs
 */
define("CONF_URL_BASE", "http://www.BoletoCafe.com.br"); // URL DE DEPLOY
define("CONF_URL_TEST", "http://localhost/projetoMVC"); // URL LOCAL
define("CONF_URL_ADMIN",  "/admin");

/**
 * SITE
 */
define("CONF_SITE_NAME", "BoletoCafe");
define("CONF_SITE_TITLE", "Gerencie suas contas com um cafézinho");
define("CONF_SITE_DESC", "Te ajuda a organizar tuas contas!!!");
define("CONF_SITE_LANG", "pt_BR");
define("CONF_SITE_DOMAIN", "upinside.com.br");
define("CONF_SITE_ADDR_NUMBER", "123");
define("CONF_SITE_ADDR_COMPLEMENT", "Algum lugar ai");
define("CONF_SITE_ADDR_CITY", "Xalablau");
define("CONF_SITE_ADDR_STATE", "RS");
define("CONF_SITE_ADDR_ZIPCODE", "40028922");
define("CONF_SITE_ADDR_STREET", "Rua da minha casa");

/**
 * SOCIAL
 */
define("CONF_SOCIAL_TWITTER_CREATOR", "@robsonvleite");
define("CONF_SOCIAL_TWITTER_PUBLISHER", "@robsonvleite");
define("CONF_SOCIAL_FACEBOOK_APP", "626590460695980");
define("CONF_SOCIAL_FACEBOOK_PAGE", "BoletoCafe");
define("CONF_SOCIAL_INSTAGRAM_PAGE", "BoletoCafe");
define("CONF_SOCIAL_YOUTUBE_PAGE", "BoletoCafe");
define("CONF_SOCIAL_FACEBOOK_AUTHOR", "robsonvleiteoficial");
define("CONF_SOCIAL_GOOGLE_PAGE", "107305124528362639842");
define("CONF_SOCIAL_GOOGLE_AUTHOR", "103958419096641225872");

/**
 * DATES
 */
define("CONF_DATE_BR", "d/m/Y H:i:s");
define("CONF_DATE_APP", "Y-m-d H:i:s");

/**
 * PASSWORD
 */
define("CONF_PASSWD_MIN_LEN", 8);
define("CONF_PASSWD_MAX_LEN", 40);
define("CONF_PASSWD_ALGO", PASSWORD_DEFAULT);
define("CONF_PASSWD_OPTION", ["cost" => 10]);

/**
 * MESSAGE
 */
define("CONF_MESSAGE_CLASS", "message");
define("CONF_MESSAGE_INFO", "info icon-info");
define("CONF_MESSAGE_SUCCESS", "success icon-check-square-o");
define("CONF_MESSAGE_WARNING", "warning icon-warning");
define("CONF_MESSAGE_ERROR", "error icon-warning");

/**
 * VIEW
 */
define("CONF_VIEW_PATH", __DIR__ . "/../../shared/views");
define("CONF_VIEW_EXT", "php");
define("CONF_VIEW_THEME", "boletocafeWeb");
define("CONF_VIEW_APP", "boletocafeapp");

/**
 * UPLOAD
 */
define("CONF_UPLOAD_DIR", "storage");
define("CONF_UPLOAD_IMAGE_DIR", "images");
define("CONF_UPLOAD_FILE_DIR", "files");
define("CONF_UPLOAD_MEDIA_DIR", "medias");

/**
 * IMAGES
 */
define("CONF_IMAGE_CACHE", CONF_UPLOAD_DIR . "/" . CONF_UPLOAD_IMAGE_DIR . "/cache");
define("CONF_IMAGE_SIZE", 2000);
define("CONF_IMAGE_QUALITY", ["jpg" => 75, "png" => 5]);

/**
 * MAIL
 */
define("CONF_MAIL_HOST", " smtp-mail.outlook.com");
define("CONF_MAIL_PORT", "587");
define("CONF_MAIL_USER", "gilberto-junior@outlook.com");
define("CONF_MAIL_PASS", "Ga@1234567");
define("CONF_MAIL_SENDER", ["name" => "Gilberto Azevedo", "address" => "gilberto-junior@outlook.com"]);
define("CONF_MAIL_SUPPORT", "suporte@mail.com");
define("CONF_MAIL_OPTION_LANG", "br");
define("CONF_MAIL_OPTION_HTML", true);
define("CONF_MAIL_OPTION_AUTH", true);
define("CONF_MAIL_OPTION_SECURE", "tls");
define("CONF_MAIL_OPTION_CHARSET", "utf-8");