<?php

function evasionview_report_extend_navigation($reportnav, $course, $context) {
    $url = new moodle_url('/course/report/evasionview/index.php', array('id' => $course->id));
    $reportnav->add(get_string('pluginname', 'coursereport_evasionview'), $url);
}

function funcao_teste() {
    echo "<script type='text/javascript'>
        function clicou(){
            var a = document.getElementById('plugintitle');
            a.style.color = 'blue';
        }
      </script>";
}

// função para impressão do bread crumb e as páginas referentes a cada solicitação do usuário
function print_navigation_evasionview($params, $url) {
    global $OUTPUT;
//        Incluir aqui o que se espera quando o usuário requer a visualização do grupo de risco dos usuários
    if ($params['group']) {
//            print_icon_evasionview('group');
        echo "<div class='view-title'>
              <strong><p>Selecione um usuário para detalhar suas informações.</strong>
              </p></div>";                
        
        
        print_group_view($params['group'], $params['id']);
        } else {
//        Incluir aqui o que se espera quando o usuário requer a visualização das informações sobre um usuário em específico            
        if ($params['userinfo']) {
//            print_icon_evasionview('userinfo');  
            echo "<div class='view-title'><strong><p>Detalhamento dos dados de notas, interações e acessos do usuário.</strong></p></div>";

            print_user($params['id'], $params['userinfo'], $_POST['d_inicial'], $_POST['d_final']);
//            echo $OUTPUT->action_link(new moodle_url($url, array('usersend'=>$params['userinfo'])), "Navegar para enviar mensagem");            
        } else {
//        Incluir aqui o que se espera quando o usuário requer notificar um usuário selecionado no grupo de risco
            if ($params['usersend']) {
//                print_icon_evasionview('usersend');                
                echo "<div class='view-title'><h3>User Send Message</h3></div>";
                echo $OUTPUT->action_link(new moodle_url($url), "Navegar para Home");
            } else {
                echo "<h1 align='center'>Evasion View</h1>";
//        Incluir aqui o que se espera quando o usuário entra no link principal do plugin
//                    print_icon_evasionview('home');                    
                echo "<div class='view-title'><strong><p>Selecione no gráfico para exibir os alunos do grupo de risco.</strong></p></div>";
                
//                    echo $OUTPUT->action_link(new moodle_url($url,array('group'=>3)),"Navegar para Grupo de Usuários");
                if (search_users($params['id'])) {
                    $groups_grades = get_group_grades_evasionview($params['id']);                                         
                    $groups_access = get_group_access_evasionview($params['id']);                                                             
                    echo "<div id='container_index' >";
                        echo "<div id='piechart' >";
                                grafchartjs($groups_grades);
                        echo "</div>";
                        echo "<div class='pie_info1'>";
                            echo "<div class='group-subtitle'><h5 id='good-group'>Acima de média</h5><p>Estudantes com desempenho maior que 70%.</p></div>";
                            echo "<div class='group-subtitle'><h5 id='fair-group'>Se aproximando da média</h5><p>Estudantes com desempenho entre que 50 e 70%.</p></div>";
                            echo "<div class='group-subtitle'><h5 id='poor-group'>Abaixo da média</h5><p>Estudantes com desempenho inferior a 50%.</p></div>";
                            echo "<div class='group-subtitle'><h5 id='null-group'>Alunos que não fizeram atividades</h5><p>Estudantes sem lançamento de notas.</p></div>";
                        echo "</div>";
                    echo "</div>";
                    echo "<div id='container_access' style='border-top: 2px solid lightgrey;'>";                                
                                echo "<div id='barchart' >";                                
                                    grafbarchartjs($groups_access);
                                echo "</div>";
//                                var_dump($groups_access);
                                echo "<div class='pie_info1'>";
                                    echo "<div class='group-subtitle'><h5 id='group_no'>0 acesso</h5><p>Estudantes sem lançamento de notas.</p></div>";                                        
                                    echo "<div class='group-subtitle'><h5 id='group_one'>De 1 á 5 acessos</h5><p>Estudantes com desempenho inferior a 50%.</p></div>";
                                    echo "<div class='group-subtitle'><h5 id='group_six'>De 6 á 15 acessos</h5><p>Estudantes com desempenho entre que 50 e 70%.</p></div>";
                                    echo "<div class='group-subtitle'><h5 id='group_fifteen'>De 15 á 30 acessos</h5><p>Estudantes com desempenho maior que 70%.</p></div>"; 
                                    echo "<div class='group-subtitle'><h5 id='group_thirty'>De 31 á 50 acessos</h5><p>Estudantes sem lançamento de notas.</p></div>";    
                                    echo "<div class='group-subtitle'><h5 id='group_fifty'>De 51 á 99 acessos</h5><p>Estudantes sem lançamento de notas.</p></div>";    
                                    echo "<div class='group-subtitle'><h5 id='group_more'>Mais 100 acessos</h5><p>Estudantes sem lançamento de notas.</p></div>";    
                                echo "</div>";
                    echo "</div>";
                } else {
                    echo "<div style='text-align:center'>
                                <img id='no-users-img' src='img/logoplugin.png' alt='logoAvaMoodle'/>
                                <div class='alert'>
                                Não há usuários cadastrados para esse curso
                                </div>                
                           </div>";
                }                
            }
        }
    }
}

function print_icon_evasionview($param) {
    $breadcrumb = array('home', 'group', 'userinfo', 'usersend');

    echo "<div class='nav-evasion' style='text-align:center'>";
    foreach ($breadcrumb as $key => $value) {
        if ($param != $value) {
            $opacity = "style='opacity:0.1'";
        } else {
            $opacity = "";
        }
        echo "<div id='nav-icon' $opacity>
            <img class='bread' src='img/$value.png' alt='global'/>
           </div>";
    }
    echo "</div>";
}

function get_group_grades_evasionview($courseid) {
    $usersCourse = search_users($courseid);
    $groups = null;
    $good_group = array();
    $fair_group = array();
    $poor_group = array();
    $null_group = array();

    if ($usersCourse) {
        foreach ($usersCourse as $key => $user) {
            $grade = grade_progress($courseid, $user->id);
            $nota = null;
            foreach ($grade as $value2) {
                $nota = $value2->sum;
            }
            if ($nota != null) {
                if ($nota < 5) {
                    $poor_group[] = $user;
                } else {
                    if ($nota >= 5 && $nota < 7) {
                        $fair_group[] = $user;
                    } else {
                        if ($nota >= 7) {
                            $good_group[] = $user;
                        }
                    }
                }
//                echo "Nome: $user->firstname valor: $nota<br>";
            } else {
//                echo "Nome: $user->firstname valor: - <br>";            
                $null_group[] = $user;
            }
        }

//        echo "Grupo com notas Boas". count($good_group);
//        echo "Grupo com notas Médias". count($fair_group);
//        echo "Grupo com notas Ruins". count($poor_group);
//        echo "Grupo sem notas notas". count($null_group);
        $groups = array('good' => $good_group, 'fair' => $fair_group, 'poor' => $poor_group, 'null' => $null_group);
    } else {
        echo "Não Tem Alunos";
    }
    return $groups;
}

function get_group_access_evasionview($courseid) {
    $usersCourse = search_users($courseid);
    
    $noaccess = array();    
    $onetofive = array();
    $sixtofifteen = array();
    $sixteentothirty = array();
    $thirtyonetofifty = array();
    $fiftyonetoninetynine = array();
    $moreThan = array();            
    
    foreach ($usersCourse as $user) {                                             
//            echo "$user->firstname: ";            
            $user_access = get_access_user($courseid, $user->id); 
//            var_dump($user_access);
            if($user_access){
                foreach ($user_access as $user) {
//                    var_dump($user);
                    if ($user->count > 0 && $user->count <= 5){
                        $onetofive[] = ($user);
                    }else{
                        if ($user->count > 5 && $user->count <= 15){
                            $sixtofifteen[] = ($user);
                        }else{
                            if ($user->count > 15 && $user->count <= 30){
                                $sixteentothirty[] = ($user);
                            }else{
                                if ($user->count > 30 && $user->count <= 50){
                                    $thirtyonetofifty[] = ($user);
                                }else{
                                    if ($user->count > 50 && $user->count <= 99){
                                    $fiftyonetoninetynine[] = ($user);
                                }else{
                                    $moreThan[]=($user);
                                }
                                }
                            }
                        }
                    }
//                    echo $user->count;
                }   
            }else{
//                echo 0;
                $noaccess[] = ($user);
            }   
//            echo " acessos.<br>";
        }  
        
        $groups = array('noaccess'=>$noaccess, 
                    'onetofive'=>$onetofive,
                    'sixtofifteen'=>$sixtofifteen,
                    'sixteentothirty'=>$sixteentothirty,                    
                    'thirtyonetofifty' => $thirtyonetofifty,
                    'fiftyonetoninetynine' => $fiftyonetoninetynine,
                    'moreThan'=>$moreThan,
                    );    
        
        return $groups;        
}

function grafchartjs($groups) {
    $good = count($groups['good']);
    $fair = count($groups['fair']);
    $poor = count($groups['poor']);
    $null = count($groups['null']);
    echo "<canvas id='myChartPie' width='400' height='400'></canvas>";

    echo "<script src='js/Chart.min.js'></script>";

    echo "<script>
        var ctx = document.getElementById('myChartPie').getContext('2d');
        var myChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['Good', 'Fair', 'Poor', 'Null'],
                
                datasets: [{
                    label: 'Percentual de contribuição',
                    
                    data: [$good, $fair, $poor, $null ],
                    backgroundColor: [
                        'rgba(0, 232, 0, 1)',
                        'rgba(255, 235 , 59, 1)',
                        'rgba(200, 0, 0, 1)',
                        'rgba(128, 128, 128, 1)'                        
                    ],
                    borderColor: [
                        'rgba(255, 255, 255,1)',
                        'rgba(255, 255, 255, 1)',
                        'rgba(255, 255, 255, 1)',
                        'rgba(255, 255, 255, 1)'                        
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                events: ['click','mousemove','touchmove'],
                title:{
                    display: true,
                    text: 'Gráfico do desempenho de notas dos alunos',
                    fontSize: '22'
                    
                }
            }        
        });                
        
        document.getElementById('myChartPie').onclick = function(evt){                        
            var activeElements = myChart.getElementsAtEvent(evt);            
                
        if(activeElements.length > 0)
            {
            var clickedElementindex = activeElements[0]['_index'];      
            
            var label = myChart.data.labels[clickedElementindex];            
//            var value = myChart.data.datasets[0].data[clickedElementindex];      
//            document.getElementById('next').style.setProperty('visibility','visible');
                                    var parsedUrl = new URL(window.location.href);
                                    console.log(parsedUrl);                                    
                                    parsedUrl.searchParams.set('group',label);                                    
                                    window.location.href = parsedUrl;
                console.log(label);
            }
        };
        </script>";
}

function grafbarchartjs($groups_access) {        
    
    $noaccess = count($groups_access['noaccess']);    
    $onetofive = count($groups_access['onetofive']);    
    $sixtofifteen = count($groups_access['sixtofifteen']);    
    $sixteentothirty = count($groups_access['sixteentothirty']);    
    $thirtyonetofifty = count($groups_access['thirtyonetofifty']);    
    $fiftyonetoninetynine = count($groups_access['fiftyonetoninetynine']);        
    $morethan = count($groups_access['morethan']);    
        
    
    echo "<div style='width: 100%'>
            <div class='bar-chart'>
                <canvas id='myChart' width='400' height='400'></canvas>                            
            </div>                        
        </div>                        

        <script>
    
    var ctx = document.getElementById('myChart').getContext('2d');
    var myBarChart = new Chart(ctx, {        
    type: 'bar',        
    data: {                        
        datasets: [{                    
                    label: '0 ...',                   
                    data: [$noaccess],
                    backgroundColor: [
                        'rgba(255, 0, 0, 1)'                        
                    ],
                    borderColor: [
                        'rgba(255, 0, 0,1)'                        
                    ],
                    borderWidth: 1
                },{                    
                    label: '1 - 5',                   
                    data: [$onetofive],
                    backgroundColor: [                        
                        'rgba(255, 106, 61, 1)'                        
                    ],
                    borderColor: [                        
                        'rgba(255, 106, 61, 1)'                        
                    ],
                    borderWidth: 1
                },{                    
                    label: '6 - 15',                   
                    data: [$sixtofifteen],
                    backgroundColor: [
                        'rgba(255, 203, 61, 1)'                        
                    ],
                    borderColor: [
                        'rgba(255, 203, 61, 1)'                        
                    ],
                    borderWidth: 1
                },{                    
                    label: '16 - 30',                   
                    data: [$sixteentothirty],
                    backgroundColor: [
                        'rgba(255, 235 , 59, 1)'                        
                    ],
                    borderColor: [
                        'rgba(255, 235 , 59,1)'                        
                    ],
                    borderWidth: 1
                },{                    
                    label: '31 - 50',                   
                    data: [$thirtyonetofifty],
                    backgroundColor: [
                        'rgba(161, 255, 61, 1)'                        
                    ],
                    borderColor: [
                        'rgba(161, 255, 61, 1)'                        
                    ],
                    borderWidth: 1
                },{                    
                    label: '51 - 99',                   
                    data: [$fiftyonetoninetynine],
                    backgroundColor: [
                        'rgba(100, 255, 0, 1)'                        
                    ],
                    borderColor: [
                        'rgba(100, 255, 0, 1)'                        
                    ],
                    borderWidth: 1
                },{                    
                    label: '100 ...',                   
                    data: [$morethan],
                    backgroundColor: [
                        'rgba(0, 255, 0, 1)'                        
                    ],
                    borderColor: [
                        'rgba(0, 255, 0,1)'                        
                    ],
                    borderWidth: 1
                }                
            ]
        },
    options: {
        event:['click'],
        animation: false,
        responsive: true,        
        legend: {
            display: true,
            labels: {                
                fontColor: 'rgb(255, 99, 132)'                
            }
        },
        scales: {
            yAxes: [{                
                ticks: {                    
                    beginAtZero: true
                }
            }]
        },
        title:{
            display: true,
            text: 'Gráfico de acessos dos usuários',
            fontSize: '22',
        }
    }
});

        document.getElementById('myChart').onclick = function(evt){                                    
            var activeElements = myBarChart.getElementAtEvent(evt);                        
            
            if(activeElements.length > 0)
            {
                var clickedElementindex = activeElements[0]['_datasetIndex'];            
                label = myBarChart.data.datasets[clickedElementindex].label;
                var parsedUrl = new URL(window.location.href);            
                parsedUrl.searchParams.set('group',label);                                                
                window.location.href = parsedUrl;          
            }
            
        };

</script>";
}

function grade_progress($courseid, $userid) {
    global $DB;
    $grade_progress = $DB->get_records_sql("select sum((gg.finalgrade :: bigint) * gi.aggregationcoef2)
                                    from public.mdl_grade_items as gi 
                                    join public.mdl_grade_grades as gg on
                                    gi.id = gg.itemid 
                                    join public.mdl_user as us
                                    on gg.userid = us.id
                                    join public.mdl_course as c on
                                    gi.courseid = c.id
                                    where gg.userid = us.id and c.id = $courseid 
                                    and gi.itemtype != 'course' and us.id = $userid");

    return $grade_progress;
}

function print_group_view($group, $courseid) {
    echo $group;
    global $OUTPUT;
    
    $groups = get_group_grades_evasionview($courseid); 
    $groups_access = get_group_access_evasionview($courseid);            
    
    if ($group != null) {
        switch ($group) {
            case 'Good':
                $group = $groups['good'];
                break;
            case 'Fair':
                $group = $groups['fair'];
                break;
            case 'Poor':
                $group = $groups['poor'];
                break;
            case 'Null':
                $group = $groups['null'];
                break;
            case '0 ...':
                $group = $groups_access['noaccess'];                
                break;
            case '1 - 5':
                $group = $groups_access['onetofive'];
                break;
            case '6 - 15':
                $group = $groups_access['sixtofifteen'];
                break;
            case '16 - 30':
                $group = $groups_access['sixteentothirty'];
                break;
            case '31 - 50':
                $group = $groups_access['thirtyonetofifty'];
                break;
            case '51 - 99':
                $group = $groups_access['fiftyonetoninetynine'];
                break;
            case 'moreThan':
                $group = $groups_access['morethan'];
                break;
            default:
                echo "<h1>Nada para ser mostrado!</h1>";
                break;
        }
        
        $letras = array("all"=>"all");
        
        foreach ($group as $user) {
            $l = substr($user->firstname,0,1); 
            $letras["$l"] = "$l";
        }                
        
        echo "<form action='' method='post'><label>Filtrar por inicial</label><select name='first_char'>";                
        foreach($letras as $letra){                        
                echo "<option value='$letra'>$letra</option>";              
            }
        echo "</select>";
        echo "<input type='submit' value='Filtrar'></input>";
        echo "</form>";
        
        $first_char = null;
        
        if(isset($_POST['first_char'])){
            echo $_POST['first_char'];
            $first_char = $_POST['first_char'];
        }

        foreach ($group as $user) {
            $access_user = get_access_user($courseid, $user->id, null);
            $progresses = grade_progress($courseid, $user->id);
            foreach ($progresses as $user_progress) {
                $progress = $user_progress->sum;
            }
            echo "<div style='overflow: scroll; max-height: 415px;'>";
            for ($i = 0; $i < 4; $i++) {                
                if($first_char){
                    if($first_char == 'all'){
                        print_simple_user($courseid, $user->id, $user->firstname, $user->lastname, $progress, $access_user);                                        
                    }else{
                        if(substr($user->firstname,0,1) == $first_char){
                           print_simple_user($courseid, $user->id, $user->firstname, $user->lastname, $progress, $access_user);                                        
                           }   
                    }                                     
                    }else{                        
                        print_simple_user($courseid, $user->id, $user->firstname, $user->lastname, $progress, $access_user);                
                    }                    
                }
            echo "</div";
        }
    } else {
        echo "<h1>Nada para ser mostrado!</h1>";
    }
}

function get_grade_user($courseid, $userid, $datainicial, $datafinal) {

    if ($datainicial != null) {
        $mindatefilter = "and to_timestamp(gi.timecreated) >= to_timestamp('$datainicial', 'YYYY-MM-DD')";
    }

    if ($datafinal != null) {
        $maxdatefiilter = "and to_timestamp(gi.timecreated) <= to_timestamp('$datafinal', 'YYYY-MM-DD')";
    }

    global $DB;
    $grade_user = $DB->get_records_sql(
            "select gi.itemname as atividade,
                (gi.aggregationcoef2 * 100) :: numeric(10,2)as contAtividade,
                gi.grademin :: numeric(10,2)as notaMinima, 
                gi.grademax :: numeric(10,2) as notaMaxima, 
                gg.finalgrade :: numeric(10,2) as notaObtida,
                ((gg.finalgrade ::numeric(10,2) * gi.aggregationcoef2)*10):: numeric(10,2) as contribuicao
                from public.mdl_grade_items as gi 
                join public.mdl_grade_grades as gg on
                gi.id = gg.itemid 
                join public.mdl_user as us
                on gg.userid = us.id
                join public.mdl_course as c on
                gi.courseid = c.id
                where gg.userid = us.id and c.id = $courseid 
                and gi.itemtype != 'course' and us.id = $userid
                $mindatefilter
                $maxdatefiilter"
    );

    return $grade_user;
}

function print_simple_user($courseid, $userid, $userfirstname, $userlastname, $userprogress, $useraccess) {
    global $OUTPUT;
    $access;
    foreach ($useraccess as $value) {
        $access = $value->count;
    }
    echo "<div id='grid-user-info'>
                                <div id='user-info-title'><strong>User id: $userid</strong>
                                    <div id='user-info'>First Name: $userfirstname</div></div>
                                <div id='contents'>                                                                                                
                                <div id='user-info'>Last Name: $userlastname</div>
                                <div id='user-info'>Progresso: $userprogress</div>
                                <div id='user-info'>Acessos durante o curso: $access</div>                                
                                <div id='user-info-return'>
                                ";
    echo $OUTPUT->action_link(new moodle_url($url, array('id' => $courseid, 'userinfo' => $userid)), "Detalhar dados");
    echo "</div>";
    echo "</div>";
    echo "</div>";
}

//function print_user($grade_user, $access_user ,$userid) {
function print_user($courseid, $userid, $datainicial, $datafinal) {
    global $DB;
    global $OUTPUT;
    $startdatecourse = get_course_start_date($courseid);

    if (!$datainicial) {
        $datainicial = $startdatecourse[$courseid]->startdate;
    }

    if (!$datafinal) {
        $datafinal = date("Y-m-d");
    }

    $mindate = $startdatecourse[$courseid]->startdate;
    $maxdate = date("Y-m-d");

    echo "<p onclick='clicou()' >Clique aqui!</p>";

//  Resgata do banco as notas obtidas pelo usuário pesquisado
    $grade_user = get_grade_user($courseid, $userid, $datainicial, $datafinal);

//  Resgata do banco os acessos do usuário no curso
    $access_user = get_list_access_user($courseid, $userid, $datainicial, $datafinal);

    $options = array('size' => 100);

    $user = $DB->get_record("user", array("id" => $userid, 'deleted' => 0), '*', MUST_EXIST);
//    var_dump($user);
    echo "<div id='card-user'>";
    echo "<div id='col-img'>";
    echo $OUTPUT->user_picture($user, $options);
    echo "<table>
            <thead>
                <tr>
                    <th>Id</th>
                    <th>Nome</th>
                    <th>Sobrenome</th>                    
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>$user->id</td>
                    <td>$user->firstname</td>
                    <td>$user->lastname</td>                    
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                </tr>
            </tbody>
        </table>";
    echo "<div>";
    echo "<a data-trigger='core_message-messenger::sendmessage' data-fullname='Segundo Second' data-userid='4' role='button' class='btn' href='http://localhost/moodle-latest-31/moodle/message/index.php?id=4' id='yui_3_17_2_1_1539259101262_109'><span id='yui_3_17_2_1_1539259101262_108'><img class='iconsmall' role='presentation' alt='Message' title='Message' src='http://localhost/moodle-latest-31/moodle/theme/image.php/clean/core/1536223182/t/message'><span class='header-button-title' id='yui_3_17_2_1_1539259101262_107'>Message</span></span></a>";
//        echo $OUTPUT->action_link(new moodle_url($url, array('id'=>$_GET['id'],'usersend'=>$user->id)), "Mensagem");
    echo "</div>";
    echo "</div>";

    echo"<div>
        <h4>Filtros<h4/>
        <form style='display:flex;'name='data' method='POST' action=''>                  
                 <label style='padding:5px;'>De: </label><input type='date' name='d_inicial' min='$mindate' max='$maxdate' value='$datainicial'>
                 <label style='padding:5px;'>Até: </label><input type='date' name='d_final' min='$mindate' max='$maxdate' value='$datafinal'>
                 <label style='padding: 5px;'>Nome</label><input type='text' name='p_nome'>    
                 <input type='submit' value='Filtrar'>
        </form>";
        echo "</div>";
    echo "<h3>Notas</h3>";
    if ($grade_user) {
        echo "<div id='table-user'>";
        foreach ($grade_user as $grade) {

            if (!$grade->notaobtida)
                $notaobtida = 0;
            else
                $notaobtida = $grade->notaobtida;

            if (!$grade->contribuicao)
                $contribuicao = "0";
            else
                $contribuicao = $grade->contribuicao;

//                Inserção da div com atividades
            echo "<div style='display: grid; grid-template-rows: auto auto auto; background-color: lightgrey; margin: 4px'>"
            . "<div style='margin: 2px;background-color: whitesmoke;' ><h4>$grade->atividade</h4></div>"
            . "<div style='display: grid; grid-template-columns: auto auto auto auto'>"
            . "<div style='display: grid; grid-template-rows: auto auto'><p>Nota Minima<p/>$grade->notaminima</div>"
            . "<div style='display: grid; grid-template-rows: auto auto'><p>Nota Máxima</p>$grade->notamaxima</div>"
            . "<div style='display: grid; grid-template-rows: auto auto'><p>Nota Obtida</p>$notaobtida</div>"
            . "<div style='display: grid; grid-template-rows: auto auto'><p>Contribuição para o curso</p>$contribuicao %</div>"
            . "</div>";
            echo "</div>";
        }
//            echo "</tbody></table>";                        
        echo "</div>";
    }else {
        echo "<div style='text-align:center'>                                
              <div class='alert'>
              Não há notas no período selecionado!
              </div>                
              </div>";
    }

    echo "<h4>Acessos</h4>";
//            echo "<div id='table-access-user'>";                            
    if ($access_user) {
        echo "<div id='table-access-user'>";
//            foreach ($access_user as $access) {
        $a = 0;
        foreach ($access_user as $key => $access) {
            $style = "";
            if ($a % 2) {
                $style = "style='background-color: transparent;'";
            }
            echo "<div style='margin-bottom: 5px;'>";
//            echo "<div $style class='card_access_user'>";
            echo "<div class='card_access_user'>";
            echo "<p style='background-color: whitesmoke;'>Evento</p>";
            echo "<p style='background-color: whitesmoke;'>$access->eventname</p>";
            echo "<p>Data</p>";
            echo "<p>$access->evtdate</p>";
            echo "</div>";
            echo "</div>";
            $a++;
        }
        echo "</div>";
    } else {
        echo "<div style='text-align:center'>                                
                                <div class='alert'>
                                    Não há acessos no período selecionado!
                                </div>                
                           </div>";
    }
//            echo "</div>";
}

function get_access_user($courseid, $userid, $filter) {
    global $DB;
    global $OUTPUT;
    $condition;

    switch ($filter) {
        case "all_times":
            echo "Todo curso";
            break;
        case "three_months":
            echo "Nos últimos 3 mêses";
            $condition = "and to_timestamp(timecreated) > current_date - interval '3 months'";
            break;
        case "month":
            echo "No ultimo mês";
            $condition = "and to_timestamp(timecreated) > current_date - interval '1 month'";
            break;
        case "week":
            echo "Na última semana";
            $condition = "and to_timestamp(timecreated) > current_date - interval '1 week'";
            break;
        default:
            break;
    }

    $sql = "SELECT u.firstname,u.lastname ,u.id, count(l.timecreated)
            FROM public.mdl_logstore_standard_log as l						
            join public.mdl_user as u
            on u.id = l.userid
            where userid = $userid
            and courseid = $courseid
            and to_timestamp(l.timecreated) >  CURRENT_DATE - INTERVAL '2 months'
            group by u.id";

    $access = $DB->get_records_sql($sql);

    return $access;
}

function get_list_access_user($courseid, $userid, $datainicial, $datafinal) {
    global $DB;
    global $OUTPUT;

    if ($datainicial != null) {
        $mindatefilter = "and to_timestamp(timecreated) >= to_timestamp('$datainicial', 'YYYY-MM-DD')";
    }

    if ($datafinal != null) {
        $maxdatefiilter = "and to_timestamp(timecreated) <= to_timestamp('$datafinal', 'YYYY-MM-DD')";
    }

    $sql = "SELECT id,eventname, to_char(to_timestamp(timecreated), 'DD-MM-YYYY') as evtdate  
            FROM public.mdl_logstore_standard_log l 
            where userid = $userid and courseid = $courseid 
            $mindatefilter
            $maxdatefiilter";

    $access = $DB->get_records_sql($sql);

    return $access;
}

function get_course_start_date($courseid) {
    global $DB;

    $startdate = null;
    $sql = "select id, to_char(to_timestamp(startdate),'YYYY-MM-DD') as startdate from public.mdl_course where id=$courseid";
    $startdate = $DB->get_records_sql($sql);

    return $startdate;
}
