<?php

/**
 * @file
 * Provide site administrators with a list of all the Event List signups 
 * so they know who is attending their events.
 */

 namespace Drupal\events_submissions\Controller;

 use Drupal\Core\Controller\ControllerBase;
 use Drupal\Core\Database\Database;

 class ReportController extends ControllerBase {

    /**
     * Creates the attendees List report page
     * @return array
     * Render array for the report output.
     */
    public function report() {
        return \Drupal::formBuilder()->getForm('\Drupal\events_submissions\Form\ReportSubmissionsForm');
    }
 }