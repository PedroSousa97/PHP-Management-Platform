<?php

require_once("custom/php/common.php");

$link = getConnection();

$queryString = "Select * From obj_type ORDER BY obj_type.name";




if(is_user_logged_in() && current_user_can( 'insert_values' ))
{
    if(empty($_REQUEST))
    {
        if (!$link){

            echo "<div class='Conect'><h4>Lamentamos, mas a sua conexão à base de dados falhou!</h4></div>";
            echo "Error: " .mysqli_error($link);
            exit();
        }

        echo "<div class='lists'><h3 class='InsertHeader'>Inserção de valores - escolher objeto/formulário customizado</h3><br>
        <p class='list_titles'>Objetos:</p>";
        $result=$link->query($queryString);

        if(!$result)
        {
            echo "<div class='quererror'>";
            echo "<div class='InserQuery'><h4>Lamentamos, mas a visualiazaçõa de dados falhou!</h4></div>";
            echo "Error: " . mysqli_error($link);
            echo "</div>";
            exit();
        }

        if($result->num_rows>0)     //Criação de listas para todos os tipos de objeto, os objetos e formulários
        {
           while ($row1 = mysqli_fetch_assoc($result))
           {    $search_id=$row1['id'];
                echo"<li class='li_h'>".$row1['name']."</li>";
                $query_objects= "Select * From object WHERE object.obj_type_id = $search_id ORDER BY object.name";
                $resultobj=$link->query($query_objects);
               if(!$resultobj)
               {
                   echo "<div class='quererror'>";
                   echo "<div class='InserQuery'><h4>Lamentamos, mas a visualiazaçõa de dados falhou!</h4></div>";
                   echo "Error: " . mysqli_error($link);
                   echo "</div>";
                   exit();
               }
               if($resultobj->num_rows>0)
               {   echo"<ul>";
                   while ($row2 = mysqli_fetch_assoc($resultobj))
                   {
                       echo"<li class='ul_h'>".'<a href="insercao-de-valores?estado=introducao&obj='.$row2['id'].'">'."[".$row2['name']."]"."</a></li>";
                   }
                   echo"</ul>";
               }
               else
               {
                   echo "<div><p>Não existem objetos associados</p></div>";
               }
           }
        }
        else
        {
            echo "<div><p>Não existem tipos de objeto a apresentar</p></div>";
        }
        echo"<p class='list_titles'>Formulários customizados:</p>";
        $query_form = "Select * From custom_form ORDER BY custom_form.name";
        $result_form=$link->query($query_form);
        if(!$result_form)
        {
            echo "<div class='quererror'>";
            echo "<div class='InserQuery'><h4>Lamentamos, mas a visualiazaçõa de dados falhou!</h4></div>";
            echo "Error: " . mysqli_error($link);
            echo "</div>";
            exit();
        }

        if($result_form->num_rows>0)
        {
            while ($row_form = mysqli_fetch_assoc($result_form))
            {
                echo"<li class='ul_h'>".'<a href="insercao-de-valores?estado=introducao&form='.$row_form['id'].'">'."[".$row_form['name']."]"."</a></li>";
            }
        }
        else
        {
            echo "<div><p>Não existem formulários customizados a apresentar</p></div>";
        }
        echo "</div>";
    }
    else if ($_REQUEST['estado'] == "introducao" AND $_REQUEST['obj'])  //Introdução de dados para objetos
    {
        $_SESSION['obj_id'] = $_REQUEST['obj'];
        $obj_id=$_SESSION['obj_id'];
        $query_obj_name = "SELECT * FROM object WHERE object.id =$obj_id";
        $resultobjname = $link->query($query_obj_name);
        $valueobj = mysqli_fetch_object($resultobjname);
        $obj_type_id=$_SESSION['obj_type_id']=$valueobj->obj_type_id;
        $obj_name=$_SESSION['obj_name']=$valueobj->name;
        echo "<div class='lists'><h3 class='InsertHeader'>".'Inserção de valores - '.$obj_name."</h3>
        <p class='alert'>Atributos obrigatório devem ser preenchidos! Não é permitido o avanço/validação caso não estiver preenchido.</p>";
        echo "<div class='row' style='text-align: center'>
        <form id='insert-form' name='obj_type_".$obj_type_id."_obj_".$obj_id."' method='POST' action='?estado=validar&obj=$obj_id'>";
        $query_attributes = "SELECT * FROM attribute WHERE attribute.obj_id =$obj_id AND attribute.state = 'active' ";
        $resultattrs = $link->query($query_attributes);
        if($resultattrs->num_rows>0)
        {
            while($row_attrform=mysqli_fetch_assoc($resultattrs))
            {
                switch ($row_attrform['value_type'])
                {
                    case "text":
                        if($row_attrform['mandatory'])
                        {
                            echo"<p class='obriga'>".'O atributo '.$row_attrform['name'].' é obrigatório'."</p>";
                        }
                        else
                        {
                            echo"<p class='obriga'>".'O atributo '.$row_attrform['name'].' não é obrigatório'."</p>";
                        }
                        if($row_attrform['unit_type_id'])
                        {
                            $unitid = intval($row_attrform['unit_type_id']);
                            $sql3 = "SELECT * FROM attr_unit_type WHERE id= $unitid";
                            $result3 = $link->query($sql3);
                            $value = $result3->fetch_assoc();
                            if($row_attrform['form_field_size'])
                            {
                                echo "".$row_attrform['name']."<input type='text' name='".$row_attrform['form_field_name']."'  maxlenght= '".$row_attrform['form_field_size']."' size='".$row_attrform['form_field_size']."'>".$value['name']."";
                            }
                            else
                            {
                                echo "".$row_attrform['name']."<input type='text' name='".$row_attrform['form_field_name']."''>".$value['name']."";

                            }

                        }
                        else
                        {
                            if($row_attrform['form_field_size'])
                            {
                                echo "".$row_attrform['name']."<input type='text' name='".$row_attrform['form_field_name']."'  maxlenght= '".$row_attrform['form_field_size']."' size='".$row_attrform['form_field_size']."'>";
                            }
                            else
                            {
                                echo "".$row_attrform['name']."<input type='text' name='".$row_attrform['form_field_name']."''>";

                            }
                        }
                        break;
                    case "bool":
                        if($row_attrform['mandatory'])
                        {
                            echo"<p class='obriga'>".'O atributo '.$row_attrform['name'].' é obrigatório'."</p>";
                        }
                        else
                        {
                            echo"<p class='obriga'>".'O atributo '.$row_attrform['name'].' não é obrigatório'."</p>";
                        }
                        if($row_attrform['unit_type_id'])
                        {
                            $unitid = intval($row_attrform['unit_type_id']);
                            $sql3 = "SELECT * FROM attr_unit_type WHERE id= $unitid";
                            $result3 = $link->query($sql3);
                            $value = $result3->fetch_assoc();

                                echo "<input type='radio' name='".$row_attrform['form_field_name']."'  value= 'verdadeiro'>".'Verdadeiro (Unidade: '.$value['name'].')'."<br>";
                                echo "<input type='radio' name='".$row_attrform['form_field_name']."'  value= 'falso'>".'Falso (Unidade: '.$value['name'].')';


                        }
                        else
                        {
                            echo "<input type='radio' name='".$row_attrform['form_field_name']."'  value= 'verdadeiro'>".'Verdadeiro'."<br>";
                            echo "<input type='radio' name='".$row_attrform['form_field_name']."'  value= 'falso'>".'Falso';

                        }
                        break;
                    case "int":
                        if($row_attrform['mandatory'])
                        {
                            echo"<p class='obriga'>".'O atributo '.$row_attrform['name'].' é obrigatório'."</p>";
                        }
                        else
                        {
                            echo"<p class='obriga'>".'O atributo '.$row_attrform['name'].' não é obrigatório'."</p>";
                        }
                        if($row_attrform['unit_type_id'])
                        {
                            $unitid = intval($row_attrform['unit_type_id']);
                            $sql3 = "SELECT * FROM attr_unit_type WHERE id= $unitid";
                            $result3 = $link->query($sql3);
                            $value = $result3->fetch_assoc();
                            if($row_attrform['form_field_size'])
                            {
                                echo "".$row_attrform['name']."<input type='text' name='".$row_attrform['form_field_name']."'  maxlenght= '".$row_attrform['form_field_size']."' size='".$row_attrform['form_field_size']."'>".$value['name']."";
                            }
                            else
                            {
                                echo "".$row_attrform['name']."<input type='text' name='".$row_attrform['form_field_name']."''>".$value['name']."";

                            }

                        }
                        else
                        {
                            if($row_attrform['form_field_size'])
                            {
                                echo "".$row_attrform['name']."<input type='text' name='".$row_attrform['form_field_name']."'  maxlenght= '".$row_attrform['form_field_size']."' size='".$row_attrform['form_field_size']."'>";
                            }
                            else
                            {
                                echo "".$row_attrform['name']."<input type='text' name='".$row_attrform['form_field_name']."''>";

                            }
                        }
                        break;
                    case "double":
                        if($row_attrform['mandatory'])
                        {
                            echo"<p class='obriga'>".'O atributo '.$row_attrform['name'].' é obrigatório'."</p>";
                        }
                        else
                        {
                            echo"<p class='obriga'>".'O atributo '.$row_attrform['name'].' não é obrigatório'."</p>";
                        }
                        if($row_attrform['unit_type_id'])
                        {
                            $unitid = intval($row_attrform['unit_type_id']);
                            $sql3 = "SELECT * FROM attr_unit_type WHERE id= $unitid";
                            $result3 = $link->query($sql3);
                            $value = $result3->fetch_assoc();
                            if($row_attrform['form_field_size'])
                            {
                                echo "".$row_attrform['name']."<input type='text' name='".$row_attrform['form_field_name']."'  maxlenght= '".$row_attrform['form_field_size']."' size='".$row_attrform['form_field_size']."'>".$value['name']."";
                            }
                            else
                            {
                                echo "".$row_attrform['name']."<input type='text' name='".$row_attrform['form_field_name']."''>".$value['name']."";

                            }

                        }
                        else
                        {
                            if($row_attrform['form_field_size'])
                            {
                                echo "".$row_attrform['name']."<input type='text' name='".$row_attrform['form_field_name']."'  maxlenght= '".$row_attrform['form_field_size']."' size='".$row_attrform['form_field_size']."'>";
                            }
                            else
                            {
                                echo "".$row_attrform['name']."<input type='text' name='".$row_attrform['form_field_name']."''>";

                            }
                        }
                        break;
                    case "enum":
                        if($row_attrform['mandatory'])
                        {
                            echo"<p class='obriga'>".'O atributo '.$row_attrform['name'].' é obrigatório'."</p>";
                        }
                        else
                        {
                            echo"<p class='obriga'>".'O atributo '.$row_attrform['name'].' não é obrigatório'."</p>";
                        }
                        $attid = $row_attrform['id'];
                        $queryStringallowed = "Select * From attr_allowed_value WHERE attribute_id=$attid ORDER BY value";
                        $resultallowed=$link->query($queryStringallowed);
                        if($resultallowed->num_rows>0) {
                            if ($row_attrform['form_field_type'] == "radio") {
                                while ($rowenum = mysqli_fetch_assoc($resultallowed)) {
                                    echo "<input type='radio' name='" . $row_attrform['form_field_name'] . "' value='" . $rowenum['value'] . "'>" . $rowenum['value'] . "<br>";
                                }
                            }
                            if ($row_attrform['form_field_type'] == "checkbox") {
                                while ($rowenum = mysqli_fetch_assoc($resultallowed)) {
                                    echo "<input type='checkbox' name='" . $row_attrform['form_field_name'] . "' value='" . $rowenum['value'] . "'>" . $rowenum['value'] . "<br>";
                                }


                            }
                            if ($row_attrform['form_field_type'] == "selectbox") {
                                echo "<select name='" . $row_attrform['form_field_name'] . "'>";
                                while ($rowenum = mysqli_fetch_assoc($resultallowed)) {
                                    echo "<option value='" . $rowenum['value'] . "'>" . $rowenum['value'] . "</option><br>";
                                }
                                echo"</select>";

                            }
                        }
                        else
                        {
                            echo "<p>O atributo não tem valores permitidos</p><br>";
                        }
                        break;
                    case "obj_ref":
                        if($row_attrform['mandatory'])
                        {
                            echo"<p class='obriga'>".'O atributo '.$row_attrform['name'].' é obrigatório'."</p>";
                        }
                        else
                        {
                            echo"<p class='obriga'>".'O atributo '.$row_attrform['name'].' não é obrigatório'."</p>";
                        }
                        $attid = $row_attrform['id'];
                        $obj_fk = $row_attrform['obj_fk_id'];
                        $queryStringobj_inst = "Select obj_inst.id, obj_inst.object_id,	obj_inst.object_name From obj_inst,attribute WHERE obj_inst.object_id=$obj_fk AND attribute.obj_fk_id=$obj_fk AND attribute.id=$attid ORDER BY obj_inst.id";
                        $resultalobj_inst=$link->query($queryStringobj_inst);
                        if($resultalobj_inst->num_rows>0) {
                            echo "<select name='" . $row_attrform['form_field_name'] . "'>";
                            while ($rowinst = mysqli_fetch_assoc($resultalobj_inst)) {
                                echo "<option value='" . $rowinst['id'] . "'>" . $rowinst['object_name'] . "</option><br>";
                            }
                            echo "</select>";
                        }
                        else
                        {
                            echo "<p>O atributo não tem objetos instanciados</p><br>";
                        }
                        break;
                }
            }
        }
        else
        {
            echo "<p>Objeto sem atributos associados</p>";
        }
        echo"<br><br><input type='text' placeholder='Nome para instância do objeto (Opcional)' name='nome_instace'><br><br>
            <input name='estado' value='validar' type='hidden'>
            <input type='submit' name='insertsub' value='Submeter'>";
        echo"</form>
        </div></div>";
    }
    else if ($_REQUEST['estado'] == "introducao" AND $_REQUEST['form'])     //Introdução de dados para forms
    {
        $_SESSION['form_id'] = $_REQUEST['form'];
        $form_id=$_SESSION['form_id'];
        $query_form_name = "SELECT * FROM custom_form WHERE custom_form.id =$form_id";
        $resultformname = $link->query($query_form_name);
        $valueform = mysqli_fetch_object($resultformname);
        $form_name=$_SESSION['form_name']=$valueform->name;
        $query_form_att = "SELECT * FROM custom_form_has_attribute WHERE custom_form_has_attribute.custom_form_id=$form_id";
        $resultformatt = $link->query($query_form_att);
        echo "<div class='lists'><h3 class='InsertHeader'>".'Inserção de valores - '.$form_name."</h3>
        <p class='alert'>Atributos obrigatório devem ser preenchidos! Não é permitido o avanço/validação caso não estiver preenchido.</p>";
        echo "<div class='row' style='text-align: center'>
        <form id='insert-form' name='form_name_".$form_name."_form_".$form_id."' method='POST' action='?estado=validar&form=$form_id'>";
        if($resultformatt->num_rows>0)
        {
            while ($row_has_att=mysqli_fetch_assoc($resultformatt))
            {
                $form_att_id=$row_has_att['attribute_id'];
                $query_attributes = "SELECT * FROM attribute WHERE id =$form_att_id AND attribute.state = 'active' ";
                $resultattrs = $link->query($query_attributes);
                if($resultattrs->num_rows>0)
                {
                    while($row_attrform=mysqli_fetch_assoc($resultattrs))
                    {
                        switch ($row_attrform['value_type'])
                        {
                            case "text":
                                if($row_attrform['mandatory'])
                                {
                                    echo"<p class='obriga'>".'O atributo '.$row_attrform['name'].' é obrigatório'."</p>";
                                }
                                else
                                {
                                    echo"<p class='obriga'>".'O atributo '.$row_attrform['name'].' não é obrigatório'."</p>";
                                }
                                if($row_attrform['unit_type_id'])
                                {
                                    $unitid = intval($row_attrform['unit_type_id']);
                                    $sql3 = "SELECT * FROM attr_unit_type WHERE id= $unitid";  //Para cada atributo seleciona e guarda os dados do tipo de unidade
                                    $result3 = $link->query($sql3);
                                    $value = $result3->fetch_assoc();
                                    if($row_attrform['form_field_size'])
                                    {
                                        echo "".$row_attrform['name']."<input type='text' name='".$row_attrform['form_field_name']."'  maxlenght= '".$row_attrform['form_field_size']."' size='".$row_attrform['form_field_size']."'>".$value['name']."";
                                    }
                                    else
                                    {
                                        echo "".$row_attrform['name']."<input type='text' name='".$row_attrform['form_field_name']."''>".$value['name']."";

                                    }

                                }
                                else
                                {
                                    if($row_attrform['form_field_size'])
                                    {
                                        echo "".$row_attrform['name']."<input type='text' name='".$row_attrform['form_field_name']."'  maxlenght= '".$row_attrform['form_field_size']."' size='".$row_attrform['form_field_size']."'>";
                                    }
                                    else
                                    {
                                        echo "".$row_attrform['name']."<input type='text' name='".$row_attrform['form_field_name']."''>";

                                    }
                                }
                                break;
                            case "bool":
                                if($row_attrform['mandatory'])
                                {
                                    echo"<p class='obriga'>".'O atributo '.$row_attrform['name'].' é obrigatório'."</p>";
                                }
                                else
                                {
                                    echo"<p class='obriga'>".'O atributo '.$row_attrform['name'].' não é obrigatório'."</p>";
                                }
                                if($row_attrform['unit_type_id'])
                                {
                                    $unitid = intval($row_attrform['unit_type_id']);
                                    $sql3 = "SELECT * FROM attr_unit_type WHERE id= $unitid";  //Para cada atributo seleciona e guarda os dados do tipo de unidade
                                    $result3 = $link->query($sql3);
                                    $value = $result3->fetch_assoc();

                                    echo "<input type='radio' name='".$row_attrform['form_field_name']."'  value= 'verdadeiro'>".'Verdadeiro (Unidade: '.$value['name'].')'."<br>";
                                    echo "<input type='radio' name='".$row_attrform['form_field_name']."'  value= 'falso'>".'Falso (Unidade: '.$value['name'].')';


                                }
                                else
                                {
                                    echo "<input type='radio' name='".$row_attrform['form_field_name']."'  value= 'verdadeiro'>".'Verdadeiro'."<br>";
                                    echo "<input type='radio' name='".$row_attrform['form_field_name']."'  value= 'falso'>".'Falso';

                                }
                                break;
                            case "int":
                                if($row_attrform['mandatory'])
                                {
                                    echo"<p class='obriga'>".'O atributo '.$row_attrform['name'].' é obrigatório'."</p>";
                                }
                                else
                                {
                                    echo"<p class='obriga'>".'O atributo '.$row_attrform['name'].' não é obrigatório'."</p>";
                                }
                                if($row_attrform['unit_type_id'])
                                {
                                    $unitid = intval($row_attrform['unit_type_id']);
                                    $sql3 = "SELECT * FROM attr_unit_type WHERE id= $unitid";  //Para cada atributo seleciona e guarda os dados do tipo de unidade
                                    $result3 = $link->query($sql3);
                                    $value = $result3->fetch_assoc();
                                    if($row_attrform['form_field_size'])
                                    {
                                        echo "".$row_attrform['name']."<input type='text' name='".$row_attrform['form_field_name']."'  maxlenght= '".$row_attrform['form_field_size']."' size='".$row_attrform['form_field_size']."'>".$value['name']."";
                                    }
                                    else
                                    {
                                        echo "".$row_attrform['name']."<input type='text' name='".$row_attrform['form_field_name']."''>".$value['name']."";

                                    }

                                }
                                else
                                {
                                    if($row_attrform['form_field_size'])
                                    {
                                        echo "".$row_attrform['name']."<input type='text' name='".$row_attrform['form_field_name']."'  maxlenght= '".$row_attrform['form_field_size']."' size='".$row_attrform['form_field_size']."'>";
                                    }
                                    else
                                    {
                                        echo "".$row_attrform['name']."<input type='text' name='".$row_attrform['form_field_name']."''>";

                                    }
                                }
                                break;
                            case "double":
                                if($row_attrform['mandatory'])
                                {
                                    echo"<p class='obriga'>".'O atributo '.$row_attrform['name'].' é obrigatório'."</p>";
                                }
                                else
                                {
                                    echo"<p class='obriga'>".'O atributo '.$row_attrform['name'].' não é obrigatório'."</p>";
                                }
                                if($row_attrform['unit_type_id'])
                                {
                                    $unitid = intval($row_attrform['unit_type_id']);
                                    $sql3 = "SELECT * FROM attr_unit_type WHERE id= $unitid";  //Para cada atributo seleciona e guarda os dados do tipo de unidade
                                    $result3 = $link->query($sql3);
                                    $value = $result3->fetch_assoc();
                                    if($row_attrform['form_field_size'])
                                    {
                                        echo "".$row_attrform['name']."<input type='text' name='".$row_attrform['form_field_name']."'  maxlenght= '".$row_attrform['form_field_size']."' size='".$row_attrform['form_field_size']."'>".$value['name']."";
                                    }
                                    else
                                    {
                                        echo "".$row_attrform['name']."<input type='text' name='".$row_attrform['form_field_name']."''>".$value['name']."";

                                    }

                                }
                                else
                                {
                                    if($row_attrform['form_field_size'])
                                    {
                                        echo "".$row_attrform['name']."<input type='text' name='".$row_attrform['form_field_name']."'  maxlenght= '".$row_attrform['form_field_size']."' size='".$row_attrform['form_field_size']."'>";
                                    }
                                    else
                                    {
                                        echo "".$row_attrform['name']."<input type='text' name='".$row_attrform['form_field_name']."''>";

                                    }
                                }
                                break;
                            case "enum":
                                if($row_attrform['mandatory'])
                                {
                                    echo"<p class='obriga'>".'O atributo '.$row_attrform['name'].' é obrigatório'."</p>";
                                }
                                else
                                {
                                    echo"<p class='obriga'>".'O atributo '.$row_attrform['name'].' não é obrigatório'."</p>";
                                }
                                $attid = $row_attrform['id'];
                                $queryStringallowed = "Select * From attr_allowed_value WHERE attribute_id=$attid ORDER BY value";
                                $resultallowed=$link->query($queryStringallowed);
                                if($resultallowed->num_rows>0) {
                                    if ($row_attrform['form_field_type'] == "radio") {
                                        while ($rowenum = mysqli_fetch_assoc($resultallowed)) {
                                            echo "<input type='radio' name='" . $row_attrform['form_field_name'] . "' value='" . $rowenum['value'] . "'>" . $rowenum['value'] . "<br>";
                                        }
                                    }
                                    if ($row_attrform['form_field_type'] == "checkbox") {
                                        while ($rowenum = mysqli_fetch_assoc($resultallowed)) {
                                            echo "<input type='checkbox' name='" . $row_attrform['form_field_name'] . "' value='" . $rowenum['value'] . "'>" . $rowenum['value'] . "<br>";
                                        }


                                    }
                                    if ($row_attrform['form_field_type'] == "selectbox") {
                                        echo "<select name='" . $row_attrform['form_field_name'] . "'>";
                                        while ($rowenum = mysqli_fetch_assoc($resultallowed)) {
                                            echo "<option value='" . $rowenum['value'] . "'>" . $rowenum['value'] . "</option><br>";
                                        }
                                        echo"</select>";

                                    }
                                }
                                else
                                {
                                    echo "<p>O atributo não tem valores permitidos</p><br>";
                                }
                                break;
                            case "obj_ref":
                                if($row_attrform['mandatory'])
                                {
                                    echo"<p class='obriga'>".'O atributo '.$row_attrform['name'].' é obrigatório'."</p>";
                                }
                                else
                                {
                                    echo"<p class='obriga'>".'O atributo '.$row_attrform['name'].' não é obrigatório'."</p>";
                                }
                                $attid = $row_attrform['id'];
                                $obj_fk = $row_attrform['obj_fk_id'];
                                $queryStringobj_inst = "Select obj_inst.id, obj_inst.object_id,	obj_inst.object_name From obj_inst,attribute WHERE obj_inst.object_id=$obj_fk AND attribute.obj_fk_id=$obj_fk AND attribute.id=$attid ORDER BY obj_inst.id";
                                $resultalobj_inst=$link->query($queryStringobj_inst);
                                if($resultalobj_inst->num_rows>0) {
                                    echo "<select name='" . $row_attrform['form_field_name'] . "'>";
                                    while ($rowinst = mysqli_fetch_assoc($resultalobj_inst)) {
                                        echo "<option value='" . $rowinst['id'] . "'>" . $rowinst['object_name'] . "</option><br>";
                                    }
                                    echo "</select>";
                                }
                                else
                                {
                                    echo "<p>O atributo não tem objetos instanciados</p><br>";
                                }
                                break;
                        }
                    }
                }
                else
                {
                    echo "<p>Atributo não está ativo</p>";
                }
            }
        }
        else
        {
            echo "<p>O formulário não tem atributos associados</p><br>";
        }
        echo"<br><br><input type='text' placeholder='Nome para instância do objeto (Opcional)' name='nome_instace'><br><br>
            <input name='estado' value='validar' type='hidden'>
            <input type='submit' name='insertsub' value='Submeter'>";
        echo"</form>
        </div></div>";
    }
    else if ($_REQUEST['estado'] == "validar" AND $_REQUEST['obj'])
    {
        $obj_id=$_SESSION['obj_id'];
        $obj_type_id=$_SESSION['obj_type_id'];
        $obj_name=$_SESSION['obj_name'];
        echo "<div class='lists'><h3 class='InsertHeader'>".'Inserção de valores - '.$obj_name.' - validar'."</h3>";
        $query_attributes = "SELECT * FROM attribute WHERE attribute.obj_id =$obj_id AND attribute.state = 'active' ";
        $resultattrs = $link->query($query_attributes);
        echo "<p class='validar'>Estamos prestes a inserir os dados abaixo na base de dados. Confirma que os dados estão correctos e pretende submeter os mesmos?</p>";

            while ($row_attrform = mysqli_fetch_assoc($resultattrs))
            {
                if($row_attrform['mandatory'])
                {
                    $result= mysqli_real_escape_string($link, $_REQUEST[$row_attrform['form_field_name']]);
                    if(empty($result))
                    {
                        echo "<div class='insertion_verify'>";
                        echo "<p>".'O campo '.$row_attrform['name'].' é de preenchimento obrigatório'."</p><br>";        //Caso o campo não tenha sido preenchido
                        voltar_atras();
                        exit();
                        echo "</div>";
                    }
                    else
                    {
                            echo "<li>".'Atributo: '.$row_attrform['name'].' -> '.$result."</li>";
                    }
                }
                else if(!$row_attrform['mandatory'])
                {   $result= mysqli_real_escape_string($link, $_REQUEST[$row_attrform['form_field_name']]);
                        echo "<li>".'Atributo: '.$row_attrform['name'].' -> '.$result."</li>";

                }
            }
        echo "<div class='row' style='text-align: center'>
        <form id='insert-form' method='POST' action='?estado=inserir&obj=$obj_id'>";
        $query_attributes = "SELECT * FROM attribute WHERE attribute.obj_id =$obj_id AND attribute.state = 'active' ";
        $resultattrs = $link->query($query_attributes);
        while ($row_attrform = mysqli_fetch_assoc($resultattrs))
        {
            $result= mysqli_real_escape_string($link, $_REQUEST[$row_attrform['form_field_name']]);
            echo "<input name='".$row_attrform['form_field_name']."' value='$result' type='hidden'>";
        }
        echo "<input type='submit' name='insertsub' value='Submeter'>
        </form>
        </div></div>";

    }
    else if ($_REQUEST['estado'] == "validar" AND $_REQUEST['form'])
    {
        $form_id=$_SESSION['form_id'];
        $form_name=$_SESSION['form_name'];
        echo "<div class='lists'><h3 class='InsertHeader'>".'Inserção de valores - '.$form_name.' - validar'."</h3>";
        $query_form_att = "SELECT * FROM custom_form_has_attribute WHERE custom_form_has_attribute.custom_form_id=$form_id";
        $resultformatt = $link->query($query_form_att);
        echo "<p class='validar'>Estamos prestes a inserir os dados abaixo na base de dados. Confirma que os dados estão correctos e pretende submeter os mesmos?</p>";
        if($resultformatt->num_rows>0)
        {
            while ($row_has_att=mysqli_fetch_assoc($resultformatt)) {
                $form_att_id = $row_has_att['attribute_id'];
                $query_attributes = "SELECT * FROM attribute WHERE id =$form_att_id AND attribute.state = 'active' ";
                $resultattrs = $link->query($query_attributes);
                while($row_attrform = mysqli_fetch_assoc($resultattrs))
                {
                    if ($row_attrform['mandatory']) {
                        $result = mysqli_real_escape_string($link, $_REQUEST[$row_attrform['form_field_name']]);
                        if (empty($result)) {
                            echo "<div class='insertion_verify'>";
                            echo "<p>" . 'O campo ' . $row_attrform['name'] . ' é de preenchimento obrigatório' . "</p><br>";        //Caso o campo não tenha sido preenchido
                            voltar_atras();
                            exit();
                            echo "</div>";
                        } else {
                                echo "<li>" . 'Atributo: ' . $row_attrform['name'] . ' -> ' . $result . "</li>";

                        }
                    } else if (!$row_attrform['mandatory']) {
                        $result = mysqli_real_escape_string($link, $_REQUEST[$row_attrform['form_field_name']]);
                            echo "<li>" . 'Atributo: ' . $row_attrform['name'] . ' -> ' . $result . "</li>";

                    }
                }
            }
            echo "<div class='row' style='text-align: center'>
            <form id='insert-form' method='POST' action='?estado=inserir&form=$form_id'>";
            $query_attributes = "SELECT * FROM custom_form_has_attribute WHERE custom_form_has_attribute.custom_form_id=$form_id ";
            $resultattrs = $link->query($query_attributes);
            while ($row_attrform = mysqli_fetch_assoc($resultattrs))
            {   $form_att_id = $row_attrform['attribute_id'];
                $query_attributes2 = "SELECT * FROM attribute WHERE id =$form_att_id AND attribute.state = 'active' ";
                $resultattrs2 = $link->query($query_attributes2);
                while($row_attrform2 = mysqli_fetch_assoc($resultattrs2)) {
                    $result = mysqli_real_escape_string($link, $_REQUEST[$row_attrform2['form_field_name']]);
                    echo "<input name='" . $row_attrform2['form_field_name'] . "' value='$result' type='hidden'>";
                }
            }
            echo "<input type='submit' name='insertsub' value='Submeter'>
            </form>
            </div></div>";

            }

    }
    else if ($_REQUEST['estado'] == "inserir" AND $_REQUEST['obj'])
    {
        $obj_id=$_SESSION['obj_id'];
        $obj_type_id=$_SESSION['obj_type_id'];
        $obj_name=$_SESSION['obj_name'];

        echo "<div class='lists'><h3 class='InsertHeader'>" . 'Inserção de valores - ' . $obj_name . ' -  inserção' . "</h3>";

        $query_attributes = "SELECT * FROM attribute WHERE attribute.obj_id =$obj_id AND attribute.state = 'active' ";
        $resultattrs = $link->query($query_attributes);

        while ($row_attrform = mysqli_fetch_assoc($resultattrs))
        {
           $result= mysqli_real_escape_string($link, $_REQUEST[$row_attrform['form_field_name']]);
           $attr_name = $row_attrform['name'];
           $query_string_insert= "INSERT INTO inserçaofinal_obj (Obj_id,Obj_name,attr_name,value) 
                                    VALUES ('$obj_id','$obj_name','$attr_name','$result')";
            $result_db = $link->query($query_string_insert);

            if (!$result_db) {
                echo "<div class='InserQuery'><h4>Lamentamos, mas a sua inserção de dados falhou!</h4></div>";
                echo "Error: " . mysqli_error($link);     //Query falhada
                voltar_atras();
                exit();
            }
        }
        echo "<div class='insert_style'>
                <div class='Inser_success'><h4>Inseriu os dados com sucesso.</h4></div> <br>
                <p>Clique <a href='insercao-de-valores'> aqui </a> para continuar.</p>   
                </div>";  //query inserida com sucesso

    }
    else if ($_REQUEST['estado'] == "inserir" AND $_REQUEST['form'])
    {
        $form_id=$_SESSION['form_id'];
        $form_name=$_SESSION['form_name'];
        echo "<div class='lists'><h3 class='InsertHeader'>".'Inserção de valores - '.$form_name.' -  inserção'."</h3>";

        $query_form_att = "SELECT * FROM custom_form_has_attribute WHERE custom_form_has_attribute.custom_form_id=$form_id";
        $resultformatt = $link->query($query_form_att);

        if($resultformatt->num_rows>0)
        {
            while ($row_has_att=mysqli_fetch_assoc($resultformatt)) {
                $form_att_id = $row_has_att['attribute_id'];
                $query_attributes = "SELECT * FROM attribute WHERE id =$form_att_id AND attribute.state = 'active' ";
                $resultattrs = $link->query($query_attributes);
                while($row_attrform = mysqli_fetch_assoc($resultattrs))
                {
                    $result= mysqli_real_escape_string($link, $_REQUEST[$row_attrform['form_field_name']]);
                    $attr_name = $row_attrform['name'];
                    $query_string_insert2= "INSERT INTO inserçaofinal_form (Form_id,Form_name,attr_name,value) 
                                    VALUES ('$form_id','$form_name','$attr_name','$result')";
                    $result_db2 = $link->query($query_string_insert2);
                    if (!$result_db2) {
                        echo "<div class='InserQuery'><h4>Lamentamos, mas a sua inserção de dados falhou!</h4></div>";
                        echo "Error: " . mysqli_error($link);     //Query falhada
                        voltar_atras();
                        exit();
                    }
                }
            }
        }
        echo "<div class='insert_style'>
                <div class='Inser_success'><h4>Inseriu os dados com sucesso.</h4></div> <br>
                <p>Clique <a href='insercao-de-valores'> aqui </a> para continuar.</p>   
                </div>";  //query inserida com sucesso
    }
}
else
{
    echo "<div class='naoaut'><p>Não tem autorização para aceder a esta página</p></div>";

}
?>