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