<?php

namespace Drupal\ief_table_view\Plugin\views\display;

use Drupal\ief_table_view\InlineEntityForm\InlineEntityFormTableProviderInterface;
use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\ResultRow;

/**
 * Views display plugin for IEF table.
 *
 * @ViewsDisplay(
 *   id = "ief_table",
 *   title = @Translation("Inline Entity Form Table"),
 *   admin = @Translation("Inline Entity Form Table Source"),
 *   help = @Translation("Exposed the view for use as a table within inline entity form widgets."),
 *   theme = "views_view",
 *   register_theme = FALSE,
 *   uses_menu_links = FALSE,
 *   ief_table_display = TRUE
 * )
 */
class InlineEntityFormTableDisplay extends DisplayPluginBase implements InlineEntityFormTableProviderInterface {

  /**
   * {@inheritdoc}
   */
  protected $usesAJAX = FALSE;

  /**
   * {@inheritdoc}
   */
  protected $usesPager = FALSE;

  /**
   * {@inheritdoc}
   */
  protected $usesAttachments = FALSE;

  /**
   * {@inheritdoc}
   */
  public function usesExposed() {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getType() {
    return 'ief_table';
  }

  /**
   * {@inheritdoc}
   */
  public function getInlineEntityFormTableColumns() {
    // Pass this off to the style plugin if possible.
    $style = $this->getPlugin('style');
    if (!$style instanceof InlineEntityFormTableProviderInterface) {
      return [];
    }
    return $style->getInlineEntityFormTableColumns();
  }

  /**
   * Set missing entities into the view result.
   *
   * This occurs because entities may be referenced by the field that have not
   * yet been saved, which means they're not able to be queried by the view.
   * Adding them to the view result is not perfect, but rather a best-effort
   * attempt to make them appear along with saved nodes.
   *
   * @param array $fieldEntities
   *   The entities specified by the field that need to appear in the result.
   */
  public function setResultEntities(array $fieldEntities) {
    // Find existing entities and the highest index in the view result.
    $existing = [];
    $index = -1;
    foreach ($this->view->result as $row) {
      $existing[] = $row->_entity->uuid();
      $index = max($index, $row->index);
    }

    // Make sure the view has up to date results.
    foreach ($fieldEntities as $entity) {
      if (!in_array($entity->uuid(), $existing)) {
        // For entities not included in the result of the view, add a result
        // row. This is likely newly-added entities.
        $existing[] = $entity->uuid();
        $row = new ResultRow([
          '_entity' => $entity,
          '_relationship_entities' => [],
          'index' => ++$index,
        ]);
        $this->view->result[] = $row;
      }
      else {
        // For existing entities, plug in what's provided by the field which has
        // possibly been edited.
        foreach ($this->view->result as $row) {
          if ($entity->uuid() == $row->_entity->uuid()) {
            $row->_entity = $entity;
          }
        }
      }
    }
  }

}
