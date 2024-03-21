<?php

/**
 * @file
 * Contains the submissions Enabler service.
 */

namespace Drupal\submissions;

use Drupal\Core\Database\Connection;
use Drupal\node\Entity\Node;

class EnablerService {
    protected $database_connection;

    public function __construct(Connection $connection) {
        $this->database_connection = $connection;
    }

    /**
     * Checks if an individual node is enabled for submissions
     * @param  Node $node
     * @return bool
     * whether or not the node is enabled for the submissions functionality
     */
    public function isEnabled(Node &$node){
        if($node->isNew()){
            return False;
        }
        // if($node->getType()!='event'){
        //     return False;
        // }
        try{
            $select_enabled = $this->database_connection->select('event_submissions_enabled',"se");
            $select_enabled->fields("se", ["nid"]);
            $select_enabled->condition("nid", $node->id());
            $result = $select_enabled->execute();           

            return !(empty($result->fetchCol()));
        }
        catch(\Exception $e){
            \Drupal::messenger()->addError(
                t('Unable to determine Event submissions settings at this time. please try again later.')
            );
            return NULL;
        }
    }

    /**
     * Sets an individual node to be submissions enabled
     * @param Node $node
     * @throws Exception
     */
    public function setEnabled(Node $node){
        try {
            if(!($this->isEnabled($node))){
                $insert = $this->database_connection->insert('event_submissions_enabled');
                $insert->fields(['nid']);
                $insert->values([$node->id()]);
                $insert->execute();
            }
        }
        catch(\Exception $e){
            \Drupal::messenger()->addError(
                t('Unable to save Event submission settings at this time. Please try again.')
            );
        }
    }
    /**
     * Delete submissions enabled settings for an individual node
     * @param Node $node
     */
    public function delEnabled(Node $node){
        try {
            $delete = $this->database_connection->delete('event_submissions_enabled');
            $delete->condition('nid', $node->id());
            $delete->execute();
        }
        catch(\Exception $e){
            \Drupal::messenger()->addError(
                t('Unable to save Submission settings at this time. Please try again.')
            );
        }
    }
}