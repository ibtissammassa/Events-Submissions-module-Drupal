<?php

/**
 * @file
 * A form to collect details for Event submission
 */

namespace Drupal\submissions\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class EventSubmitForm extends FormBase {
    /**
     * {@inheritDoc}
     */
    function getFormId(){
        return 'event_submission_form';
    }
    /**
     * {@inheritDoc}
     */
    function buildForm(array $form, FormStateInterface $form_state){
        //get the node of the page 
        $node = \Drupal::routeMatch()->getParameter('node');
        //some pages may not be nodes, so the $node will be NULL
        if(!(is_null($node))) {
            $nid = $node -> id();
        }
        else{
            $nid = 0;
        }
        $form['name'] = [
            '#type'=> 'textfield',
            '#title' => t('Full Name'),
            '#size' => 25,
            '#description' => t('Enter your full name'),
            '#required' => TRUE,
        ];
        $form['email'] = [
            '#type' => 'textfield',
            '#title' => t('Email address'),
            '#size' => 25,
            '#description' => t('We will send updates to the email address you provide'),
            '#required' => TRUE,
        ];
        $form['submit'] = [
            '#type' => 'submit',
            '#value' => t('Attend event'),
        ];
        //this field contains the id of the node the form is displayed on
        $form['nid'] = [
            '#type' => 'hidden',
            '#value' => $nid,
        ];
        return $form;
    }
    /**
     * {@inheritDoc}
     */
    function submitForm(array &$form, FormStateInterface $form_state){
        try{
           //current user ID
           $uid = \Drupal::currentUser()->id();
           //values from form
           $nid = $form_state->getValue('nid');
           $name = $form_state->getValue('name');
           $email = $form_state->getValue('email');
           $current_time = \Drupal::time()->getRequestTime();

        //insert to database
            $query = \Drupal::database()->insert('events_submissions_list');
            //values that the query will insert into
            $query->fields([
                'uid',
                'nid',
                'name',
                'mail',
                'created',
            ]);
            //Set values of the fields selected
            $query->values([
                $uid,
                $nid,
                $name,
                $email,
                $current_time,
            ]);
            //execute the query
            $query->execute();

            $node = \Drupal::routeMatch()->getParameter('node');
            if ($node instanceof \Drupal\node\NodeInterface && $node->getType() == 'event') {
                // Get the current value of the field.
                $seats_left = $node->get('field_seats_left')->value;
            
                // Decrease the value by 1.
                $new_seats_left = $seats_left - 1;
            
                // Set the new value to the field.
                $node->set('field_seats_left', $new_seats_left);
            
                // Save the node.
                $node->save();
            }

            \Drupal::messenger()->addMessage(
                t('Thanks for your submission @name, you are on the list for the event!',
                ['@name'=> $name]),
            );

        }catch(\Exception $e){
            \Drupal::messenger()->addError(
                t('Unable to save Event submissions settings due to database error. Please try again.')
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function validateForm(&$form, FormStateInterface $form_state){
        $value = $form_state->getValue('email');
        if(!(\Drupal::service('email.validator')->isValid($value))){
            $form_state->setErrorByName('email',
            $this->t('It appears that %mail is not a valid email.
            Please try again', ['%mail'=>$value]));
        }
    }
}