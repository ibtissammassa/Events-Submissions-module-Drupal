<?php

/**
 * @file
 * Creates a block that displays the EventSubmitForm contained in EventSubmitForm.php
 */

 namespace Drupal\submissions\Plugin\Block;

 use Drupal\Core\Block\BlockBase;
 use Drupal\Core\Block\Attribute\Block;
 use Drupal\Core\StringTranslation\TranslatableMarkup;
 use Drupal\Core\Session\AccountInterface;
 use Drupal\Core\Access\AccessResult;

 /**
 * Provides the Event Submit main block.
 */
#[Block(
    id: "EventSubmission_block",
    admin_label: new TranslatableMarkup("The Event submissions Block")
  )]

 class EventSubmissionsBlock extends BlockBase {
    /**
     * {@inheritDoc}
     */
    public function build(){
        return \Drupal::formBuilder()->getForm('\Drupal\submissions\Form\EventSubmitForm');
    }

    /**
     * {@inheritDoc}
     */
    public function blockAccess(AccountInterface $account){
        // If viewing a node
        $node = \Drupal::routeMatch()->getParameter('node');

        if (!(is_null($node)) AND $node->getType()=='event') {
            $enabler = \Drupal::service('submissions.enabler');
            if($enabler->isEnabled($node)){
                $seats_left = $node->get('field_seats_left')->value;
                $query = \Drupal::database()->select('events_submissions_list','es');
                $query->fields('es',["nid"]);
                $query->condition("nid", $node->id());
                $result = $query->execute()->fetchAll();
                $count = count($result);
                if ($count >= 0 AND $count <= $seats_left) {
                    return AccessResult::allowedIfHasPermission($account,'view event submit');
                }
            }
        }
        return AccessResult::forbidden();                       
    }
 }