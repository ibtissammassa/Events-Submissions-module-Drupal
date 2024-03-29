<?php

/**
 * @file
 * A form to collect details for Event submission
 */

namespace Drupal\events_submissions\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class ReportFilterForm extends FormBase {
    /**
     * {@inheritDoc}
     */
    function getFormId(){
        return 'report_filter_form';
    }
    /**
     * {@inheritDoc}
     */
    function buildForm(array $form, FormStateInterface $form_state){
        $form["event"] = [
            "#type" => "textfield",
            "#title" => $this->t("Event"),
            "#autocomplete_route_name" => 'submissions.autocomplete',
        ];
        $form["submit"] = [
            "#type"=> "submit",
            "#value" => $this->t("Filter"),
        ];
        return $form;
    }
    /**
     * {@inheritDoc}
     */
    function submitForm(array &$form, FormStateInterface $form_state){
    }

    /**
     * {@inheritdoc}
     */
    public function validateForm(&$form, FormStateInterface $form_state){

    }
}