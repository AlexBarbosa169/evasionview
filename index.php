<?php

require_once '../../../config.php';
require_once($CFG->libdir.'/gradelib.php');
require_once($CFG->dirroot.'/grade/querylib.php');
require_once($CFG->dirroot.'/grade/lib.php');
require_once $CFG->dirroot . '/course/report/lib.php';
require_once $CFG->dirroot . '/course/report/evasionview/lib.php';

// $courseid VARIAVEL PARA ARMAZENAR O ID DO CURSO ACESSADO.
// required_param() 'ESSA FUNÇÃO PEGA PARAMETROS PASSADOS VIA REQUISIÇÃO'.
// 'id' NOME DO PARAMETRO PASSADO VIA REQUISIÇÃO
// PARAM_INT TIPO DO PARAMETRO PASSADO NA REQUISIÇÃO

$courseid = required_param('id', PARAM_INT);

// VERIFICAÇÃO DO CURSO ATRAVÉS DO PARAMETRO PASSADO VIA URL
if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('nocourseid');
}

// $context 'INSTANCIA DO CONTEXTO, COM BASE NO CURSO ATUAL.
$context = context_course::instance($course->id);

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

// INSERÇÃO ...
echo $OUTPUT->header();

//
echo "<div id='plugintitle'><h1>". get_string('hello','coursereport_evasionview')."</div>";

//
echo "<button name='evento' value='teste' onclick = clicou(this)>Teste Javascript</button>";

// TESTE DE FUNÇÃO PHP
funcao_teste();

// INSERÇÃO DO RODAPÉ DA PÁGINA DO MOODLE
echo $OUTPUT->footer();
