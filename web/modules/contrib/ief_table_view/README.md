# IEF Table View

The IEF Table View module in an integration between the core Views module and 
[Inline Entity Form](https://www.drupal.org/project/inline_entity_form) which 
allows the IEF entity table to be generated using the output of a View.

## Installation

Download and install per your preferred module installation method. See the 
[Drupal documentation](https://www.drupal.org/docs/8/extending-drupal-8/installing-drupal-8-modules)
for more information.

## Architecture

Included in this module is a field widget that extends the "Inline entity form - 
Complex" widget from the IEF module. This is configurable so a View may be 
specified to control the columns that appear in the IEF widget's entity table.   

Also included are Views display and Views style plugins that allow the View's 
results to be utilized by the entity table of the field widget. 

## Configuration

1.  Set up the View.
    1.  Create a new view or add to an existing View matching the entity type of
    the entity reference field.
    1.  Add an "Inline Entity Form Table" display.
    1.  **Format:** You'll likely only have one style available, "Inline  Entity
    Form Table". Use this. Note the style has all of the settings provided by 
    the core "Table" style. These will affect the live preview, but many will 
    not affect the IEF entity table, ie click sorting, grouping, etc. 
    1.  **Contextual Filters**: The display should have exactly one contextual
    filter, for the entity ID (Node ID, User ID, etc.). This is the most 
    important piece of the View configuration.
        1. **When the filter value is NOT available:** "Hide view"
        1. **Allow multiple values:** Checked
    1.  **Filter criteria:** Leave this empty, the entity reference field will 
    explicitly identify the entities to return in the view. Having additional 
    filter criteria may prevent the entities referenced from displaying in the
    IEF table. 
    1.  **Sort criteria:** You may specify sort criteria but it will not be 
    utilized for the IEF table. The entities will render in the field item delta 
    order.
    1.  **Pager:** The pager functionality is ignored by the Inline Entity Form 
    Table display.
1.  Set up the field widget.
    1.  Find the widget configuration on the "Manage form display" tab of the 
    entity type and bundle's field configuration screens.
    1.  For each desired field, switch to using the "Inline entity form - 
    Complex w/Views table" widget.
    1.  Set the widget configuration for "Entity table view/display" to the 
    desired View and display that has been previously configured.
    1.  Set the rest of the widget configuration per the standard "Inline entity
    form - Complex" widget.
    1.  Press the Update button, then save your configuration.
    
You should now see the IEF widget with an entity table using the columns as 
configured in your View(s).       

## Developers

If you're already using an field widget that extends the "Inline entity form - 
Complex" widget and would like to use the Views integration for the entity 
table you can add this behavior with 
`\Drupal\ief_table_view\InlineEntityForm\InlineEntityFormViewsTableWidgetTrait`.
See 
`\Drupal\ief_table_view\Plugin\Field\FieldWidget\InlineEntityFormComplexTableView`
for an example of how to implement.
