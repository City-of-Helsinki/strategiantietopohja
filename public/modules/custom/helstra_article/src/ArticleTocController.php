<?php

namespace Drupal\helstra_article;

use Drupal\Component\Utility\Html;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Link;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\node\NodeInterface;
use Drupal\paragraphs\ParagraphInterface;
use Drupal\paragraphs\Plugin\migrate\source\d7\ParagraphsItem;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ArticleTocController {

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * @var
   */
  protected $routeMatch;

  /**
   * ArticleToc constructor.
   */
  public function __construct() {
    $this->entityTypeManager = \Drupal::service('entity_type.manager');
    $this->routeMatch = \Drupal::service('current_route_match');
  }

  /**
   * {@inheritdoc}
   */
  public function build(NodeInterface $node) {
    $build = [];
    $build['#theme'] = 'article_toc';
    $build['#toc'] = $this->buildArticleToc($node);
    return $build;
  }

  /**
   * @return array
   */
  protected function buildArticleToc(NodeInterface $node) {
    $index = [];
    if (empty($node->field_content))
      return [];
    $paragraphs = $node->field_content;

    if (empty($paragraphs))
      return [];

    foreach ($paragraphs as $paragraph) {
      $paragraph_entity = $paragraph->entity;
      if ($paragraph_entity->getType() != 'section_title_text')
        continue;
      $section = $paragraph_entity->field_section->value;
      $subsection = $paragraph_entity->get('field_subsection')->value;
      $title = $paragraph_entity->field_section_title->value;

      if (!empty($subsection)) {
        $item['parent'] = $section;
        $item['section'] = $subsection;
        $index[$section]['children'][$subsection] = [
          'parent' => $section,
          'section' => $this->getSection($paragraph_entity),
          'title' => $title,
          'link_url' => $this->buildAnchor($paragraph_entity)
        ];
        continue;
      }

      $index[$section]['title'] = $title;
      $index[$section]['section'] = $this->getSection($paragraph_entity);
      $index[$section]['link_url'] = $this->buildAnchor($paragraph_entity);
    }

    return $index;
  }

  /**
   * Build anchor link from item.
   *
   * @param ParagraphInterface $entity
   * @return string
   */
  protected function buildAnchor(ParagraphInterface $entity) {
    $section = $this->getSection($entity);
    $value = Html::cleanCssIdentifier($entity->field_section_title->value);

    if (empty($section))
      $id = sprintf("#%s", $value);
    else
      $id = sprintf("#%s-%s", $section, $value);

    return $id;
  }

  /**
   * @param $entity
   * @return string
   */
  protected function getSection($entity) {
    $section = $entity->field_section->value;
    $subsection = $entity->field_subsection->value;
    if (empty($section))
      return '';
    if (empty($subsection))
      return sprintf('%s.', $section);
    return sprintf('%s.%s', $section, $subsection);
  }
}
