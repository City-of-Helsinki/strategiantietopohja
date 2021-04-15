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

  protected const SELECT_OPTIONS = [
    'list' => 'Dropdown List',
    'grid' => 'Grid list'
  ];

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'display_type' => 'list',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::settingsForm($form, $form_state);
    $form['display_type'] = [
      '#type' => 'select',
      '#label' => $this->t('Select display type'),
      '#options' => $this::SELECT_OPTIONS,
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $settings = $this->getSettings();
    $summary[] = $this->t('Selected display type: @selected', ['@selected' => $this::SELECT_OPTIONS[$settings['display_type']]]);

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
    $template = sprintf('helstra_phenomenon_field_%s', $this->getSetting('display_type'));
    $content = [];
    foreach ($nodes as $node) {
      $content['nodes'][] = [
        'entity' => \Drupal::entityTypeManager()->getViewBuilder('node')->view($node, 'teaser'),
        'label' => $node->getTitle(),
        'link' => $node->toLink()->toRenderable(),
      ];
    }
    $render = [
      '#theme' => $template,
      '#content' => $content,
      '#count' => count($nodes)
    ];
    $renderer = \Drupal::service('renderer');
    return $renderer->render($render);
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
