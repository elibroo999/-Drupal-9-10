<?php

namespace Drupal\sb_projects\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\node\Entity\Node;

/**
 * @Block(
 *   id = "sb_latest_projects",
 *   admin_label = @Translation("Последние проекты (3)")
 * )
 */
class LatestProjectsBlock extends BlockBase {

  public function build() {
    $nids = \Drupal::entityQuery('node')
      ->condition('type', 'project')
      ->condition('status', 1)
      ->sort('created', 'DESC')
      ->range(0, 3)
      ->execute();

    $items = [];
    if (!empty($nids)) {
      $nodes = Node::loadMultiple($nids);
      foreach ($nodes as $node) {
        $items[] = $node->toLink()->toRenderable();
      }
    }

    return [
      '#theme' => 'item_list',
      '#title' => $this->t('Последние проекты'),
      '#items' => $items,
      '#cache' => ['max-age' => 0],
    ];
  }

}
