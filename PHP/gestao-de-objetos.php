<?php

require_once("common.php");

$connect = getConnection();


if ($connect->connect_error)
{
    echo 'Não consegue conectar-se à base de dados: ' . $connect->connect_error;
    exit();
}


if(is_user_logged_in() && current_user_can('manage_objects'))
{
    if(empty($_POST))
    {
        // query que devolve tudo do objecto ordenado pelo type_id e name
        $qr_object = "SELECT * FROM object ORDER BY obj_type_id, name;";
        $rs_qr_object = mysqli_query($connect,$qr_object);


        // Se houver objectos
        if ($rs_qr_object->num_rows > 0)
        {
            $qr_num = "SELECT * FROM object;";
            $num_objects = mysqli_query($connect,$qr_num)->num_rows;

            if ($num_objects > 0)
            {
                echo '<table class="mytable" style="text-align: left; width: 100%;" border="1" cellpadding="2" cellspacing="2">';
                echo '<tr class="tableheader"><th>tipo de objecto</th><th>id</th><th>nome do objecto</th><th>estado</th><td>ação</td></tr>';

                $aparece = false;
                while ($row_obj_types = mysqli_fetch_assoc($rs_qr_object))
                {
                    $qr_obj_mostratipos_num = "SELECT * FROM object WHERE obj_type_id =%s";
                    $obj_mostratipos_num = sprintf($qr_obj_mostratipos_num, $row_obj_types['obj_type_id']);
                    $rs_objects = mysqli_query($connect,$obj_mostratipos_num);
                    $rss_objects = mysqli_num_rows($rs_objects);

                    $qr_procura_tipos = "SELECT id, name FROM obj_type";
                    $rs_procura_tipos = mysqli_query($connect, $qr_procura_tipos);

                    while($tupl_tipos = mysqli_fetch_assoc($rs_procura_tipos))
                    {
                        $sai = false;
                        if ($tupl_tipos["id"] == $row_obj_types["obj_type_id"]) {
                            $sai = true;
                            if ($tupl_tipos["name"] != $aparece) {
                                echo "<td rowspan = " . $rss_objects . ">" . $tupl_tipos["name"] . " </td>";
                                $aparece = $tupl_tipos["name"];
                            }
                        }
                    }

                    echo "<td>".$row_obj_types["id"]."</td>";
                    echo"<td>".$row_obj_types["name"]."</td>";
                    if($row_obj_types["state"] == "active")
                    {
                        echo"<td>ativo</td>";
                        echo "<td>[editar] [desativar]</td></tr>";
                    }
                    else
                    {
                        echo"<td>inativo</td>";
                        echo "<td>[editar] [ativar]</td></tr>";
                    }
                }
                echo '</table>';
            }
            else
            {
                echo 'Não há objectos!';
            }
        }
        else
        {
            echo 'Não há objectos!';
        }
    }
}
else
{
    echo "Não tem autorização para aceder a esta página!";
}


function listaTipos()
{
    $connect = getConnection();
    $qr_obj_types = "SELECT id, name FROM obj_type ORDER BY name";
    $rs_qr_object = mysqli_query($connect,$qr_obj_types);
    $first_checked = true;

    while($tupl_type = mysqli_fetch_assoc($rs_qr_object))
    {
        if($first_checked == true)
        {
            echo '<input type="radio" name="object_obj_type_id" value="'.$tupl_type['id'].'" checked> ' .$tupl_type['name']."<br>";
        }
        else
        {
            echo '<input type="radio" name="object_obj_type_id" value="'.$tupl_type['id'].'" '.$tupl_type['name']."<br>";
        }
    }

}

if(empty($_POST) && is_user_logged_in() && current_user_can('manage_objects'))
{

    ?>
    <h3 class='IntroHeader' style='text-align: center'> Gestão de objectos - introdução </h3>
    <div class='row' style='text-align: center'>
    <form method="post" name="gestao-de-objectos">
        <label><input type="text" placeholder="Nome" name="object_name"></label><br>
        <b>*Tipo:</b><br> <?php listatipos() ?><br>
            <b>*Estado:</b><br>
        <label><input type="radio" name="object_state" value="active" checked> Ativo <br>
        <input type="radio" name="object_state" value="inactive"> Inativo <br>
        <input type="hidden" name="estado" value="inserir"><br>
        <input type="submit" name="insertobj" value="Inserir objecto"></label>
    </form>
    </div>

    <?php
}
    if ($_REQUEST['estado'] == "inserir")
    {
        ?>
        <h3 class='InsertHeader'> Gestão de objectos - inserção </h3>

        <?php
        $object_name = mysqli_real_escape_string($connect, $_REQUEST['object_name']);
        $object_obj_type_id = mysqli_real_escape_string($connect, $_REQUEST['object_obj_type_id']);
        $object_state = mysqli_real_escape_string($connect, $_REQUEST['object_state']);

        $qr_string_insert = "INSERT INTO object (name, obj_type_id, state) VALUES ('$object_name', '$object_obj_type_id', '$object_state')";


        if (empty($object_name))
        {
            echo "<div class='insertion_verify'>O campo Nome é de preenchimento obrigatório.<br>";
            echo "Clique <a href='gestao-de-objetos'> aqui </a> para voltar.</div>";
            exit();
        }
        else
        {
            $rs_insercao = mysqli_query($connect,$qr_string_insert);

            if ($rs_insercao)
            {
                echo "<div class='insert_style'>Inseriu os dados com sucesso! <br>";
                echo "Clique <a href='gestao-de-objetos'> aqui </a> para continuar.</div>";
                exit();
            }
            else
            {
                echo "Não inseriu os dados com sucesso! <br>";
                echo 'Error: '. $qr_string_insert . "<br>". $connect ->connect_error;
                echo "Clique <a href='gestao-de-objetos'> aqui </a> para voltar.";
                exit();
            }
        }
}
?>

