<?php

namespace Drupal\ief_table_view\InlineEntityForm;

use Drupal\ief_table_view\Plugin\views\display\InlineEntityFormTableDisplay;
use Drupal\views\Entity\View;
use Drupal\views\Plugin\views\ViewsPluginInterface;

/**
 * Trait InlineEntityFormViewsTableWidgetTrait.
 */
trait InlineEntityFormViewsTableWidgetTrait {

  /**
   * Gets the View display.
   *
   * @param string $viewDisplay
   *   A string with the view ID and display ID, separated by a colon.
   *
   * @return \Drupal\views\Plugin\views\ViewsPluginInterface|false
   *   The display, or FALSE if able to load.
   */
  public function getViewDisplay($viewDisplay) {
    $viewDisplay = explode(':', $viewDisplay);
    if (count($viewDisplay) != 2) {
      return FALSE;
    }
    list($viewId, $displayId) = $viewDisplay;

    if (!$config = View::load($viewId)) {
      return FALSE;
    }
    /** @var \Drupal\views\Entity\View $config */
    $view = $config->getExecutable();
    $view->build();

    if ($view->displayHandlers->has($displayId)) {
      return $view->displayHandlers->get($displayId);
    }

    return FALSE;
  }

  /**
   * Gets a list of options for selecting the View and display.
   *
   * @param string $entityType
   *   The entity type of the View.
   *
   * @return array
   *   The options.
   */
  protected function getViewsDisplayOptions($entityType) {
    $views = View::loadMultiple();

    $options = [];
    foreach ($views as $config) {
      /** @var \Drupal\views\Entity\View $config */
      $view = $config->getExecutable();

      // Need to ensure the View is for the specified entity type.
      if (!$id = $view->getBaseEntityType()) {
        continue;
      }
      if ($view->getBaseEntityType()->id() != $entityType) {
        continue;
      }

      // Iterate over the displays, set displays defined with ief_table_display
      // as options.
      $view->build();
      foreach ($view->displayHandlers as $displayId => $display) {
        /** @var \Drupal\views\Plugin\views\ViewsPluginInterface $display */
        $definition = $display->getPluginDefinition();
        if (!empty($definition['ief_table_display'])) {
          $id = $view->id() . ':' . $displayId;
          $label = [
            $display->view->storage->label(),
            $display->pluginTitle(),
          ];
          $options[$id] = implode(' - ', $label);
        }
      }
    }

    asort($options);

    return $options;
  }

  /**
   * Adjust the IEF entity table to utilize the View output.
   *
   * @param \Drupal\views\Plugin\views\ViewsPluginInterface $display
   *   The View display plugin.
   * @param array $entities
   *   The entity array.
   *
   * @return array
   *   Table field data for the IEF widget.
   */
  protected function processIefTableEntities(ViewsPluginInterface $display, array $entities) {
    /** @var \Drupal\views\Plugin\views\display\DisplayPluginBase $display */
    $view = $display->view;

    // Grab the entities already set in the widget and pass their IDs to the
    // view as contextual arguments.
    $entity_ids = $field_entities = [];
    foreach ($entities as $key => $val) {
      if (is_numeric($key) && !empty($val['#entity'])) {
        $entity_ids[] = $val['#entity']->id();
        $field_entities[] = $val['#entity'];
      }
    }

    // NULL for newly added entites (not yet saved).
    $entity_ids = array_filter($entity_ids);

    // Run the view and allow the display to produce the table columns.
    $view->setArguments([implode(',', $entity_ids)]);
    $view->execute($display->display['id']);

    // Is there a better way to handle this?
    if ($display instanceof InlineEntityFormTableDisplay) {
      $display->setResultEntities($field_entities);
    }

    /** @var \Drupal\ief_table_view\InlineEntityForm\InlineEntityFormTableProviderInterface|\Drupal\views\Plugin\views\ViewsPluginInterface $display */
    return $display->getInlineEntityFormTableColumns();
  }

}
