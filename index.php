<?php

require_once '../../../config.php';
require_once($CFG->libdir.'/gradelib.php');
require_once($CFG->libdir.'/coursecatlib.php');
require_once($CFG->dirroot.'/grade/querylib.php');
require_once($CFG->dirroot.'/grade/lib.php');
require_once $CFG->dirroot . '/course/report/lib.php';
require_once $CFG->dirroot . '/course/report/evasionview/lib.php';

// $courseid VARIAVEL PARA ARMAZENAR O ID DO CURSO ACESSADO.
// required_param() 'ESSA FUNÇÃO PEGA PARAMETROS PASSADOS VIA REQUISIÇÃO'.
// 'id' NOME DO PARAMETRO PASSADO VIA REQUISIÇÃO
// PARAM_INT TIPO DO PARAMETRO PASSADO NA REQUISIÇÃO

$courseid = required_param('id', PARAM_INT);
$grouprequest = optional_param('group', null ,PARAM_TEXT);
$userinfo = optional_param('userinfo',null ,PARAM_TEXT);
$usersend = optional_param('usersend',null ,PARAM_TEXT);

$params = array('group'=>$grouprequest,'userinfo'=>$userinfo,'usersend'=>$usersend);


// VERIFICAÇÃO DO CURSO ATRAVÉS DO PARAMETRO PASSADO VIA URL
if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('nocourseid');
}

// $context 'INSTANCIA DO CONTEXTO, COM BASE NO CURSO ATUAL.
$context = context_course::instance($course->id);
//var_dump($context);

require_capability('coursereport/evasionview:view', $context);

// SETANDO O CONTEXTO DA PÁGINA COM O CONTEXTO OBTIDO VIA INSTÂNCIA COM BASE NO CURSO
$PAGE->set_context($context);

// SETANDO A URL DA PÁGINA COM A URL DO PLUGIN
$PAGE->set_url('/course/report/evasionview/index.php?id=' . $courseid);

// INSERÇÃO DE CÓDIGO CSS NA PÁGINA DO CURSO
$PAGE->requires->css('/course/report/evasionview/css/styleevasionview.css', true);

// INSERÇÃO DE CÓDIGO JAVASCRIPT NA PÁGINA DO CURSO
$PAGE->requires->js('/course/report/evasionview/js/evasionviewscript.js', true);

// SETANDO O CAMINHO NA BARRA DE NAVEGAÇÃO COM O ENDEREÇO PARA O PLUGIN
$PAGE->navbar->add(get_string('pluginname', 'coursereport_evasionview'), new moodle_url("$CFG->httpswwwroot/course/report/evasionview/index.php?id=" . $courseid));

// SETANDO O TÍTULO DA PÁGINA COM O NOME DO PLUGIN
$PAGE->set_title(get_string('pluginname', 'coursereport_evasionview'));

// SETANDO O TIPO DO LAYOUT DA PÁGINA DE ACORDO COM O PLUGIN TIPO REPORT
$PAGE->set_pagelayout('report');

// SETANDO O CABEÇALHO DA PÁGINA COM O NOME DO PLUGIN
$PAGE->set_heading(get_string('pluginname', 'coursereport_evasionview'));

$page = $PAGE->url;
// INSERÇÃO ...
echo $OUTPUT->header();

print_navigation_evasionview($params,$page ,$OUTPUT);

//
echo "<div id='plugintitle'><h1>". get_string('hello','coursereport_evasionview')."</div>";

//
echo "<button name='evento' value='teste' onclick = clicou(this)>Teste Javascript</button>";

//Teste de impressão
//echo $OUTPUT->login_info();
//echo $OUTPUT->home_link();
//echo $OUTPUT->user_menu($user);
//$user = $USER;
//$options = array('size'=>300);
//echo $OUTPUT->user_picture($user,$options);

// Renderizando um link com a biblioteca output do moodle
//echo $OUTPUT->action_link(new moodle_url($PAGE->url, array('group'=>'Vai','userInfo'=>'Vai')), "Vai");
// Renderizando um link com a biblioteca output do moodle
//echo $OUTPUT->action_link(new moodle_url($PAGE->url, array('teste'=>'Vem')), "Vem");

//grade_get_course_grade
//var_dump($USER);

//$users_in_course = get_users($courseid);
////var_dump($users_in_course);
//foreach ($users_in_course as $user) {
////    var_dump($user->id);
//    echo "Nome do usuário: ".$user->firstname;
//    $grade_user = grade_get_course_grade($user->id);
////    var_dump($grade_user[2]->item->grademax);
//    echo "Nota Geral do Curso: ".$grade_user[2]->item->grademax;
////    var_dump($grade_user[2]->grade);
//    echo "Nota obtida pelo aluno: ".$grade_user[2]->grade;
////    echo $grade_user[0];
//// Renderizando um link com a biblioteca output do moodle    
//    echo $OUTPUT->action_link(new moodle_url($PAGE->url, array('group'=>3,'userinfo'=>$user->id)), "clique aqui");
//    echo '<br>';
//
//}


//var_dump(get_users());

// TESTE DE FUNÇÃO PHP
//funcao_teste();


// INSERÇÃO DO RODAPÉ DA PÁGINA DO MOODLE

echo $OUTPUT->footer();
