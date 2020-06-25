<?php

namespace Drupal\ief_table_view\Render;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\views\Plugin\views\ViewsPluginInterface;

/**
 * Class EntityTableRenderer.
 */
class EntityTableRenderer {

  /**
   * Renders a column from a result row.
   *
   * This is a rough copy of Views table rendering to allow fields to render
   * with other columns with a separator.
   *
   * @param \Drupal\views\Plugin\views\ViewsPluginInterface $style
   *   The style plugin.
   * @param int $index
   *   The result row index.
   * @param string $column
   *   The field column being rendered.
   *
   * @return array
   *   Renderable array of the field rendering in the specified column.
   *
   * @see template_preprocess_views_view_table
   */
  public function renderColumn(ViewsPluginInterface $style, $index, $column) {
    /** @var \Drupal\views\Plugin\views\style\StylePluginBase $style */
    $build = [];
    $separator = $style->options['info'][$column]['separator'] ?? '';

    // Get the columns configured in the style.
    $columns = $style->options['columns'];

    // Add this column if not defined in the style options.
    if (empty($columns[$column])) {
      $columns[$column] = $column;
    }

    // Add fields if they are configured to render with this column.
    foreach ($columns as $field => $fieldColumnInfo) {
      if ($fieldColumnInfo == $column) {
        $field_output = $style->getField($index, $field);
        if (trim($field_output) != '') {
          if (!empty($build)) {
            $build[] = ['#markup' => $separator];
          }
          $build[] = ['#markup' => $field_output];
        }
      }
    }

    return $build;
  }

  /**
   * Renders a field from a result row.
   *
   * @param \Drupal\views\Plugin\views\ViewsPluginInterface $style
   *   The style plugin.
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity to render.
   * @param string $column
   *   The field column being rendered.
   *
   * @return array
   *   Renderable array of the field rendering in the specified column.
   *
   * @see template_preprocess_views_view_table
   */
  public function renderEntityColumn(ViewsPluginInterface $style, ContentEntityInterface $entity, $column) {
    foreach ($style->view->result as $row) {
      /** @var \Drupal\views\ResultRow $row */
      if ($row->_entity->uuid() == $entity->uuid()) {
        return $this->renderColumn($style, $row->index, $column);
      }
    }
    return [];
  }

}
