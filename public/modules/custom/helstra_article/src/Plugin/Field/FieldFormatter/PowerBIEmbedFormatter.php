<?php

namespace Drupal\helstra_article\Plugin\Field\FieldFormatter;

use Drupal\Component\Utility\Html;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'powerbi_embed_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "powerbi_embed_formatter",
 *   label = @Translation("PowerBI embed formatter"),
 *   field_types = {
 *     "string"
 *   }
 * )
 */
class PowerBIEmbedFormatter extends FormatterBase {

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
      $url = $item->value;
      $elements[$delta] = [
        '#theme' => 'powerbi_embed_paragraph',
        '#url' => $url,
      ];
    }

    return $elements;
  }
}
