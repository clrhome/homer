<?php
namespace ClrHome;

final class HomerCode {
  private array $unicodeToTi = [];

  public function __construct() {
    $this->unicodeToTi[0x057c] = 0x01;
    $this->unicodeToTi[0x1d1c] = 0x02;
    $this->unicodeToTi[0x1d20] = 0x03;
    $this->unicodeToTi[0x1d21] = 0x04;
    $this->unicodeToTi[0x25ba] = 0x05;
    $this->unicodeToTi[0x2b06] = 0x06;
    $this->unicodeToTi[0x2b07] = 0x07;
    $this->unicodeToTi[0x222b] = 0x08;
    $this->unicodeToTi[0x2179] = 0x09;
    $this->unicodeToTi[0x208a] = 0x0b;
    $this->unicodeToTi[0x2219] = 0x0c;
    $this->unicodeToTi[0x1d4c] = 0x0e;
    $this->unicodeToTi[0xa730] = 0x0f;
    $this->unicodeToTi[0x221a] = 0x10;
    $this->unicodeToTi[0xfb39] = 0x11;
    $this->unicodeToTi[0x1dbb] = 0x12;
    $this->unicodeToTi[0x2220] = 0x13;
    $this->unicodeToTi[0x2070] = 0x14;
    $this->unicodeToTi[0x02b3] = 0x15;
    $this->unicodeToTi[0x1d40] = 0x16;
    $this->unicodeToTi[0x2264] = 0x17;
    $this->unicodeToTi[0x2260] = 0x18;
    $this->unicodeToTi[0x2265] = 0x19;
    $this->unicodeToTi[0x207b] = 0x1a;
    $this->unicodeToTi[0x1d07] = 0x1b;
    $this->unicodeToTi[0x2192] = 0x1c;
    $this->unicodeToTi[0x044e] = 0x1d;
    $this->unicodeToTi[0x2191] = 0x1e;
    $this->unicodeToTi[0x2193] = 0x1f;

    for ($index = 0; $index < 10; $index++) {
      $this->unicodeToTi[0x2080 + $index] = 0x80 + $index;
    }
  }

  public function encode(string $string) {
    return preg_replace_callback(
      '/\\\\x[\da-f]{2}/i',
      function(array $match) {
        return chr(hexdec(substr($match[0], 2)));
      },
      str_replace(
        array("\r\n", "\r", "\n"),
        "\xd6",
        utf8_decode(str_replace(
          array_map(function(int $codepoint) {
            return json_decode('"\u' . str_pad(
              dechex($codepoint),
              4,
              '0',
              STR_PAD_LEFT
            ) . '"');
          }, array_keys($this->unicodeToTi)),
          array_map(function(int $codepoint) {
            return '\x' . str_pad(
              dechex($codepoint),
              2,
              '0',
              STR_PAD_LEFT
            );
          }, array_values($this->unicodeToTi)),
          addcslashes($string, '\\')
        ))
      )
    );
  }

  public function getMap() {
    return $this->unicodeToTi;
  }
}
?>
