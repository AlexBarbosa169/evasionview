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
function print_navigation_evasionview($params,$url ,$OUTPUT) {
//        Incluir aqui o que se espera quando o usuário requer a visualização do grupo de risco dos usuários
        if($params['group']){            
            print_icon_evasionview('group');
            echo $OUTPUT->action_link(new moodle_url($url, array('userinfo'=>3)), "Navegar para usuário");
        }else{
//        Incluir aqui o que se espera quando o usuário requer a visualização das informações sobre um usuário em específico
            if($params['userinfo']){
            print_icon_evasionview('userinfo');  
            echo $OUTPUT->action_link(new moodle_url($url, array('usersend'=>3)), "Navegar para enviar mensagem");            
            }else{
//        Incluir aqui o que se espera quando o usuário requer notificar um usuário selecionado no grupo de risco
                if($params['usersend']){
                print_icon_evasionview('usersend');                
                echo $OUTPUT->action_link(new moodle_url($url),"Navegar para Home");            
                }else{
//        Incluir aqui o que se espera quando o usuário entra no link principal do plugin
                    print_icon_evasionview('home');                    
                    echo $OUTPUT->action_link(new moodle_url($url,array('group'=>3)),"Navegar para Grupo de Usuários");
                    $groups = get_group_grades_evasionview($params['id']);
//                    var_dump($groups);
                    $good_group = count($groups['good']);
                    $far_group = count($groups['far']);
                    $poor_group = count($groups['far']);
                    $null_group = count($groups['null']);
                    grafchartjs($good_group, $far_group, $poor_group, $null_group);
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
    $far_group = array();
    $poor_group = array();
    $null_group = array();
       
    if($usersCourse){
        echo "<br>Tem Alunos<br>";
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
                        $far_group[] = $user;
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
//        echo "Grupo com notas Médias". count($far_group);
//        echo "Grupo com notas Ruins". count($poor_group);
//        echo "Grupo sem notas notas". count($null_group);
        $groups = array('good'=>$good_group,'far'=>$far_group,'poor'=>$poor_group, 'null'=>$null_group);
    }else{
        echo "Não Tem Alunos";
    }
    return $groups;
}

function grafchartjs($good,$far,$poor,$null){
    echo "<canvas id='myChartPie' width='400' height='400'></canvas>";
    
    echo "<script src='js/Chart.min.js'></script>";
    
    echo "<script>
        var ctx = document.getElementById('myChartPie').getContext('2d');
        var myChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['Good', 'Far', 'Poor', 'Null'],
                datasets: [{
                    label: 'Percentual de contribuição',
                    data: [$good, $far, $poor, $null ],
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

function search_user($params){
    $groups = get_group_grades_evasionview($params['id']);
    $selectedGroup = $groups['good'];
    var_dump($selectedGroup);
    $valor_filtrado = 'Use';
    $users_filtrado = array_filter($selectedGroup, function($user) use($valor_filtrado){
                    if(strpos($user->firstname, $valor_filtrado) === 0){
                        return $user;
                    }
                    //return $user->firstname === 'User2';
                });
                echo 'Filtrei aqui embaixo';
                var_dump($users_filtrado);
                return $users_filtrado;
   // var_dump($groups);
    
}




