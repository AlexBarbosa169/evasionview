<?php

function evasionview_report_extend_navigation($reportnav, $course, $context) {
    $url = new moodle_url('/course/report/evasionview/index.php', array('id' => $course->id));
    $reportnav->add(get_string('pluginname', 'coursereport_evasionview'), $url);
}

function funcao_teste(){
    echo "<script type='text/javascript'>
        function clicou(){
            var a = document.getElementById('plugintitle');
            a.style.color = 'blue';
        }
      </script>";
}

// função para impressão do bread crumb e as páginas referentes a cada solicitação do usuário
function print_navigation_evasionview($params,$url) {        
        global $OUTPUT;
//        Incluir aqui o que se espera quando o usuário requer a visualização do grupo de risco dos usuários
        if($params['group']){            
            print_icon_evasionview('group');
            print_group_view($params['group'], $params['id']);            
        }else{
//        Incluir aqui o que se espera quando o usuário requer a visualização das informações sobre um usuário em específico
            if($params['userinfo']){
            print_icon_evasionview('userinfo');  
//            inserir visualização para o usuário            
            $grade_user = get_grade_user($params['id'], $params['userinfo']);
            print_user($grade_user, $params['userinfo']);
//            echo $OUTPUT->action_link(new moodle_url($url, array('usersend'=>3)), "Navegar para enviar mensagem");            
            }else{
//        Incluir aqui o que se espera quando o usuário requer notificar um usuário selecionado no grupo de risco
                if($params['usersend']){
                print_icon_evasionview('usersend');                
                echo $OUTPUT->action_link(new moodle_url($url),"Navegar para Home");            
                }else{
//        Incluir aqui o que se espera quando o usuário entra no link principal do plugin
                    print_icon_evasionview('home');                    
//                    echo $OUTPUT->action_link(new moodle_url($url,array('group'=>3)),"Navegar para Grupo de Usuários");
                    if(search_users($params['id'])){
                    $groups = get_group_grades_evasionview($params['id']);
                    
                    $good_group = count($groups['good']);
                    $fair_group = count($groups['fair']);
                    $poor_group = count($groups['poor']);
                    $null_group = count($groups['null']);
                    
                    echo "<div id='container_index' >"; 
                    echo "<div id='piechart' >"; 
                    grafchartjs($good_group, $fair_group, $poor_group, $null_group);                    
                    echo "</div>";                     
                    echo "<div class='pie_info1'>";                    
                    echo "<div class='group-subtitle'><h5 id='good-group'>Good Group</h5><p>Grupo de estudantes com desempenho maior que 70%.</p></div>";
                    echo "<div class='group-subtitle'><h5 id='fair-group'>Fair Group</h5><p>Grupo de estudantes com desempenho entre que 50 e 70%.</p></div>";
                    echo "<div class='group-subtitle'><h5 id='poor-group'>Poor Group</h5><p>Grupo de estudantes com desempenho inferior a 50%.</p></div>";
                    echo "<div class='group-subtitle'><h5 id='null-group'>Null Group</h5><p>Grupo de estudantes sem lançamento de notas.</p></div>";                    
                    echo "</div>";                     
                    echo "</div>"; 
                    }else{
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
    $breadcrumb = array('home','group','userinfo','usersend');        
    echo "<div class='nav-evasion'>";
    foreach ($breadcrumb as $key => $value){
        if($param != $value){
            $opacity = "style='opacity:0.1'";
        }else{
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
       
    if($usersCourse){        
        foreach ($usersCourse as $key => $user) {                                                 
            $grade = grade_progress($courseid, $user->id);                    
            $nota = null;
            foreach ($grade as $value2) {                                
                $nota = $value2->sum;
            }    
            if($nota != null){
                if($nota < 5){
                    $poor_group[] = $user;
                }else{
                    if($nota >=5 && $nota < 7){
                        $fair_group[] = $user;
                    }else{
                        if($nota >= 7){
                            $good_group[] = $user;
                        }
                    }
                }
//                echo "Nome: $user->firstname valor: $nota<br>";
            }else{
//                echo "Nome: $user->firstname valor: - <br>";            
                $null_group[] = $user;            
            }                        
        }    
        
//        echo "Grupo com notas Boas". count($good_group);
//        echo "Grupo com notas Médias". count($fair_group);
//        echo "Grupo com notas Ruins". count($poor_group);
//        echo "Grupo sem notas notas". count($null_group);
        $groups = array('good'=>$good_group,'fair'=>$fair_group,'poor'=>$poor_group, 'null'=>$null_group);
    }else{
        echo "Não Tem Alunos";
    }
    return $groups;
}

function grafchartjs($good,$fair,$poor,$null){
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
                events: ['click','mousemove','touchmove']                                      
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

function grade_progress($courseid,$userid) {
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
    global $OUTPUT;
    $groups = get_group_grades_evasionview($courseid);
    
    if($group != null){
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
                default:
                    echo "<h1>Nada para ser mostrado!</h1>";
                break;
            }
            
            foreach ($group as $user) {                        
                        $progresses = grade_progress($courseid, $user->id);
                        foreach ($progresses as $user_progress){                                             
                            $progress = $user_progress->sum;
                        }
                        echo "<div style='overflow: scroll; max-height: 415px;'>";
                        for($i=0;$i<4;$i++){
                            print_simple_user($courseid, $user->id, $user->firstname, $user->lastname, $progress);     
                        }                        
                        echo "</div";
                    }
    }else{
        echo "<h1>Nada para ser mostrado!</h1>";
    }
    
}

function get_grade_user($courseid, $userid){
//    var_dump($user);
    
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
                and gi.itemtype != 'course' and us.id = $userid"
            );
    
            return $grade_user;
}

function print_simple_user($courseid, $userid, $userfirstname, $userlastname, $userprogress) {
    global $OUTPUT;
                            echo "<div id='grid' style='display: grid;
                                grid-template-columns: auto;
                                grid-template-rows: auto auto auto;
                                padding: 10px;
                                border: solid 1px #9E9E9E;
                                background-color: whitesmoke;                                
                                margin: 2px;                                
                                '>
                                <div id='user-info-title'><strong>User id: $userid</strong></div>
                                <div id='contents' style='display: grid;
                                grid-template-columns: auto auto auto auto;
                                grid-template-rows: auto auto auto;'>                                
                                <div id='user-info'>First Name</div>
                                <div id='user-info-return'>$userfirstname</div>
                                <div id='user-info'>Last Name</div>
                                <div id='user-info-return'>$userlastname</div>
                                <div id='user-info'>Progresso</div>
                                <div id='user-info-return'>$userprogress</div>
                                <div id='user-info'>Link</div>
                                <div id='user-info-return'>
                                ";
                                echo $OUTPUT->action_link(new moodle_url($url, array('id'=>$courseid,'userinfo'=>$userid)), "Detalhamento das notas");
                                echo "</div>";
                                echo "</div>";    
                            echo "</div>";         
}

function print_user($grade_user, $userid) {
    global $DB;
    global $OUTPUT;
    
    $options = array('size'=>200);     
    
    $user = $DB->get_record("user", array("id"=>$userid, 'deleted'=>0), '*', MUST_EXIST);             
//    var_dump($user);
    echo "<div style='display: grid; grid-template-columns: 1fr 4fr;
                                grid-template-rows: auto;'>";
    echo "<div id='image'>";
        echo $OUTPUT->user_picture($user,$options);
    echo "</div>";    
    
    echo "<div style='display: grid; grid-template-columns: 1fr;
                                grid-template-rows: 1fr 1fr;'>";    
            echo "<div>";
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
            echo "</div>";
            echo "<table>"
                    . "<thead>"
                    . "<th>Atividade</th>"
                    . "<th>Nota Miníma da Atividade</th>"
                    . "<th>Nota Máxima da Atividade</th>"
                    . "<th>Nota Máxima Obtida</th>"
                    . "<th>Contribuição para o curso</th>"
                    . "</thead><tbody>";			
            $array_atividades = array();
            $array_atividades_course = array();
//            var_dump($array_atividades);
            foreach ($grade_user as $grade) {                
//                var_dump($grade);
                if(!$grade->notaobtida)
                    $notaobtida = 0;
                else
                    $notaobtida = $grade->notaobtida;
                
                echo "<tr>";
                echo "<td>$grade->atividade</td>";
                $array_atividades[$grade->atividade] = $grade->contribuicao;                
                $array_atividades_course[$grade->atividade] = $grade->contatividade;
                echo "<td>$grade->notaminima</td>";
                echo "<td>$grade->notamaxima</td>";
                echo "<td>$notaobtida</td>";
                echo "<td>$grade->contribuicao%</td>";
                echo "</tr>";
            }
            echo "</tbody></table>";                        
            echo "</div>";
}