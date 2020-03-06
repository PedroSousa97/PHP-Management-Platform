
<?php

require_once("custom/php/common.php");

$link = getConnection();
$queryString = "Select * From attr_unit_type ORDER BY NAME"; //String que irá compor a query que retorna todos os tipos de unidade de atributos



if(is_user_logged_in() && current_user_can( 'manage_unit_types' ))
{
    if(empty($_REQUEST))
    {
        if (!$link){
            echo "<div class='Conect'><h4>Lamentamos, mas a sua conexão à base de dados falhou!</h4></div>";    //Se houver erro na conexão à base de dados
            echo "Error: " .mysqli_error($link);
            exit();
        }

        $result=$link->query($queryString); //Query à base de dados


        if(!$result)
        {
            echo "<div class='InserQuery'><h4>Lamentamos, mas a visualiazaçõa de dados falhou!</h4></div>";
            echo "Error: " . mysqli_error($link);       //Se houver erro na query
            exit();
        }

        if($result->num_rows>0)         //Caso existam unidades a apresentar
        {
            echo "<div class='tableContainer'>
            <table class='TabelaUnit' style='text-align: left; width: 100%;'>
            <tr class='tableheader'><th>ID</th><th>Unidade</th></tr>";
            while ($line = $result->fetch_assoc()) { //Cria array associativo com cada linha resultante da query
                echo "<tr><td>".$line['id']."</td><td>".$line['name']."</td></tr>";
            }
            echo '</table></div>';
        }
        else
        {
            echo "<div class='SemAtrib'><h4>Não há tipos de unidades</h4></div>"; //Caso não existam unidades na base de dados
        }

        echo "<h3 class='IntroHeader' style='text-align: center'>Gestão de unidades - introdução</h3>";

        // HTML formulário simples...
        echo "<div class='row' style='text-align: center'>
        <form id='insert-form' method='POST' action=''>
        <input type='text' placeholder='Nome' name='nome_unidade'><br><br>
        <input name='estado' value='inserir' type='hidden'>
        <input type='submit' name='insertsub' value='Inserir tipo de unidade'>
        </form>
        </div>";

        // Fim HTML do formulário
    }
    else
    {
        echo "<h3 class='InsertHeader'>Gestão de unidades - inserção</h3>";

        $result= mysqli_real_escape_string($link, $_REQUEST['nome_unidade']);  //string resultante sem caracteres especiais
        $query_string_insert= "INSERT INTO attr_unit_type (name) VALUES ('$result')"; //string para query de inserção com o nome passado no form

        if(empty($result))
        {
            echo "<div class='insertion_verify'>";
            echo "<p>O campo Nome é de preenchimento obrigatório </p> <br>";        //Caso o campo não tenha sido preenchido
            voltar_atras();
            exit();
            echo "</div>";
        }
        else
        {
            $result = $link->query($query_string_insert);

            if (!$result){
                echo "<div class='InserQuery'><h4>Lamentamos, mas a sua inserção de dados falhou!</h4></div>";
                echo "Error: ".mysqli_error($link);     //Query falhada
                voltar_atras();
                exit();
            }
            else
            {
                echo "<div class='insert_style'>
                <div class='Inser_success'><h4>Inseriu os dados do novo tipo de unidade com sucesso.</h4></div> <br>
                <p>Clique <a href='gestao-de-unidades'> aqui </a> para continuar.</p>   
                </div>";  //query inserida com sucesso
            }
        }
    }
}
else
{
    echo "<div class='naoaut'><p>Não tem autorização para aceder a esta página</p></div>"; //sem autorização para utilizar esta capability
}
?>


