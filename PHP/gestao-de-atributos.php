<?php

require_once("custom/php/common.php");

$link = getConnection();

$queryString = "Select * From object ORDER BY object.name"; //Seleciona todos os atributos da tabela object




if(is_user_logged_in() && current_user_can( 'manage_unit_types' ))
{
    if(empty($_REQUEST))
    {
        if (!$link){

            echo "<div class='Conect'><h4>Lamentamos, mas a sua conexão à base de dados falhou!</h4></div>";
            echo "Error: " .mysqli_error($link);
            exit();     //Se houver erro na conexão à base de dados
        }

        $result=$link->query($queryString);     //Query à base de dados


        if(!$result)
        {
            echo "<div class='quererror'>";
            echo "<div class='InserQuery'><h4>Lamentamos, mas a visualiazaçõa de dados falhou!</h4></div>";
            echo "Error: " . mysqli_error($link);
            echo "</div>";              //Se houver erro na query
            exit();
        }

        if($result->num_rows>0) //Se existem objetos, então print table
        {
            echo "<div class='tableContainer2'>";
            echo "<table class='mytable' style='text-align: left; width: 100%;' border='1' cellpadding='2' cellspacing='2'>
            <tr class='tableheader'><th>Objeto</th><th>ID</th><th>Nome do Atributo</th><th>Tipo de Valor</th><th>Nome do Campo no Formulário</th>
            <th>Tipo do Campo no Formulário</th><th>Tipo de Unidade</th><th>Ordem do Campo no Formulário</th>
            <th>Tamanho do Campo no Formulário</th><th>Obrigatório</th><th>Estado</th><th>Ação</th></tr>";
            while($row=$result->fetch_assoc()):
                $objid = intval($row['id']);   //Para cada row, guardar o object.id

                $sql2 = "SELECT attribute.id,attribute.name, value_type, form_field_name, form_field_type, unit_type_id, form_field_order, form_field_size, mandatory, attribute.state FROM attribute WHERE attribute.obj_id= $objid ORDER BY attribute.name";
                $result2 = $link->query($sql2); //Para cada objeto selecionar todos os atributos que correspondem a si, ou seja com o obj_id = objid
                $count_result2 = mysqli_num_rows($result2); //Contar número de atributos para associar ao rawspan dinâmico
                if($count_result2>0) //Se existirem atributos, criar rawspan dinâmico
                {
                    echo "<tr><td colspan='1' rowspan='$count_result2'>" . $row['name'] . "</td>"; //Nome do objeto na primeira coluna
                    while ($row2 = $result2->fetch_assoc()):
                        $unitid = intval($row2['unit_type_id']);  //Para cada atributo do objeto guardar o unit_type_id
                        if (!$row2['unit_type_id'])  //Caso do unit_type_id ser nulo, no campo do tipo de unidade será colocado "-"
                        {   if($row2['mandatory']==1) //Caso em que o mandatory = 1, ou seja, na tabela terá que estar SIM neste campo
                            {
                                echo "<td>" . $row2['id'] . "<td>" . $row2['name'] . "<td>" . $row2['value_type'] . "<td>" . $row2['form_field_name'] . "<td>" . $row2['form_field_type'] . "<td>" . "-" . "<td>" . $row2['form_field_order'] . "<td>" . $row2['form_field_size'] . "<td>" . "Sim" . "<td>" . $row2['state'] . "<td>" . "[editar] [desativar]" . "</td></tr>";
                            }
                            if ($row2['mandatory']==0) //Caso em que o mandatory = 0, ou seja, na tabela terá que estar NÃO neste campo
                            {
                                echo "<td>" . $row2['id'] . "<td>" . $row2['name'] . "<td>" . $row2['value_type'] . "<td>" . $row2['form_field_name'] . "<td>" . $row2['form_field_type'] . "<td>" . "-" . "<td>" . $row2['form_field_order'] . "<td>" . $row2['form_field_size'] . "<td>" . "Não" . "<td>" . $row2['state'] . "<td>" . "[editar] [desativar]" . "</td></tr>";
                            }
                        }
                        else //Caso do unit_type_id ser não nulo, no campo do tipo de unidade o nome da unidade correspondente
                        {
                            $sql3 = "SELECT * FROM attr_unit_type WHERE id= $unitid";  //Para cada atributo seleciona e guarda os dados do tipo de unidade
                            $result3 = $link->query($sql3);
                            $value = $result3->fetch_assoc();  //Guarda os valores id e nome num array associativo. $value['name'] setá usado para realizar o print do tipo de unidade
                            if($row2['mandatory']==1) //Caso em que o mandatory = 1, ou seja, na tabela terá que estar SIM neste campo
                            {
                                echo "<td>" . $row2['id'] . "<td>" . $row2['name'] . "<td>" . $row2['value_type'] . "<td>" . $row2['form_field_name'] . "<td>" . $row2['form_field_type'] . "<td>" . $value['name'] . "<td>" . $row2['form_field_order'] . "<td>" . $row2['form_field_size'] . "<td>" . "Sim" . "<td>" . $row2['state'] . "<td>" . "[editar] [desativar]" . "</td></tr>";
                            }
                            if ($row2['mandatory']==0) //Caso em que o mandatory = 0, ou seja, na tabela terá que estar NÃO neste campo
                            {
                                echo "<td>" . $row2['id'] . "<td>" . $row2['name'] . "<td>" . $row2['value_type'] . "<td>" . $row2['form_field_name'] . "<td>" . $row2['form_field_type'] . "<td>" . $value['name'] . "<td>" . $row2['form_field_order'] . "<td>" . $row2['form_field_size'] . "<td>" . "Não" . "<td>" . $row2['state'] . "<td>" . "[editar] [desativar]" . "</td></tr>";
                            }
                        }
                        endwhile; // endwhile para cada loop
                }
                else
                {
                    //Caso em que o objeto não tem atributos associados. Realiza-se um colspan de 12, unindo assim todas as células da tabela para esta linha
                    echo "<tr><td colspan='12'>" . $row['name'] . "</td></tr>";
                }
            endwhile; // endwhile para o main loop
            echo "</table></div>";
        }
        else
        {
            echo "<div class='SemAtrib'><h4>Não há propriedades especificadas</h4></div>"; //Se não existem objetos
        }

        $tipoval=get_set( $link, attribute, value_type);    //valores enum do value_type
        $tipovalnum = count($tipoval);                          //conta número de valores de enum
        $tipocamp=get_set( $link, attribute, form_field_type); //valores enum do form_field_type
        $tipocampnum = count($tipocamp);                        //conta número de valores de enum
        $queryStringObject = "Select name From object ORDER BY NAME";
        $result2=$link->query($queryStringObject);
        $queryStringObject2 = "Select name From object ORDER BY NAME";  //String e querys para objetos e unidades
        $result2_2=$link->query($queryStringObject2);
        $queryStringUnit = "Select name From attr_unit_type ORDER BY NAME";
        $result3=$link->query($queryStringUnit);
        echo "<h3 class='IntroHeader' style='text-align: center'>Gestão de Atributos - Introdução</h3>";
        echo "<div class='row' style='text-align: center'>
        <form id='insert-form' method='POST' action=''>
        <input type='text' placeholder='Nome do Atributo' name='nome_atributo'><br><br>
        <div id='tipoval'><p style='text-align: center'>Tipo de valor:</p>";
        //enquanto houverem valores de enum cria radios
        for ($x = 0; $x <$tipovalnum; $x++) {
            echo "<input type='radio' name='tipo_valor' value='$tipoval[$x]'>$tipoval[$x] <br>";
        }
        echo"</div><br><div id='objeto'><p style='text-align: center'>Objeto do atributo:</p>";
        //Opções com todos os nomes de objetos
          if($result2->num_rows>0){
              echo "<select name='obetoatr'>";
              while($row=$result2->fetch_assoc()):
                  $objname = $row['name'];
                  echo "<option value='$objname'>$objname</option>";
              endwhile;
              echo"</select>";
          }
          else{
                echo "<p>Não existem objetos na base de dados</p>";
          }
        echo"</div><br><div id='tipocamp'><p style='text-align: center'>Tipo do campo do formulário:</p>";
        for ($x = 0; $x <$tipocampnum; $x++) {
            echo "<input type='radio' name='tipo_campo' value='$tipocamp[$x]'>$tipocamp[$x] <br>";
        }
        //enquanto houverem valores de enum cria radios
        echo"</div><br><div id='unit'><p style='text-align: center'>Tipo de Unidade:</p>";
        //Opções com todas as unidades
        if($result3->num_rows>0){
            echo "<select name='unidade'>";
            echo "<option value='NULL'></option>";
            while($row=$result3->fetch_assoc()):
                $unitname = $row['name'];
                echo "<option value='$unitname'>$unitname</option>";
            endwhile;
            echo"</select>";
        }
        else{
            echo "<p>Não existem unidades na base de dados</p>";
        }
        echo"</div><br>
        <input type='text' id='ordem' placeholder='Ordem do campo no formulário' name='ord_camp_form'><br><br>
        <input type='text' id='tamanho' placeholder='Tamanho do campo no formulário' name='tam_camp_form'><br><br>
        <div id='obriga'><p style='text-align: center'>Obrigatório:</p>
        <input type='radio' name='Obrigatório' value='Sim'>Sim<br>
        <input type='radio' name='Obrigatório' value='Não'>Não</div><br>
        <div id='objetoref'><p style='text-align: center'>Objeto referenciado por este atributo:</p>";
        //Opções com todos os nomes de objetos
        if($result2_2->num_rows>0){
            echo "<select name='obetoref'>";
            while($row=$result2_2->fetch_assoc()):
                $objname = $row['name'];
                echo "<option value='$objname'>$objname</option>";
            endwhile;
            echo"</select>";
        }
        else{
            echo "<p>Não existem objetos na base de dados</p>";
        }
        echo "</div><br>";
        echo "<input name='estado' value='inserir' type='hidden'>
        <input type='submit' name='insertsattr' value='Inserir atributo'>
        </form>
        </div>";
    }
    else
    {
        echo "<h3 class='InsertHeader'>Gestão de atributos - inserção</h3>";

        //strings resultantes sem caracteres especiais

        $result1= mysqli_real_escape_string($link, $_REQUEST['nome_atributo']);
        $result2= mysqli_real_escape_string($link, $_REQUEST['tipo_valor']);
        $result3= mysqli_real_escape_string($link, $_REQUEST['obetoatr']);
        $result4= mysqli_real_escape_string($link, $_REQUEST['tipo_campo']);
        $result5= mysqli_real_escape_string($link, $_REQUEST['unidade']);
        $result6= mysqli_real_escape_string($link, $_REQUEST['ord_camp_form']);
        $result7= mysqli_real_escape_string($link, $_REQUEST['tam_camp_form']);
        $result8= mysqli_real_escape_string($link, $_REQUEST['Obrigatório']);
        $result9= mysqli_real_escape_string($link, $_REQUEST['obetoref']);

        //Verificações para campos obrigatórios

        if(empty($result1))
        {
            echo "<div class='insertion_verify'>";
            echo "<p>O campo Nome é de preenchimento obrigatório </p> <br>";
            voltar_atras();
            exit();
            echo "</div>";
        }
        if(empty($result2))
        {
            echo "<div class='insertion_verify'>";
            echo "<p>O campo Tipo de valor é de seleção obrigtória </p> <br>";
            voltar_atras();
            exit();
            echo "</div>";
        }
        if(empty($result3))
        {
            echo "<div class='insertion_verify'>";
            echo "<p>O campo Objeto do atributo é de seleção obrigtória </p> <br>";
            voltar_atras();
            exit();
            echo "</div>";
        }
        if(empty($result4))
        {
            echo "<div class='insertion_verify'>";
            echo "<p>O campo Tipo do campo do formulário é de seleção obrigtória </p> <br>";
            voltar_atras();
            exit();
            echo "</div>";
        }
        if(empty($result6))
        {
            echo "<div class='insertion_verify'>";
            echo "<p>O campo Ordem do campo no formulário é de preenchimento obrigtória </p> <br>";
            voltar_atras();
            exit();
            echo "</div>";
        }
        if(empty($result8))
        {
            echo "<div class='insertion_verify'>";
            echo "<p>O campo Obrigatório é de preenchimento obrigtória </p> <br>";
            voltar_atras();
            exit();
            echo "</div>";
        }


        else
        {
            //Verificação para padrão aaxbb para o tamanho do campo quando o tipo é textbox

            if($result4 == 'textbox')
            {
                if(!preg_match('/[0-9][0-9].*[x].*[0-9][0-9]/', $result7)){
                    echo "<div class='insertion_verify'>";
                    echo "<p>Quando o Tipo do campo do formulário é textbox,<br> o Tamanho do campo no formulário tem de ser do tipo:<br>aaxbb em que aa é o número de colunas e bb o número de linhas da caixa de texto</p> <br>";
                    voltar_atras();
                    exit();
                    echo "</div>";
                }
            }

            //Verificação para tamanho do campo obrigatório quando o tipo é textbox ou text

            if($result4 == 'text' OR $result4 == 'textbox')
            {
                if(empty($result7)){
                    echo "<div class='insertion_verify'>";
                    echo "<p>Quando o Tipo do campo do formulário é text ou textbox,<br> o Tamanho do campo no formulário é de preenchimento obrigtório</p> <br>";
                    voltar_atras();
                    exit();
                    echo "</div>";
                }
            }
            if($result6) {

                //Verifica se é valor numérico

                if (!is_numeric($result6)) {
                    echo "<div class='insertion_verify'>";
                    echo "<p>O valor inserido na Ordem do campo no formulário tem que ser um inteiro</p> <br>";
                    voltar_atras();
                    exit();
                    echo "</div>";
                }
            }
            if($result7)
            {
                //Verifica se é valor numérico

                if(!is_numeric($result7))
                {
                    echo "<div class='insertion_verify'>";
                    echo "<p>O valor inserido no Tamanho do campo no formulário tem que ser um inteiro</p> <br>";
                    voltar_atras();
                    exit();
                    echo "</div>";
                }
            }

            //Extrai ids através dos nomes introduzidos no formulário, unidades e objetos

            $query_unit_type = "SELECT id FROM attr_unit_type WHERE name LIKE '%$result5%'";
            $resultunit = $link->query($query_unit_type);
            $valueid1 = mysqli_fetch_object($resultunit);
            $valueid = intval($valueid1->id);;

            $query_obj_id = "SELECT id FROM object WHERE object.name LIKE '%$result3%'";
            $resultobj = $link->query($query_obj_id);
            $valueobjid1 = mysqli_fetch_object($resultobj);
            $valueobjid = intval($valueobjid1->id);

            $query_objfk_id = "SELECT id FROM object WHERE object.name LIKE '%$result9%'";
            $resultobjfk = $link->query($query_objfk_id);
            $valueobjfkid1 = mysqli_fetch_object($resultobjfk);
            $valueobjfkid = intval($valueobjfkid1->id);

            //Criação do formname temporário. 3 primeiros chars do objeto, e nome do atributo em
            //asci com a função dada no enunciado

            $formname1 = substr($result3, 0, 3);
            $formname2 = preg_replace('/[^a-z0-9_ ]/i', '', $result1);
            $formnametemporary=$formname1."-id-".$formname2;

            if ($result8 == 'Sim')
            {
                $mandatory = 1;
            }
            if ($result8 == 'Não')
            {
                $mandatory = 0;
            }
            if ($valueid>0)
            {
                $query_string_insert = "INSERT INTO attribute (name,value_type,obj_id,form_field_type,unit_type_id,form_field_name,form_field_order,form_field_size,mandatory,obj_fk_id,state) 
                                    VALUES ('$result1','$result2','$valueobjid','$result4','$valueid','$formnametemporary','$result6','$result7','$mandatory','$valueobjfkid','active')";
                $results = $link->query($query_string_insert);
            }
            if ($valueid==0)
            {
                $query_string_insert = "INSERT INTO attribute (name,value_type,obj_id,form_field_type,form_field_name,form_field_order,form_field_size,mandatory,obj_fk_id,state) 
                                    VALUES ('$result1','$result2','$valueobjid','$result4','$formnametemporary','$result6','$result7','$mandatory','$valueobjfkid','active')";
                $results = $link->query($query_string_insert);
            }

            if (empty($results)){
                echo "<div class='InserQuery'><h4>Lamentamos, mas a sua inserção de dados falhou!</h4></div>";
                echo "Error: ".mysqli_error($link);
                voltar_atras();
                exit();
            }
            else
            {
                $attr_id = intval($link->insert_id);  //id atribuido ao atributo do ultimo insert
                $formnamefinal=$formname1."-".$attr_id."-".$formname2; //Final formname com id atualizado

                $update_string = "UPDATE attribute SET form_field_name = '$formnamefinal' WHERE attribute.id=$attr_id";
                $update = $link->query($update_string);

                if (empty($update))
                {   echo "aqui";
                    echo "<div class='InserQuery'><h4>Lamentamos, mas a sua inserção de dados falhou!</h4></div>";
                    echo "Error: " . mysqli_error($link);
                    voltar_atras();
                    exit();
                }
                else
                {
                    echo "<div class='insert_style'>
                    <div class='Inser_success'><h4>Inseriu os dados do novo tipo de atributo com sucesso.</h4></div> <br>
                    <p>Clique <a href='gestao-de-atributos'> aqui </a> para continuar.</p>
                    </div>";
                }
            }
        }
    }
}
else
{
    echo "<div class='naoaut'><p>Não tem autorização para aceder a esta página</p></div>";
}
?>

