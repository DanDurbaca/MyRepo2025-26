<?php
  class Translator {
    private string $lang;
    private array $translations = [];
    private array $fallbacks = [];

    public function __construct(string $lang, array $translations, array $fallbacks = []) {
      $this->lang = $lang;
      $this->translations = $translations;
      $this->fallbacks = $fallbacks;
    }

    public function t(string $key, array $replacements = []): string {
      $text = $this->translations[$key]
      ?? $this->fallbacks[$key]
      ?? $key;

      foreach ($replacements as $k => $v) {
        $text = str_replace("{" . $k . "}", $v, $text);
      }

      return $text;
    }

    public function getLang(): string {
      return $this->lang;
    }
  }
