<?php
  function renderBrand(): string {
    $brand_name_1 = t('brand-name-1');
    $brand_name_2 = t('brand-name-2');

    return "<span id='highlighted-text-1'>" . htmlspecialchars($brand_name_1) . "</span>" . "<span id='highlighted-text-2'>" . htmlspecialchars($brand_name_2) . "</span>";
  }
