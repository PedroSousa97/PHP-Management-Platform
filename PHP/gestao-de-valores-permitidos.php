<?php

require_once("common.php");

$connect = getconnection();

if ($connect->connect_error)
{
    echo 'Não consegue conectar-se à base de dados: ' . $connect->connect_error;
    exit();
}


if(is_user_logged_in() && current_user_can('manage_allowed_values')) {
    if (empty($_REQUEST['estado'])) {

        // query para obter o nome e id do objecto ordenado pelo nome
        $qr_obj = "SELECT DISTINCT object.name , object.id FROM object ORDER BY name";
        $rs_obj = mysqli_query($connect, $qr_obj);

        // Se houver objectos
        if ($rs_obj->num_rows > 0) {
            echo '<table class="mytable" style="text-align: left; width: 100%;" border="1" cellpadding="2" cellspacing="2">';
            echo "<tr class='tableheader'><th>objeto</th><th>id</th><th>atributo</th><th>id</th><th>valores permitidos</th><th>estado</th><th>ação</th></tr>";

            // Enquanto houver objectos
            while ($row_obj = mysqli_fetch_assoc($rs_obj)) {

                $object = $row_obj['name'];
                $object_id = $row_obj['id'];

                // query para obter nome e id do atributo do tipo 'enum' associado a um objecto
                $qr_attr = "SELECT DISTINCT attribute.name, attribute.id FROM attribute WHERE attribute.obj_id = $object_id AND attribute.value_type = 'enum'";
                $rs_attr_values = mysqli_query($connect, $qr_attr);

                // faz a contagem do total de valores permitidos para todos os atributos do objecto
                $countspermited=0;

                // Enquanto houver atributos associados a um objecto
                while($row1= mysqli_fetch_assoc($rs_attr_values)){
                    $attid=$row1['id'];

                    // query para retornar os valores permitidos de cada atributo (para valores distintos)
                    $attr_allowed = "SELECT DISTINCT * FROM attr_allowed_value WHERE attr_allowed_value.attribute_id=$attid";
                    $rs_obj_attr_allowed = mysqli_query($connect,  $attr_allowed);
                    if($rs_obj_attr_allowed->num_rows>0) {
                        // faz a soma de todos os valores permitidos de cada atributo
                        $countspermited = $countspermited + intval($rs_obj_attr_allowed->num_rows);
                    }
                    else{
                        $countspermited = $countspermited + 1;
                    }
                }

                $rs_attr_values = mysqli_query($connect, $qr_attr);

                // Se houver atributos associados a objectos
                if ($rs_attr_values->num_rows > 0) {
                    // Se houver valores permitidos nesses atributos
                    if($countspermited>0) {
                        echo "<tr><td colspan='1' rowspan='$countspermited'> $object</td>";
                    }
                    else{
                        echo "<tr><td colspan='1' rowspan='$rs_attr_values->num_rows'> $object</td>";
                    }

                    // Enquanto houver atributos associados a objectos
                    while ($row_attr = mysqli_fetch_assoc($rs_attr_values)) {
                       $attr_id = $row_attr['id'];

                        // query para retornar os valores permitidos de cada atributo
                        $attr_allowed_value = "SELECT DISTINCT * FROM attr_allowed_value WHERE attr_allowed_value.attribute_id=$attr_id";
                        $rs_obj_attr_value_enum = mysqli_query($connect,  $attr_allowed_value);

                        // Se para um certo atributo houver valores permitidos.
                        if ($rs_obj_attr_value_enum->num_rows > 0) {
                            echo "<td colspan='1' rowspan='$rs_obj_attr_value_enum->num_rows'>" . $row_attr['id'] . "<td colspan='1' rowspan='$rs_obj_attr_value_enum->num_rows'>".'<a href="gestao-de-valores-permitidos?estado=introducao&atributo='.$row_attr['id'] .'">' ."[". $row_attr['name']."]"."</td>";

                            // enquanto houver valores permitidos associados a um atributo
                            while ($row_permited = mysqli_fetch_assoc($rs_obj_attr_value_enum))
                            {
                                if ($row_permited['state'] == "active")
                                {
                                    echo "<td>" . $row_permited['id'] . "<td>" . $row_permited['value'] . "<td>" . $row_permited['state'] . "<td>" . "[editar][desativar]" . "</td></tr>";
                                } else {
                                    echo "<td>" . $row_permited['id'] . "<td>" . $row_permited['value'] . "<td>" . $row_permited['state'] . "<td>" . "[editar][ativar]" . "</td></tr>";
                                }
                            }
                        }
                        else{
                            //Caso contrário devolve uma mensagem.
                            echo "<td>" . $row_attr['id'] . "<td>".'<a href="gestao-de-valores-permitidos?estado=introducao&atributo='.$row_attr['id'] .'">' ."[". $row_attr['name']."]"."</a><td colspan='4'>".'Não há valores permitidos definidos'."</td></tr>";
                        }
                    }
                }
                else{
                    echo "<tr><td>" .$object."<td colspan='6'>".' Não há atributos especificados cujo tipo de valor seja enum. Especificar primeiro novo(s) atributo(s) e depois voltar a esta opção.'."</td></tr>";
                }
            }
            echo "</table>";
        }
        else
            {
            echo "Não há objectos. Introduza um novo objecto.";
            }
    }
        else if ($_REQUEST['estado'] == "introducao") {

            $_SESSION['attribute_id'] = $_REQUEST['atributo'];
            $attribute_id = $_SESSION['attribute_id'];
            ?>
            <h3 class='IntroHeader' style='text-align: center'> Gestão de valores permitidos - introdução </h3>
            <div class='row' style='text-align: center'>
            <form method="post" name="gestao-de-valores-permitidos.php">
                <label><input type="text" name="attr_allowed_values" placeholder="*Valor (Obrigatório):"></label><br>
                <label><input type="hidden" name="estado" value="inserir"><br>
                <input type="submit" name="insertval" value="Inserir valor permitido"></label>
            </form>
            </div>
            <?php
        }
    else if($_REQUEST['estado'] == "inserir") {
            ?>
            <h3 class='InsertHeader'> Gestão de valores permitidos - inserção </h3>

            <?php
            $attr_allowed_value = mysqli_real_escape_string($connect, $_REQUEST['attr_allowed_values']);
            $attribute_id = $_SESSION['attribute_id'];
            $qr_string_insert = "INSERT INTO attr_allowed_value (attribute_id, value, state) VALUES ('$attribute_id', '$attr_allowed_value', 'active')";


            if (empty($attr_allowed_value))
            {
                echo "O campo Valor é de preenchimento obrigatório. <br>";
                echo "Clique <a href='gestao-de-valores-permitidos'> aqui </a> para voltar.";
                exit();
            }
            else
                {
                $rs_insercao = mysqli_query($connect, $qr_string_insert);

                if ($rs_insercao)
                {
                    echo "<div class='insert_style'>Inseriu os dados de novo valor permitido com sucesso. <br>";
                    echo "Clique <a href='gestao-de-valores-permitidos'> aqui </a> para Continuar.</div>";
                    exit();
                }
                else
                    {
                        echo "Não inseriu os dados com sucesso! <br>";
                        echo 'Error: ' . $rs_insercao . "<br>" . $connect->connect_error;
                        echo "Clique <a href='gestao-de-valores-permitidos'> aqui </a> para voltar.";
                        exit();
                }
            }
        }
    }
else
{
    echo "Não tem autorização para aceder a esta página!";
}