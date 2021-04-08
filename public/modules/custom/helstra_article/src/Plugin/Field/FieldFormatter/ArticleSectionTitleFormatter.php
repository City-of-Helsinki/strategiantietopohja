<?php

namespace Drupal\helstra_article\Plugin\Field\FieldFormatter;

use Drupal\Component\Utility\Html;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Plugin implementation of the 'article_section_title_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "article_section_title_formatter",
 *   label = @Translation("Article section title formatter"),
 *   field_types = {
 *     "string"
 *   }
 * )
 */
class ArticleSectionTitleFormatter extends FormatterBase {

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
      $elements[$delta]['title'] = [
        '#markup' => $this->viewValue($item),
      ];
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
    $entity = $item->getEntity();
    // The text value has no text format assigned to it, so the user input
    // should equal the output, including newlines.
    $section = $this->getSection($entity);
    $value = nl2br(Html::escape($item->value));
    $id = $this->buildAnchor($item);

    if (empty($section))
      $title = sprintf("<h2 id=%s>%s</h2>", $id, $value);
    else
      $title = sprintf("<h2 id=%s>%s %s</h2>", $id, $section, $value);

    return $title;
  }

  /**
   * Build anchor link from item.
   *
   * @param FieldItemInterface $item
   * @return string
   */
  protected function buildAnchor(FieldItemInterface $item) {
    $entity = $item->getEntity();
    $section = $this->getSection($entity);
    $value = Html::cleanCssIdentifier($item->value);
    if (empty($section))
      $id = sprintf("%s", $value);
    else
      $id = sprintf("%s-%s", $section, $value);
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
