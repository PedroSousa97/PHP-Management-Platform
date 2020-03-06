<?php
define('DB_HOST', 'localhost:XXXX');
define('DB_USER', 'root');
define('DB_PASSWORD', 'Your Database Password');
define('DB_NAME', 'bitnami_wordpress');


function voltar_atras()
{
    echo "<script type='text/javascript'>document.write(\"<a href='javascript:history.back()' class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>\");</script>
<noscript>
<a href='" . $_SERVER['HTTP_REFERER'] . "‘ class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>
</noscript>";
}

function getConnection()
{
    return mysqli_connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);
}

function get_set($link, $table,$column)  //Função que guarda as opções de um enum de uma determinada coluna da DB num array
{
    $sql = "SHOW COLUMNS FROM $table LIKE '$column'"; //string para retornar tipos de enum de uma coluna (atributo) de uma determinada tabela
    if (!($ret = $link->query($sql)))
        die("Erro: Não foi possível obter a coluna");

    $line = $ret->fetch_assoc();
    $set  = $line['Type']; //Guarda todos os tipos de dados do enum de uma desterminada coluna (de uma certa tabela evidentemente)
    // Remove "set('" no início da string e "');" no fim.
    $set  = substr($set,6,strlen($set)-8);
    // Separa a string e coloca conteúdos separados por vírgulas e coloca-os num array.
    return preg_split("/','/",$set);
}
?>