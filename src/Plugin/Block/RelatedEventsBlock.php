<?php

/**
 * @file
 * Creates a block that displays the related events
 */

 namespace Drupal\events_submissions\Plugin\Block;

 use Drupal\Core\Block\BlockBase;
 use Drupal\Core\Block\Attribute\Block;
 use Drupal\Core\StringTranslation\TranslatableMarkup;
 use Drupal\Core\Session\AccountInterface;
 use Drupal\Core\Access\AccessResult;

 /**
 * Provides the related events block.
 */
#[Block(
    id: "related_events_block",
    admin_label: new TranslatableMarkup("Related events Block")
  )]

  class RelatedEventsBlock extends BlockBase {

    /**
     * {@inheritdoc}
     */
    public function build() {
        $node = \Drupal::routeMatch()->getParameter('node');
        $taxonomy_field_name = 'field_event_category';
  
        // Get the term ID associated with the current node
        $term_id = $node->get($taxonomy_field_name)->target_id;
  
      // Render
      $build = [];
        $build = [
            '#theme' => 'related',
            '#termid' => $term_id,
        ];
        return $build;	
    }

    /**
     * {@inheritDoc}
     */
    public function blockAccess(AccountInterface $account){
        // If viewing a node
        $node = \Drupal::routeMatch()->getParameter('node');

        if (!(is_null($node)) AND $node->getType()=='event') {
            return AccessResult::allowedIfHasPermission($account,'view related events');
        }
        return AccessResult::forbidden();                       
    }
  
  }