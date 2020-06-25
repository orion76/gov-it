<?php

namespace Drupal\ief_table_view\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\ief_table_view\InlineEntityForm\InlineEntityFormViewsTableWidgetTrait;
use Drupal\inline_entity_form\Plugin\Field\FieldWidget\InlineEntityFormComplex;
use Drupal\views\Entity\View;

/**
 * Complex inline widget w/views table.
 *
 * @FieldWidget(
 *   id = "ief_table_view_complex",
 *   label = @Translation("Inline entity form - Complex w/Views table"),
 *   field_types = {
 *     "entity_reference"
 *   },
 *   multiple_values = true
 * )
 */
class InlineEntityFormComplexTableView extends InlineEntityFormComplex {

  use InlineEntityFormViewsTableWidgetTrait;

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    $defaults = parent::defaultSettings();
    $defaults += [
      'view_display' => NULL,
    ];
    return $defaults;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::settingsForm($form, $form_state);

    $element['view_display'] = [
      '#type' => 'select',
      '#title' => $this->t('Entity table view/display'),
      '#description' => $this->t('Specify the View and display that will populate the entity table.'),
      '#default_value' => $this->getSetting('view_display'),
      '#options' => $this->getViewsDisplayOptions($this->getFieldSetting('target_type')),
      '#required' => TRUE,
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();

    if ($display = $this->getViewDisplay($this->getSetting('view_display'))) {
      $label[] = $display->view->storage->label();
      $label[] = $display->pluginTitle();
      $label = implode(' - ', $label);
      $summary[] = $this->t('Entity table view: @label', ['@label' => $label]);
    }
    else {
      $summary[] = $this->t('Entity table view: Does not exist');
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    $dependencies = parent::calculateDependencies();
    if ($display = $this->getViewDisplay($this->getSetting('view_display'))) {
      /** @var \Drupal\views\ViewExecutable $view */
      $view = $display->view;
      $dependencies['config'][] = 'views.view.' . $view->id();
    }
    return $dependencies;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);
    $element['#process'][] = [$this, 'processForm'];
    return $element;
  }

  /**
   * Process callback to integrate view with IEF entity table.
   *
   * It's necessary to perform this in a process callback in order to set
   * unsaved entities into the View results. Without this, a re-render of the
   * form due to validation errors results in empty rows for unsaved entities.
   *
   * @param array $element
   *   The widget's form element.
   *
   * @return array
   *   The form element, processed for views-powered IEF entity table.
   */
  public function processForm(array $element) {
    if (!$display = $this->getViewDisplay($this->getSetting('view_display'))) {
      return $element;
    }

    if ($fields = $this->processIefTableEntities($display, $element['entities'])) {
      $element['entities']['#table_fields'] = $fields;
    }

    return $element;
  }

}
