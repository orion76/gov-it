<?php

namespace Drupal\ief_table_view\InlineEntityForm;

/**
 * Interface InlineEntityFormTableProviderInterface.
 */
interface InlineEntityFormTableProviderInterface {

  /**
   * Get the fields used to represent an entity in the IEF table.
   */
  public function getInlineEntityFormTableColumns();

}
