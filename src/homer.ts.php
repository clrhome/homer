<?php
namespace ClrHome;

include(__DIR__ . '/../lib/cleverly/Cleverly.class.php');
include(__DIR__ . '/classes/HomerCode.class.php');

$characters = [];

for ($ti = 1; $ti < 0xf5; $ti++) {
  if ($ti !== 0x0a && $ti !== 0x0d && $ti !== 0x7f) {
    $characters[$ti] = ['ti' => $ti, 'unicode' => $ti];

    if ($ti % 0x80 < 0x20 || $ti === 0xad) {
      $characters[$ti]['encoded'] =
          str_pad(strtoupper(dechex($ti)), 2, '0', STR_PAD_LEFT);
    }
  }
}

$unicode_to_ti = (new HomerCode())->getMap();

foreach ($unicode_to_ti as $unicode => $ti) {
  $characters[$ti]['unicode'] = $unicode;
}

$cleverly = new \Cleverly();
$cleverly->preserveIndent = true;
$cleverly->setTemplateDir(__DIR__ . '/templates');

$cleverly->display('homer.ts.tpl', [
  'characters' => $characters
]);
?>
