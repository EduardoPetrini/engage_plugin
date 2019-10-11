<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
/**
 * Report main page
 *
 * @package    report
 * @copyright  2019 Eduardo Petrini
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once(__DIR__ . '/../../grade/querylib.php'); //Para buscar as notas finais no course

admin_externalpage_setup('reportengage', '', null, '', array('pagelayout' => 'report'));

echo $OUTPUT->header();

/**
 * Recupera os parâmetros recebidos na requisição usando 'optional_param'
 */
$courseid = optional_param('id', 0, PARAM_INT);
$log_count = optional_param('log_count', 0, PARAM_INT);
$course_name = optional_param('name', 'N/A', PARAM_TEXT);

echo $OUTPUT->heading(get_string('pluginname',  'report_engage_plugin') . ' - ' . $course_name);

/**
 * Obtem o contexto do course para buscar os usuários matriculados no course
 */
$coursecontext = context_course::instance($courseid);
$user_enrol_list = get_enrolled_users($coursecontext);
$user_size = count($user_enrol_list);

/**
 * Instancia-se a tabela
 * configura o tamanho das colunas e os headers
 */
$table = new html_table();
$table->size = array('40%', '20%', '20%', '20%');
$table->head = array(
    get_string('col_username', 'report_engage_plugin'),
    get_string('col_lastaccess', 'report_engage_plugin'),
    get_string('col_grade', 'report_engage_plugin'),
    get_string('col_engage', 'report_engage_plugin')
);

/**
 * Vetores para salvar os dados calculados/obtidos para plotas nos gráficos
 */
$engage_data = array();
$labels_data = array();
foreach ($user_enrol_list as $user_enrol) {

    /**
     * Obter os dados do usuários
     */
    $user = $DB->get_record('user', array('id' => $user_enrol->id));

    /**
     * Obter o último log do usuário no curso
     */
    $last_access_resp = $DB->get_record_sql('select timecreated from {logstore_standard_log} where courseid = ' . $courseid . ' and userid = ' . $user_enrol->id . ' order by timecreated desc limit 1', []);
    $last_access = $last_access_resp->timecreated;
    if ($last_access > 0) {
        /**
         * Formata o unixtime para data amigável
         */
        $last_access = userdate($last_access);
    } else {
        $last_access = '-';
    }

    /**
     * Obter a quantidade de logs gerados no curso
     */
    $log_gen_resp = $DB->get_record_sql('select count(*) as log from {logstore_standard_log} where courseid = ' . $courseid . ' and userid = ' . $user_enrol->id, []);
    $log_gen = $log_gen_resp->log;
    $engage = round(($log_gen / $log_count) * 100);

    /**
     * Busca os dados de notas do course
     */
    $grading_info = grade_get_course_grades($courseid, $user_enrol->id);
    $grade = $grading_info->grades[$user_enrol->id]->str_grade;

    /**
     * Concatena os dados na tabela, implemenata a funcionalidade de enviar mensagem para o usuário ao clicar nele
     */
    $table->data[] = array(
        html_writer::link(
            $CFG->wwwroot . '/message/index.php?id=' . $user->id,
            $user->firstname . " " . $user->lastname,
            array('title' => get_string('text_send_message', 'report_engage_plugin'))
        ),
        $last_access,
        $grade,
        $engage . '%'
    );
    array_push($engage_data, $engage);
    array_push($labels_data, $user->firstname);
}

/**
 * Renderiza a tabela na tela
 */
echo html_writer::table($table);
echo '<hr>';

/**
 * Cria o gráfico de pizza de engajamento dos usuários
 */
if (class_exists('\core\chart_pie')) {
    $chart = new \core\chart_pie();
    $chart->set_title(get_string('col_engage', 'report_engage_plugin'));
    $series = new core\chart_series(
        get_string('col_engage', 'report_engage_plugin'),
        $engage_data
    );
    $chart->add_series($series); // On pie charts we just need to set one series.
    $chart->set_labels($labels_data);
    echo $OUTPUT->render_chart($chart, false);
    echo '<hr>';
}

if (class_exists('core\chart_bar')) {
    /**
     * Criando o gráfico de barras comum
     */
    $barChart = new core\chart_bar();

    foreach ($engage_data as $ind => $eng) {
        $barChart->add_series(new core\chart_series(
            $labels_data[$ind],
            [$engage_data[$ind]]
        ));
    }
    $barChart->set_labels([get_string('col_engage', 'report_engage_plugin')]);

    /**
     * Renderiza o bar
     */
    echo $OUTPUT->render_chart($barChart, false);
}

/**
 * Link para voltar a tela anterior
 */
echo html_writer::link(
    $CFG->wwwroot . '/report/engage_plugin/index.php',
    get_string('link_back', 'report_engage_plugin')
);

echo $OUTPUT->footer();
