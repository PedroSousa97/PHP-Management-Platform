<?php

require_once("custom/php/common.php");
$conn = getConnection();

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}



if(is_user_logged_in() && current_user_can( 'manage_custom_forms' )) {


    if (isset($_REQUEST['estado'])) {
        $estado = $_REQUEST['estado'];
        if ($estado == editar_form) {
            $form_id = $_REQUEST['id'];
            $queryString = "Select * From object ORDER BY object.name"; //Seleciona todos os atributos da tabela object
            $result_obj = $conn->query($queryString);

            if ($result_obj->num_rows > 0) //Se existem objetos, então print table
            {
                echo "<div class='tableContainer2'>";
                echo "<table class='mytable' style='text-align: left; width: 100%;' border='1' cellpadding='2' cellspacing='2'>
            <tr class='tableheader'><th>Objeto</th><th>ID</th><th>Nome do Atributo</th><th>Tipo de Valor</th><th>Nome do Campo no Formulário</th>
            <th>Tipo do Campo no Formulário</th><th>Tipo de Unidade</th><th>Ordem do Campo no Formulário</th>
            <th>Tamanho do Campo no Formulário</th><th>Obrigatório</th><th>Estado</th><th>Escolher</th><th>Ordem</th></tr>";
                while ($row1 = $result_obj->fetch_assoc()):
                    $objid = intval($row1['id']);   //Para cada row, guardar o object.id

                    $sql2 = "SELECT attribute.id AS attribute_id,attribute.name, value_type, form_field_name, form_field_type, unit_type_id, form_field_order, form_field_size, mandatory, attribute.state FROM attribute WHERE attribute.obj_id= $objid";
                    $result2 = $conn->query($sql2); //Para cada objeto selecionar todos os atributos que correspondem a si, ou seja com o obj_id = objid
                    $count_result2 = mysqli_num_rows($result2); //Contar número de atributos para associar ao rawspan dinâmico
                    if ($count_result2 > 0) //Se existirem atributos, criar rowspan dinâmico
                    {
                        echo "<tr><td colspan='1' rowspan='$count_result2'>" . $row1['name'] . "</td>"; //Nome do objeto na primeira coluna
                        while ($row2 = $result2->fetch_assoc()):
                            $attr_id = $row2['attribute_id'];
                            $unitid = intval($row2['unit_type_id']);  //Para cada atributo do objeto guardar o unit_type_id
                            if (!$row2['unit_type_id'])  //Caso do unit_type_id ser nulo, no campo do tipo de unidade será colocado "-"
                            {
                                $unit_type = "-";

                            } else {
                                $sql3 = "SELECT * FROM attr_unit_type WHERE id= $unitid";  //Para cada atributo seleciona e guarda os dados do tipo de unidade
                                $result3 = $conn->query($sql3);
                                $value = $result3->fetch_assoc();  //Guarda os valores id e nome num array associativo. $value['name'] setá usado para realizar o print do tipo de unidade
                                $unit_type = $value['name'];
                            }

                            if ($row2['mandatory'] == 1) //Caso em que o mandatory = 1, ou seja, na tabela terá que estar SIM neste campo
                            {
                                $mandatory = "Sim";
                            } else {
                                $mandatory = "Não";
                            }

                            $query_pre_select = "SELECT * FROM custom_form_has_attribute WHERE custom_form_id=$form_id AND $attr_id=attribute_id"; //Query para verificar se o atributo está selecionado
                            if ($select_result = mysqli_query($conn, $query_pre_select)) {
                                if (mysqli_num_rows($select_result) > 0) {
                                    $check = checked;              //Atributo selecionado
                                    $row = mysqli_fetch_array($select_result);
                                    $order_field = $row['field_order'];     //Ordem do atributo

                                } else {
                                    $order_field = '';
                                    $check = '';
                                }
                            } else {
                                echo "ERRO: Não foi possível executar a query $query_pre_select. " . mysqli_error($conn);
                                echo "<br>";
                                echo "<br>";
                                voltar_atras();
                                exit();
                            }

                            mysqli_free_result($select_result);

                            echo "<td>" . $row2['attribute_id'] . "<td>" . $row2['name'] . "<td>" . $row2['value_type'] . "<td>" . $row2['form_field_name'] . "<td>" . $row2['form_field_type'] . "<td>" . "$unit_type" . "<td>" . $row2['form_field_order'] . "<td>" . $row2['form_field_size'] . "<td>" . "$mandatory" . "<td>" . $row2['state'] . "<td>" . "<form method='post'>
                                <input type='checkbox' name='atributo[]' value=$attr_id $check />
                                <input type='hidden' name='atributos[]' value=$attr_id />" . "<td>" . "<input type='text' name='ordem[]' value='$order_field'  />" . "</td></tr>";

                        endwhile; // endwhile para cada loop
                    } else //Caso em que o objeto não tem atributos associados. Realiza-se um colspan de 12, unindo assim todas as células da tabela para esta linha
                    {
                        echo "<tr><td colspan='12'>" . $row1['name'] . "</td></tr>";
                    }
                endwhile; // endwhile para o main loop
                $sql_nome_form="SELECT name FROM `custom_form`  WHERE id=$form_id";
                if ($nome_result = mysqli_query($conn, $sql_nome_form)) {
                    while ($nome = mysqli_fetch_array($nome_result)) {
                        $nome_form=$nome['name'];
                    }
                }
                echo "</table>";
                echo "</div>";
                echo "<div class='row' style='text-align: center'><input type='text' name=nome_formulario value='$nome_form' /><br><br>";
                echo "<input type='hidden' name='estado' value='atualizar_form_custom'/>";
                echo "<input type='submit' name='inserform' value='Submeter' />";
                echo "</form></div>";
                echo "<br>";
                echo "<br>";

                // Free result set
                mysqli_free_result($result_obj);
            } else {
                echo "<div class='SemAtrib'><h4>Não há propriedades especificadas</h4></div>";
            }
            voltar_atras();
        }


        if ($estado == inserir) {
            $novo_form = mysqli_real_escape_string($conn, $_REQUEST['custom_form']);

            if(empty($novo_form)){
                echo"O nome do formulário tem que estar preenchido.";
                echo "<br>";
                echo "<br>";
                voltar_atras();
                exit();
            }
            
                $queryString = "Select * From object ORDER BY object.name"; //Seleciona todos os atributos da tabela object

                $result_obj = $conn->query($queryString);

                if ($result_obj->num_rows > 0) //Se existem objetos, então print table
                {
                    echo "<div class='tableContainer2'>";
                    echo "<table class='mytable' style='text-align: left; width: 100%;' border='1' cellpadding='2' cellspacing='2'>
            <tr class='tableheader'><th>Objeto</th><th>ID</th><th>Nome do Atributo</th><th>Tipo de Valor</th><th>Nome do Campo no Formulário</th>
            <th>Tipo do Campo no Formulário</th><th>Tipo de Unidade</th><th>Ordem do Campo no Formulário</th>
            <th>Tamanho do Campo no Formulário</th><th>Obrigatório</th><th>Estado</th><th>Escolher</th><th>Ordem</th></tr>";
                    while ($row1 = $result_obj->fetch_assoc()):
                        $objid = intval($row1['id']);   //Para cada row, guardar o object.id

                        $sql2 = "SELECT attribute.id AS attribute_id,attribute.name, value_type, form_field_name, form_field_type, unit_type_id, form_field_order, form_field_size, mandatory, attribute.state FROM attribute WHERE attribute.obj_id= $objid";
                        $result2 = $conn->query($sql2); //Para cada objeto selecionar todos os atributos que correspondem a si, ou seja com o obj_id = objid
                        $count_result2 = mysqli_num_rows($result2); //Contar número de atributos para associar ao rawspan dinâmico
                        if ($count_result2 > 0) //Se existirem atributos, criar rowspan dinâmico
                        {
                            echo "<tr><td colspan='1' rowspan='$count_result2'>" . $row1['name'] . "</td>"; //Nome do objeto na primeira coluna
                            while ($row2 = $result2->fetch_assoc()):
                                $attr_id = $row2['attribute_id'];
                                $unitid = intval($row2['unit_type_id']);  //Para cada atributo do objeto guardar o unit_type_id
                                if (!$row2['unit_type_id'])  //Caso do unit_type_id ser nulo, no campo do tipo de unidade será colocado "-"
                                {
                                    $unit_type = "-";

                                } else {
                                    $sql3 = "SELECT * FROM attr_unit_type WHERE id= $unitid";  //Para cada atributo seleciona e guarda os dados do tipo de unidade
                                    $result3 = $conn->query($sql3);
                                    $value = $result3->fetch_assoc();  //Guarda os valores id e nome num array associativo. $value['name'] setá usado para realizar o print do tipo de unidade
                                    $unit_type = $value['name'];
                                }

                                if ($row2['mandatory'] == 1) //Caso em que o mandatory = 1, ou seja, na tabela terá que estar SIM neste campo
                                {
                                    $mandatory = "Sim";
                                } else {
                                    $mandatory = "Não";
                                }
                                mysqli_free_result($select_result);

                                echo "<td>" . $row2['attribute_id'] . "<td>" . $row2['name'] . "<td>" . $row2['value_type'] . "<td>" . $row2['form_field_name'] . "<td>" . $row2['form_field_type'] . "<td>" . "$unit_type" . "<td>" . $row2['form_field_order'] . "<td>" . $row2['form_field_size'] . "<td>" . "$mandatory" . "<td>" . $row2['state'] . "<td>" . "<form method='post'>
                                <input type='checkbox' name='atributo[]' value=$attr_id />
                                <input type='hidden' name='atributos[]' value=$attr_id />" . "<td>" . "<input type='text' name='ordem[]'  />" . "</td></tr>";

                            endwhile; // endwhile para cada loop
                        } else //Caso em que o objeto não tem atributos associados. Realiza-se um colspan de 12, unindo assim todas as células da tabela para esta linha
                        {
                            echo "<tr><td colspan='12'>" . $row1['name'] . "</td></tr>";
                        }
                    endwhile; // endwhile para o main loop
                    echo "</table>";
                    echo "</div>";
                    echo "<div class='row' style='text-align: center'><input type='hidden' name='estado' value='inserir_atributos'/>";
                    echo "<input type='hidden' name='form_id' value=$form_id />";
                    echo "<input type='submit' name='inserform' value='Submeter' />";
                    echo "</form></div>";
                    echo "<br>";
                    echo "<br>";

                    // Free result set
                    mysqli_free_result($result_obj);
                } else {
                    echo "<div class='SemAtrib'><h4>Não há propriedades especificadas</h4></div>";
                }

                voltar_atras();

        }

        if ($estado == inserir_atributos) {

            $attrs = $_REQUEST['atributos'];   // Array com os atributos todos
            $order = $_REQUEST['ordem'];       //Array com as ordens dos atributos
            $attr = $_REQUEST['atributo'];      //Array com os atributos selecionados
            $custom_form = $_REQUEST['custom_form'];    //nome do formulário

            $novo_form = mysqli_real_escape_string($conn, $custom_form );


            if(empty($novo_form)){
                echo"O nome do formulário tem que estar preenchido.";
                echo "<br>";
                echo "<br>";
                voltar_atras();
                exit();
            }

            if(empty($attr)){
                echo "Não foi selecionado nenhum atributo.";
                echo "<br>";
                echo "<br>";
                voltar_atras();
                exit();
            }

            for ($i = 0; $i < sizeof($attrs); $i++) {
                for ($o = 0; $o < sizeof($attr); $o++) {
                    if ($attrs[$i] == $attr[$o]) {
                        if(empty($order[$i])){   //Verificar se o campo ordem está preenchido em todos os atributos selecionados

                            echo "É obrigatório preencher a ordem de todos os atributos selecionados.";
                            echo "<br>";
                            echo "<br>";
                            voltar_atras();
                            exit();
                        }
                    }
                }
            }

            $insere = "INSERT INTO `custom_form` (custom_form.name) VALUES ('$novo_form')";

            if (mysqli_query($conn, $insere)) {
                echo "Criado novo formulário<br>";

                $form_id = mysqli_insert_id($conn);
                $queryString = "Select * From object ORDER BY object.name"; //Seleciona todos os atributos da tabela object
            }else {
                echo "ERRO: Não foi possível executar a query $insere. " . mysqli_error($conn);
                echo "<br>";
                echo "<br>";
                voltar_atras();
                exit();
            }


            for ($i = 0; $i < sizeof($attrs); $i++) {
                for ($o = 0; $o < sizeof($attr); $o++) {
                    if ($attrs[$i] == $attr[$o]) {
                        $atributo_id = mysqli_real_escape_string($conn, $attrs[$i]);
                        $atributo_ordem = mysqli_real_escape_string($conn, $order[$i]);
                        $insere_dados = "INSERT INTO `custom_form_has_attribute` (custom_form_id,attribute_id,field_order) VALUES ('$form_id','$atributo_id','$atributo_ordem')";
                        if (mysqli_query($conn, $insere_dados)) {
                            echo "O atributo " . $attrs[$i] . " foi inserido com sucesso. <br>";
                        } else {
                            echo "ERRO: Não foi possível executar a query $insere_dados " . mysqli_error($conn);
                            echo "<br>";
                            echo "<br>";
                            voltar_atras();
                            exit();
                        }


                    }
                }

            }
            echo"<br>";
            echo"<p>Clique <a href='gestao-de-formularios'> aqui </a> para continuar.</p>";
        }

        if ($estado == atualizar_form_custom) {


            $attrs = $_REQUEST['atributos']; // Array com os atributos todos
            $order = $_REQUEST['ordem'];     //Array com as ordens dos atributos
            $attr = $_REQUEST['atributo'];  //Array com os atributos selecionados
            $custom_form_id = $_REQUEST['id']; //id do formulário
            $custom_form_name= $_REQUEST['nome_formulario']; //nome do formulário

            if(empty($custom_form_name)){
                echo"O nome do formulário tem que estar preenchido.";
                echo "<br>";
                echo "<br>";
                voltar_atras();
                exit();
            }

            for ($i = 0; $i < sizeof($attrs); $i++) {
                for ($o = 0; $o < sizeof($attr); $o++) {
                    if ($attrs[$i] == $attr[$o]) {

                        if(empty($order[$i])){          //Verificar se o campo ordem está preenchido em todos os atributos selecionados

                            echo "É obrigatório preencher a ordem de todos os atributos selecionados.";
                            echo "<br>";
                            echo "<br>";
                            voltar_atras();
                            exit();

                        }
                    }
                }
            }


            $query_nome="SELECT name FROM `custom_form` WHERE id=$custom_form_id";
            if ($sql_nome_result = mysqli_query($conn, $query_nome)) {
                $name = mysqli_fetch_array($sql_nome_result);
                if($name['name'] != $custom_form_name){     //Se o novo nome é diferente do antigo fazer update
                    $custom_form_name_string = mysqli_real_escape_string($conn, $custom_form_name);
                    $sql_update_nome="UPDATE `custom_form` SET custom_form.name='$custom_form_name_string' WHERE id=$custom_form_id";
                    if ($update_nome = mysqli_query($conn, $sql_update_nome)) {
                        echo "Nome do formulário atualizado com sucesso para ".$custom_form_name .".<br>";
                    }else {
                        echo "ERRO: Não foi possível executar a query". $sql_update_nome . mysqli_error($conn);
                        echo "<br>";
                        echo "<br>";
                        voltar_atras();
                        exit();
                    }
                }
            }else {
                echo "ERRO: Não foi possível executar a query" . $query_nome  . mysqli_error($conn);
                echo "<br>";
                echo "<br>";
                voltar_atras();
                exit();
            }


            $query_custom_form = "SELECT * FROM custom_form_has_attribute WHERE custom_form_id=$custom_form_id";
            if ($custom_result = mysqli_query($conn, $query_custom_form)) {
                if (mysqli_num_rows($custom_result) > 0) { //Já tem dados inseridos
                    while ($row = mysqli_fetch_array($custom_result)) {
                        $atributo = $row['attribute_id'];
                        $bool = FALSE;
                        for ($s = 0; $s < sizeof($attr); $s++) {
                            if ($attr[$s] == $atributo) {
                                $bool = TRUE;
                            }
                        }
                        if ($bool == FALSE) { //Atributo está no SGBD mas não está selecionado
                            $sql_elimina = "DELETE FROM `custom_form_has_attribute` WHERE custom_form_id=$custom_form_id AND attribute_id = $atributo";
                            if ($delete_result = mysqli_query($conn, $sql_elimina)) {
                                echo"Atributo " . $atributo . " eliminado com sucesso. <br>";
                            }else {
                                echo "ERRO: Não foi possível executar a query $sql_elimina " . mysqli_error($conn);
                                echo "<br>";
                                echo "<br>";
                                voltar_atras();
                                exit();
                            }
                        }
                        if($bool == TRUE){ //Atributo está selecionado e está no sgbd, verificar se é necessário fazer update da ordem
                            $sql_update_req = "SELECT field_order FROM `custom_form_has_attribute` WHERE custom_form_id=$custom_form_id AND attribute_id=$atributo";

                            if($update_req = mysqli_query($conn, $sql_update_req)){
                                while ($linha = mysqli_fetch_array($update_req)) {
                                    for($i = 0; $i < sizeof($attrs); $i++) {
                                        if($attrs[$i] == $atributo) {
                                            if ($linha['field_order'] != $order[$i]){
                                                $sql_update = "UPDATE `custom_form_has_attribute` SET field_order=$order[$i] WHERE custom_form_id=$custom_form_id AND attribute_id=$atributo";
                                                if ($update_result = mysqli_query($conn, $sql_update)) {
                                                    echo"Atributo " . $atributo . " atualizado com sucesso. <br>";
                                                }else {
                                                    echo "ERRO: Não foi possível executar a query $sql_update " . mysqli_error($conn);
                                                    echo "<br>";
                                                    echo "<br>";
                                                    voltar_atras();
                                                    exit();
                                                }
                                            }
                                        }
                                    }
                                }
                            }else {
                                echo "ERRO: Não foi possível executar a query $sql_update_req " . mysqli_error($conn);
                                echo "<br>";
                                echo "<br>";
                                voltar_atras();
                                exit();
                            }

                        }
                    }

                    for ($a = 0; $a < sizeof($attr); $a++) {
                        $atributo = $attr[$a];
                        $tem_id="SELECT attribute_id FROM `custom_form_has_attribute` WHERE custom_form_id=$custom_form_id AND attribute_id=$atributo";
                        if ($tem_id_result = mysqli_query($conn, $tem_id)) {
                            if (mysqli_num_rows($tem_id_result) == 0) {
                                for ($i = 0; $i < sizeof($attrs); $i++) {
                                    if ($attrs[$i] == $attr[$a]) {
                                        $atributo_id = mysqli_real_escape_string($conn, $attrs[$i]);
                                        $atributo_ordem = mysqli_real_escape_string($conn, $order[$i]);
                                        $insere_dados = "INSERT INTO `custom_form_has_attribute` (custom_form_id,attribute_id,field_order) VALUES ('$custom_form_id','$atributo_id','$atributo_ordem')";
                                        if (mysqli_query($conn, $insere_dados)) {
                                            echo "O atributo " . $attrs[$i] . " foi inserido com sucesso. <br>";
                                        } else {
                                            echo "ERRO: Não foi possível executar a query $insere_dados " . mysqli_error($conn);
                                            echo "<br>";
                                            echo "<br>";
                                            voltar_atras();
                                            exit();
                                        }
                                    }
                                }
                            }
                        }else {
                            echo "ERRO: Não foi possível executar a query $tem_id " . mysqli_error($conn);
                            echo "<br>";
                            echo "<br>";
                            voltar_atras();
                            exit();
                        }
                    }


                } else {      //Não tem dados inseridos, fazer o insert dos dados
                    for ($i = 0; $i < sizeof($attrs); $i++) {
                        for ($o = 0; $o < sizeof($attr); $o++) {
                            if ($attrs[$i] == $attr[$o]) {
                                $atributo_id = mysqli_real_escape_string($conn, $attrs[$i]);
                                $atributo_ordem = mysqli_real_escape_string($conn, $order[$i]);
                                $insere_dados = "INSERT INTO `custom_form_has_attribute` (custom_form_id,attribute_id,field_order) VALUES ('$custom_form_id','$atributo_id','$atributo_ordem')";
                                if (mysqli_query($conn, $insere_dados)) {
                                    echo "O atributo " . $attrs[$i] . " foi inserido com sucesso. <br>";
                                } else {
                                    echo "ERRO: Não foi possível executar a query $insere_dados " . mysqli_error($conn);
                                    echo "<br>";
                                    echo "<br>";
                                    voltar_atras();
                                    exit();
                                }


                            }
                        }

                    }
                }
                echo"<br>";
                echo"<p>Clique <a href='gestao-de-formularios'> aqui </a> para continuar.</p>";
            } else {
                echo "ERRO: Não foi possível executar a query $query_custom_form. " . mysqli_error($conn);
                echo "<br>";
                echo "<br>";
                voltar_atras();
                exit();
            }

        }

    }else {
        echo "<div><h4 class='IntroHeader' style='text-align: center'>Formulários</h4></div>";
        $sql = "SELECT id,name FROM custom_form";
        if ($result = mysqli_query($conn, $sql)) {
            if (mysqli_num_rows($result) > 0) {
                echo "<table>";
                echo "<tr class='tableheader'>";
                echo "<th>id</th>";
                echo "<th>Nome do Formulário</th>";
                echo "</tr>";

                while ($row = mysqli_fetch_array($result)) {
                    $form_id = $row['id'];
                    echo "<tr>";
                    echo "<td>" . $row['id'] . "</td>";
                    echo "<td><a href='gestao-de-formularios?estado=editar_form&id=$form_id'>" . $row['name'] . "</a></td>";
                    echo "</tr>";
                }

                echo "</table>";
                // Free result set
                mysqli_free_result($result);
            } else {
                echo "Sem formulários criados";
            }

            echo "<div class='row' style='text-align: center'>Criar novo formulário";

            echo "<form method='get'>";
            echo "<input type='text' name='custom_form' placeholder='Nome' /><br><br>";
            echo "<input type='hidden' name='estado' value='inserir'/>";
            echo "<input type='submit' name='inserform' value='Submeter'/>";
            echo "</form></div>";


        } else {
            echo "ERRO: Não foi possível executar a query $sql. " . mysqli_error($conn);

        }
    }

}
else
{
    echo "<div><p>Não tem autorização para aceder a esta página</p></div>";
}


$conn->close();

?>