<?php
  function t(string $key, array $replacements = []): string {
    global $translations, $fallbackTranslations;

    $text = $translations[$key] ?? $fallbackTranslations[$key] ?? $key;

    foreach ($replacements as $placeholder => $replacement) {
      $text = str_replace("{" . $placeholder . "}", $replacement, $text);
    }

    return $text;
  }
