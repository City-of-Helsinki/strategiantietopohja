<?php

use Drupal\paragraphs\ParagraphInterface;
use Drupal\paragraphs\Entity\Paragraph;
 
$ids = \Drupal::entityQuery('paragraph')->execute();

foreach ($ids as $id) {
  $paragraph = Paragraph::load($id);

  if (!empty($paragraph->field_text->value)) {
    $paragraph->field_text->value = trim(str_replace('&nbsp;', ' ', $paragraph->field_text->value));
    $paragraph->field_text->value = trim(str_replace('<span><span><span>', '', $paragraph->field_text->value));
    $paragraph->field_text->value = trim(str_replace('</span></span></span>', '', $paragraph->field_text->value));
    $paragraph->field_text->value = trim(str_replace('.  <', '.<', $paragraph->field_text->value));
    $paragraph->field_text->value = trim(str_replace('. <', '.<', $paragraph->field_text->value));
    $paragraph->field_text->value = trim(str_replace('    ', ' ', $paragraph->field_text->value));
    $paragraph->field_text->value = trim(str_replace('   ', ' ', $paragraph->field_text->value));
    $paragraph->field_text->value = trim(str_replace('  ', ' ', $paragraph->field_text->value));
    $paragraph->save();
  }
}
