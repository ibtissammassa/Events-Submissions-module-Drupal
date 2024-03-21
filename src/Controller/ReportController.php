<?php

/**
 * @file
 * Provide site administrators with a list of all the Event List signups 
 * so they know who is attending their events.
 */

 namespace Drupal\submissions\Controller;

 use Drupal\Core\Controller\ControllerBase;
 use Drupal\Core\Database\Database;

 class ReportController extends ControllerBase {
    /**
     * Gets and returns all submissions for all events
     * These are returned as an associative array, with 
     * 
     * @return array|null
     */
    protected function load() {
        try{
            $database = \Drupal::database();
            $select_query = $database->select('events_submissions_list','es');

            //Join the user table, so we can get the entry creator's username
            $select_query->join('users_field_data','u','es.uid = u.uid');
            // Join the node table, so we can get the event's name
            $select_query->join('node_field_data','n','es.nid = n.nid');

            // select these specific fields for the output
            $select_query->addField('u','name','username');
            $select_query->addField('n','nid');
            $select_query->addField('n','title');
            $select_query->addField('es','name');
            $select_query->addField('es','mail');

            $entries = $select_query->execute()->fetchAll(\PDO::FETCH_ASSOC);

            return $entries;
        } catch(\Exception $e) {
            \Drupal::messenger()->addStatus(
                t('Unable to access the database. Please try again.')
            );
            return null;
        }
    }

    /**
     * Creates the attendees List report page
     * @return array
     * Render array for the report output.
     */
    public function report() {
        $content = [];

        $content['message'] = [
            '#markup'=> t('Bellow is a list of all Events submissions inclusing username, full name, email address and the name of the event they will be attending.'),
        ];

        $headers = [
            t('Username'),
            t('Event ID'),
            t('Event'),
            t('full name'),
            t('Email'),
        ];

        $table_rows = $this->load();

        //Create the render array for rendering an html table.
        $content['table'] = [
            '#type'=> 'table',
            '#header'=> $headers,
            '#rows'=> $table_rows,
            '#empty'=>t('No entries available.')
        ];

        // Do not cache this page (always refresh this render array when it is time to display it)
        $content['#cache']['max-age'] = 0;

        return $content;
    }
 }