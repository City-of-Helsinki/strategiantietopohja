<?php

namespace Drupal\helstra_article\Plugin\Block;

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

/**
 * Provides a 'ArticleIndexBlock' block.
 *
 * @Block(
 *  id = "article_index_block",
 *  admin_label = @Translation("Article index block"),
 * )
 */
class ArticleIndexBlock extends BlockBase implements ContainerFactoryPluginInterface {

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
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = new static($configuration, $plugin_id, $plugin_definition);
    $instance->entityTypeManager = $container->get('entity_type.manager');
    $instance->routeMatch = $container->get('current_route_match');

    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $index = $this->buildArticleIndex();
    $build = [];
    $build['#theme'] = 'article_index_block';
    $build['#index'] = $index;
    return $build;
  }

  /**
   * @return array
   */
  protected function buildArticleIndex() {
    $index = [];
    $node = $this->getNode();
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
   * @return NodeInterface
   */
  protected function getNode() : NodeInterface {
    $obj = $this->routeMatch->getParameter('node');
    if (!$obj instanceof NodeInterface) {
      throw new \UnexpectedValueException("Not a node page");
    }

    return $obj;
  }

  /**
   * @param AccountInterface $account
   * @return AccessResult|\Drupal\Core\Access\AccessResultForbidden
   */
  protected function blockAccess(AccountInterface $account) {
    try {
      $node = $this->getNode();
    }
    catch (\UnexpectedValueException $ex) {
      return AccessResult::forbidden();
    }
    if ($node->bundle() != 'article')
      return AccessResult::forbidden();
    return parent::blockAccess($account);
  }
  /**
   * @return array|string[]
   */
  public function getCacheTags() {
    $node = $this->getNode();
    return Cache::mergeTags(parent::getCacheTags(), ['node:' . $node->id()]);
  }

  /**
   * @return array|string[]
   */
  public function getCacheContexts() {
    return Cache::mergeContexts(parent::getCacheContexts(), ['route']);
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
    return sprintf('%s.%s.', $section, $subsection);
  }

}
