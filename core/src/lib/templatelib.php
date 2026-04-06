<?php

function renderTemplateBuffer(string $tpl_path, array $vars): string {
  extract($vars, EXTR_SKIP);
  ob_start();
  require $tpl_path;
  return (string)ob_get_clean();
}

