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

admin_externalpage_setup('reportengage', '', null, '', array('pagelayout' => 'report'));

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname',  'report_engage_plugin'));


/**
 * Acessando o banco de dados para buscar todos os courses
 * Salva em uma varíavel (array)
 */
$courses = $DB->get_records('course');

/**
 * Instancia-se a tabela
 * configura o tamanho das colunas e os headers
 */
$table = new html_table('engageTable');
$table->size = array('40%', '20%', '20%', '20%');
$table->head = array(
    get_string('col_coursename', 'report_engage_plugin'),
    get_string('col_lastaccess', 'report_engage_plugin'),
    get_string('col_user_enrol', 'report_engage_plugin'),
    get_string('col_log', 'report_engage_plugin')
);

/**
 * Para cada course, busca a data do último acesso/log, quantidade de usuários matriculados
 * E a quantidade de logs produzidos no curso
 * 
 * Salva os dados no vetor da tabela  * 
 * Mantém os dados do shortname do course e os dados de log para plotar no gráfico
 */
$course_labels = array();
$course_logs = array();
foreach ($courses as $course) {

    /**
     * Obtem os dados do último acesso
     */
    $last_access_resp = $DB->get_record_sql('select timecreated from {logstore_standard_log} where courseid = ' . $course->id . ' and userid > 0 order by timecreated desc limit 1', []);
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
     * Obter a quantidade de logs gerados no curso por usuários
     */
    $log_gen_resp = $DB->get_record_sql('select count(*) as log from {logstore_standard_log} where courseid = ' . $course->id . ' and userid > 0', []);
    $log_gen = $log_gen_resp->log;

    /**
     * Obtem a quantidade de usuários matriculados ao curso
     */
    $coursecontext = context_course::instance($course->id);
    $user_enrol = count(get_enrolled_users($coursecontext));

    /**
     * concatena os dados no final da tabela
     * 
     * Se existe usuários matriculados no course, exibe o link para ir para a próxima página,
     * caso contrário, omitir o link
     */

    if ($user_enrol > 0) {
        $table->data[] = array(
            html_writer::link(
                $CFG->wwwroot . '/report/engage_plugin/course.php?id=' . $course->id . '&log_count=' . $log_gen . '&name=' . $course->fullname,
                $course->fullname
            ),
            $last_access,
            $user_enrol,
            $log_gen
        );
    } else {
        $table->data[] = array(
            $course->fullname,
            $last_access,
            $user_enrol,
            $log_gen
        );
    }

    /**
     * Salva os dados calculados/obtidos para plotar no gráfico
     */
    array_push($course_labels, $course->fullname);
    array_push($course_logs, $log_gen);
}

/**
 * Renderiza a tabela na tela
 */
echo html_writer::table($table);

/**
 * Processa o gráfico de logs por course
 */
if (class_exists('core\chart_bar')) {

    /**
     * Criando o gráfico de barras comum
     */
    $bar_chart = new core\chart_bar();
    $bar_chart->set_title(get_string('col_logcourse', 'report_engage_plugin'));

    /**
     * Configura os dados seriais para que as legendas sejam os coursesS
     */
    foreach ($course_logs as $ind => $value) {
        $bar_chart->add_series(
            new core\chart_series(
                $course_labels[$ind],
                [$course_logs[$ind]]
            )
        );
    }

    $bar_chart->set_labels([get_string('col_log', 'report_engage_plugin')]);

    /**
     * Renderiza o bar
     */
    echo $OUTPUT->render_chart($bar_chart, false);
}

echo $OUTPUT->footer();
