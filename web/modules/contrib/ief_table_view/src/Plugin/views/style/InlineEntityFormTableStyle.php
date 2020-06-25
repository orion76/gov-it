<?php

namespace Drupal\ief_table_view\Plugin\views\style;

use Drupal\ief_table_view\InlineEntityForm\InlineEntityFormTableProviderInterface;
use Drupal\views\Plugin\views\style\Table;

/**
 * Style plugin to render IEF table using a View.
 *
 * @ViewsStyle(
 *   id = "ief_table",
 *   title = @Translation("Inline Entity Form Table"),
 *   help = @Translation("Generates an Inline Entity Form Table from a view."),
 *   theme = "views_view_table",
 *   display_types = {"ief_table"}
 * )
 */
class InlineEntityFormTableStyle extends Table implements InlineEntityFormTableProviderInterface {

  /**
   * {@inheritdoc}
   */
  protected $usesGrouping = FALSE;

  /**
   * {@inheritdoc}
   */
  public function getInlineEntityFormTableColumns() {
    $weight = 0;
    $build = [];

    // Add each column/field.
    foreach ($this->sanitizeColumns($this->options['columns']) as $field) {
      $fieldPlugin = $this->view->field[$field];

      // Don't display hidden fields.
      if (!empty($fieldPlugin->options['exclude'])) {
        continue;
      }

      $build[$field] = [
        'label' => ['#plain_text' => $fieldPlugin->options['label']],
        'type' => 'callback',
        'callback' => 'ief_table_view_inline_entity_form_entity_table_callback',
        'callback_arguments' => [
          'style' => $this,
          'field' => $field,
        ],
        'weight' => $weight++,
      ];
    }

    return $build;
  }

}
