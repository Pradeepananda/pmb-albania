<?php
// Fichier � param�trer

// url de PMB et la connection � la base de donn�es
$s->http_url="http://192.168.0.35/~ngantier/pmb/sip2.php";
$s->http_url_login="http://192.168.0.35/~ngantier/pmb/main.php";
$s->http_port=80;

$s->http_use_cookie=true;
$s->http_cookie_login=array("user"=>"p","password"=>"","database"=>"bibli");
$s->http_renew_pattern="/class\=\'erreur\'/";

// Pour une connection s�curis�e, mettre $s->http_use_ssl � true et renseigner les fichiers ssl
$s->http_use_ssl=false;
$s->http_ssl_crt="crt.crt";
$s->http_ssl_key="key.key";

// adresse du service de la borne de pr�t
$s->socket_bind_address="127.0.0.1";

// $exec_cmd permet de lancer �ventuellement la borne, une fois la connection effectu�e.
// N�cessite le module PsTools permettant l'execution du processus tout en rendant la main
// $exec_cmd="C:\PsTools\psexec -d \"C:\Program Files\Bibliocheck4Selfservice 4_0_0_84\Bibliocheck4Selfservice.exe\"";
$exec_cmd="";

// $socket_write_type permet de gerer la m�thode de socket_write selon le constructeur de la borne
// Nedap: $socket_write_type=0;
// Ident: $socket_write_type=1;
$socket_write_type=0;

// $protocol_prolonge permet de gerer la m�thode de r�ponse d'une prolongation de pr�t, post� pour que pmb/sip2.php en prenne compte
// Nedap: $protocol_prolonge="";
// Ident: $protocol_prolonge="&protocol_prolonge=1";
$protocol_prolonge="";

/* 
 * Pour lancer le service executer dans une console
 * Sous Linux;
 * 	php socket2http.class.php
 * Sous Windows, installer PHPRuntime;
 * 	PHPRuntime socket2http.class.php
 */
