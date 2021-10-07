<?php

namespace Drupal\helstra_phenomenon\Plugin\Field\FieldFormatter;

use Drupal\Component\Utility\Html;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Menu\MenuTreeParameters;
use Drupal\Core\Url;
use Drupal\node\NodeInterface;
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
    $parent_entity = $item->getEntity();
    $links = $this->getArticles($parent_entity);
    if (empty($links))
      return '';

    $template = sprintf('helstra_phenomenon_field_%s', $this->getSetting('display_type'));
    $content = [];
    $tags = [
      'config:menu_list',
      'menu_link_content_list',
      'menu_link_content_list:menu_link_content',
    ];

    foreach ($links as $link) {
      $menu_link = $link['menu_link'];
      $content['parent'] = $parent_entity->id();
      $content['nodes'][] = [
        'entity' => \Drupal::entityTypeManager()->getViewBuilder('node')->view($link['node'], 'teaser'),
        'label' => $menu_link->getPluginDefinition()['title'],
        'link' => Link::createFromRoute($menu_link->getTitle(), $menu_link->getRouteName(), $menu_link->getRouteParameters())->toRenderable(),
      ];
      $tags[] = 'menu_link_content:' . $menu_link->getMetadata()['entity_id'];
    }

    $render = [
      '#theme' => $template,
      '#content' => $content,
      '#count' => count($links),
      '#cache' => [
        'tags' => $tags
      ]
    ];
    return \Drupal::service('renderer')->render($render);
  }

  /**
   * Returns articles tagged with phenomena term.
   * @param NodeInterface $node
   * @return array
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function getArticles(NodeInterface $node) : array {
    $node_storage = \Drupal::entityTypeManager()->getStorage('node');
    $menu_link_manager = \Drupal::service('plugin.manager.menu.link');
    $menu_link_tree = \Drupal::service('menu.link_tree');
    $result = $menu_link_manager->loadLinksByRoute('entity.node.canonical', array('node' => $node->id()));

    if (empty($result))
      return [];

    $links = [];
    $menu_tree_parameters = new MenuTreeParameters();
    $menu_tree_parameters->setMinDepth(1);

    foreach ($result as $menu_link) {
      if ($menu_link->getMenuName() != 'main')
        continue;

      $menu_tree_parameters->setRoot($menu_link->getPluginId());
      $children = $menu_link_tree->load('main', $menu_tree_parameters);

      foreach ($children as $child) {
        $link = $child->link;
        // Don't render disabled links.
        if (!$link->isEnabled())
          continue;

        // Check that the link is node.
        $params = $link->getRouteParameters();
        if (empty($params['node']))
          continue;

        $links[$link->getWeight()] = [
          'node' => $node_storage->load($params['node']),
          'menu_link' => $link,
        ];
      }
    }

    if (empty($links))
      return [];

    ksort($links);

    return $links;
  }

}
