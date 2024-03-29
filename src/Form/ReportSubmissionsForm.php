<?php

/**
 * @file
 * A form to display details for Event submission and download Submissions them as csv
 */

namespace Drupal\events_submissions\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\Response;

class ReportSubmissionsForm extends FormBase {

    public $flag = '';

    /**
     * {@inheritDoc}
     */
    function getFormId(){
        return 'report_event_submission_form';
    }
    /**
     * {@inheritDoc}
     */
    function buildForm(array $form, FormStateInterface $form_state){
        $form = [];

        $form['filter'] = [
            "#type" => "container",
            '#attributes' => [
                'id' => ['notices'],
                'class' => ['flex'],
            ]
        ];
        $form["filter"]["event"] = [
            "#type" => "textfield",
            "#title" => $this->t("Event"),
            "#autocomplete_route_name" => 'submissions.autocomplete',
        ];
        $form["filter"]["submit_1"] = [
            "#type"=> "submit",
            "#value" => $this->t("Filter"),
        ];

        $form['message'] = [
            '#markup'=> t('Bellow is a list of all Events submissions inclusing username, full name, email address and the name of the event they will be attending.'),
        ];

        $headers = [
            $this->t('Username'),
            $this->t('Event ID'),
            $this->t('Event'),
            $this->t('full name'),
            $this->t('Email'),
        ];

        $table_rows = $this->loadSubmissions($this->flag);

        //Create the render array for rendering an html table.
        $form['table'] = [
            '#type'=> 'table',
            '#header'=> $headers,
            '#rows'=> $table_rows,
            '#empty'=>$this->t('No entrie found for the specified event.')
        ];

       //for pagination
        $form['pager'] = [
          '#type' => 'pager',
        ];

        $form['submit_2'] = [
            '#type' => 'submit',
            '#value' => $this->t('Export CSV'),
            '#submit' => ['::exportCsv'], //custom submit handler
        ];

        // Do not cache this page (always refresh this render array when it is time to display it)
        $form['#cache']['max-age'] = 0;
        return $form;
    }

    /**
     * {@inheritDoc}
     */
    function submitForm(array &$form, FormStateInterface $form_state){
        $this->flag = $form_state->getValue('event');
        dpm($form_state);
        $form_state->setRebuild(TRUE);
    }

    /**
     * Custom submit handler for exporting CSV.
     */
    public function exportCsv(array &$form, FormStateInterface $form_state) {
        $table_rows = $form['table']['#rows'];
        $csv_content = '';

        $headers = [
            'Username',
            'Event ID',
            'Event',
            'Full name',
            'Email',
        ];

        $csv_content .= '"' . implode('","', $headers) . '"' . PHP_EOL;
        // Add data rows to CSV
        foreach ($table_rows as $row) {
            $csv_content .= '"' . implode('","', $row) . '"' . PHP_EOL;
        }

        //Send CSV file as response
        $response = new Response($csv_content);
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="events_submissions_report.csv"');
        $response->send();
        \Drupal::messenger()->addMessage(
            t('CSV exported successfully')
        );
        return;
    }

    /**
     * Gets and returns all submissions for all events
     * These are returned as an associative array, with
     *
     * @return array|null
     */
    protected function loadSubmissions($eventName='') {
        try{
            $database = \Drupal::database();
            $select_query = $database->select('events_submissions_list','es');

            //Join the user table, so we can get the entry creator's username
            $select_query->join('users_field_data','u','es.uid = u.uid');
            // Join the node table, so we can get the event's name
            $select_query->join('node_field_data','n','es.nid = n.nid');
            if(!empty($eventName)){
                $select_query->condition('n.title', $eventName);
            }
            // select these specific fields for the output
            $select_query->addField('u','name','username');
            $select_query->addField('n','nid');
            $select_query->addField('n','title');
            $select_query->addField('es','name');
            $select_query->addField('es','mail');

            // Add pager to the query
            $pager = $select_query->extend('Drupal\Core\Database\Query\PagerSelectExtender')->limit(5);
            $entries = $pager->execute()->fetchAll(\PDO::FETCH_ASSOC);

            return $entries;
        }
        catch(\Exception $e) {
            \Drupal::messenger()->addStatus(
                $e->getMessage()
            );
            return null;
        }
    }
}