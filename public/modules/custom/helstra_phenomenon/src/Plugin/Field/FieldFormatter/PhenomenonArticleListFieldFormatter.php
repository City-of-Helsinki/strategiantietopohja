<?php

namespace Drupal\helstra_phenomenon\Plugin\Field\FieldFormatter;

use Drupal\Component\Utility\Html;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\taxonomy\Entity\Term;
use Drupal\taxonomy\Plugin\views\wizard\TaxonomyTerm;

/**
 * Plugin implementation of the 'phenomenon_article_list_field_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "phenomenon_article_list_field_formatter",
 *   label = @Translation("Phenomenon article list field formatter"),
 *   field_types = {
 *     "entity_reference"
 *   }
 * )
 */
class PhenomenonArticleListFieldFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      // Implement default settings.
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    return [
      // Implement settings form.
    ] + parent::settingsForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    // Implement settings summary.

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $delta => $item) {
      $elements[$delta]['#markup'] = $this->viewValue($item);
    }

    return $elements;
  }

  /**
   * Generate the output appropriate for one field item.
   *
   * @param \Drupal\Core\Field\FieldItemInterface $item
   *   One field item.
   *
   * @return string
   *   The textual output generated.
   */
  protected function viewValue(FieldItemInterface $item) {
    $term = $item->entity;
    $nodes = $this->getArticles($term);
    if (empty($nodes))
      return '';

    $renderer = \Drupal::service('renderer');
    $options = [
      '#type' => 'container',
      '#title' => Html::escape($term->label()),
      '#attributes' => [
        'class' => [
          'phenomenon-article-links'
        ],
      ],
      0 => '',
    ];
    foreach ($nodes as $node) {
      $options[] =  $node->toLink()->toRenderable();
    }
    return $renderer->render($options);
  }

  /**
   * Returns articles tagged with phenomena term.
   *
   * @param Term $term
   * @return array
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function getArticles(Term $term) : array {
    $nodes = $nodes = \Drupal::entityTypeManager()->getStorage('node')->loadByProperties([
      'type' => 'article',
      'field_phenomena' => $term->id(),
    ]);

    if (empty($nodes))
      return [];

    return $nodes;
  }

}
