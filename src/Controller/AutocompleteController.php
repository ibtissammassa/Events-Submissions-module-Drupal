<?php

/**
 * @file
 * Handeles the autocomplete of the event filtering form
 */

namespace Drupal\events_submissions\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class AutocompleteController extends ControllerBase {
    /**
     * handle autocomplete
     *
     * @return object
     */
    public function handleAutocomplete(Request $request) {
        $matches = [];
        $searchParameter = $request->query->get("q");
        if(strlen($searchParameter) >= 2) {
            $matches = $this->getEventList($searchParameter);
            return new JsonResponse($matches);
        }
        return new JsonResponse("");
    }

    /**
     *
     *
     * @param [string] $searchParameter
     * @return array
     */
    private function getEventList($searchParameter) {
        $matches = [];
        try{
          $query = \Drupal::entityQuery('node')
          ->condition('status', 1)
          ->condition('type', 'event')
          ->condition('title', '%'. $searchParameter .'%','LIKE')
          ->range(0,10)
          ->accessCheck(TRUE);
          $nids = $query->execute();

          if(!empty($nids)) {
            $nodes = \Drupal::entityTypeManager()->getStorage('node')->loadMultiple($nids);
            foreach($nodes as $node) {
                $matches[] = [
                    'value' => $node->getTitle(),
                    'label'=> $node->getTitle(),
                ];
            }
          }
          else{
            $matches[] = [
                'value' => '',
                'label' => $this->t('No event found'),
            ];
          }
          return $matches;
        }
        catch(\Exception $e) {
            \Drupal::messenger()->addError(
                t('Error at autocomplete : @error.', ['@error' => $e->getMessage()])
            );
            return [];
        }
    }
}
