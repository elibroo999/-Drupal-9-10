<?php

namespace Drupal\sb_projects\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;

class ProjectsApiController extends ControllerBase {

  public function list(): JsonResponse {
    $nids = \Drupal::entityQuery('node')
      ->accessCheck(TRUE)
      ->condition('status', 1)
      ->condition('type', 'project')
      ->sort('created', 'DESC')
      ->range(0, 3)
      ->execute();

    $result = [];

    if ($nids) {
      $nodes            = \Drupal::entityTypeManager()->getStorage('node')->loadMultiple($nids);
      $fileUrlGenerator = \Drupal::service('file_url_generator');
      $mediaTypeStorage = \Drupal::entityTypeManager()->getStorage('media_type');

      $nodeImageField = 'field_media_image';

      foreach ($nodes as $node) {
        $description = ($node->hasField('field_description') && !$node->get('field_description')->isEmpty())
          ? (string) $node->get('field_description')->value
          : '';

        $endDate = ($node->hasField('field_end_date') && !$node->get('field_end_date')->isEmpty())
          ? (string) $node->get('field_end_date')->value
          : '';

        $imageUrl = '';
        if ($node->hasField($nodeImageField) && !$node->get($nodeImageField)->isEmpty()) {
          $target = $node->get($nodeImageField)->entity;
          if ($target) {
            if ($target->getEntityTypeId() === 'media') {
              $sourceField = null;
              $mediaType = $mediaTypeStorage->load($target->bundle());
              if ($mediaType && method_exists($target, 'getSource')) {
                $def = $target->getSource()->getSourceFieldDefinition($mediaType);
                if ($def) {
                  $sourceField = $def->getName();
                }
              }
              if (!$sourceField) {
                $sourceField = 'field_media_image';
              }
              if ($target->hasField($sourceField) && !$target->get($sourceField)->isEmpty()) {
                $file = $target->get($sourceField)->entity;
                if ($file) {
                  $imageUrl = $fileUrlGenerator->generateAbsoluteString($file->getFileUri());
                }
              }
            } elseif ($target->getEntityTypeId() === 'file') {
              $imageUrl = $fileUrlGenerator->generateAbsoluteString($target->getFileUri());
            }
          }
        }

        $result[] = [
          'title'       => $node->label(),
          'description' => $description,
          'image'       => $imageUrl,
          'end_date'    => $endDate,
        ];
      }
    }

    return new JsonResponse($result);
  }

}
