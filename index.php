<?
namespace ClrHome;

include(__DIR__ . '/../lib/tools/Picture.class.php');
include(__DIR__ . '/../lib/tools/Program.class.php');
include(__DIR__ . '/src/classes/HomerCode.class.php');

abstract class HomerFormat extends Enum {
  const GIF = 'image/gif';
  const JPEG = 'image/jpeg';
  const PNG = 'image/png';
}

define('ClrHome\HOMER_BG_COLOR', '9eab88');
define('ClrHome\HOMER_FG_COLOR', '1a1c16');
define('ClrHome\HOMER_FONT', '../bin/fonts/ti-calc.ttf');

define('ClrHome\HOMER_FORMATS', [
  'gif' => HomerFormat::GIF,
  'jpeg' => HomerFormat::JPEG,
  'jpg' => HomerFormat::JPEG,
  'png' => HomerFormat::PNG
]);

function make_image_color($image, $color) {
  $color_rgb = array_map('hexdec', str_split($color, 2));

  return imagecolorallocate(
    $image,
    $color_rgb[0],
    $color_rgb[1],
    $color_rgb[2]
  );
}

$homer_code = new HomerCode();
$output_extension = 'gif';
$output_image = null;
$output_text = null;

if (is_uploaded_file(@$_FILES['file']['tmp_name'])) {
  $variables = Program::fromFile($_FILES['file']['tmp_name'], 1);

  if (count($variables) >= 1 && (
    $variables[0]->getType() === VariableType::PROGRAM ||
        $variables[0]->getType() === VariableType::PROGRAM_LOCKED
  )) {
    $output_text =
        ':' . str_replace("\xd6", "\xd6:", $variables[0]->getBodyAsTiChars());
  }
} else if (array_key_exists('q', $_REQUEST)) {
  if (
    array_key_exists('bg_color', $_GET) && array_key_exists('fg_color', $_GET)
  ) {
    $output_image = imagecreate(14, 18);
    make_image_color($output_image, str_pad($_GET['bg_color'], 6, '0'));

    imagettftext(
      $output_image,
      18,
      0,
      0,
      16,
      make_image_color($output_image, str_pad($_GET['fg_color'], 6, '0')),
      HOMER_FONT,
      $_REQUEST['q']
    );
  } else {
    header('Location: ' . str_replace(
      '%2F',
      '/',
      rawurlencode($homer_code->encode($_REQUEST['q'])) . ".$output_extension"
    ));

    die();
  }
} else if (
  preg_match('/^\/homer\/(.*)\.(gif|jpe?g|png)$/',
  $_SERVER['REQUEST_URI'],
  $match
)) {
  $output_text = urldecode($match[1]);
  $output_extension = $match[2];
}

if ($output_text !== null) {
  $lines = explode(HOMER_NEWLINE, $output_text);

  $line_count = array_sum(array_map(function(string $line) {
    return (int)((strlen(str_replace(HOMER_INVERT, '', $line)) - 1) / 16) + 1;
  }, $lines));

  $output_image = imagecreate(
    PICTURE_COLUMN_COUNT * 2,
    max($line_count * 8, PICTURE_ROW_COUNT) * 2
  );

  $bg_color = make_image_color($output_image, HOMER_BG_COLOR);
  $fg_color = make_image_color($output_image, HOMER_FG_COLOR);
  $inverted = false;
  $y = 14;

  foreach ($lines as $line) {
    $x = 0;

    for (
      $character_index = 0;
      $character_index < strlen($line);
      $character_index++
    ) {
      if ($line[$character_index] === HOMER_INVERT) {
        $inverted = !$inverted;
        continue;
      }

      switch ($inverted) {
        case false:
          imagettftext(
            $output_image,
            15,
            0,
            $x, $y,
            -$fg_color,
            HOMER_FONT,
            $line[$character_index]
          );

          break;
        case true:
          imagefilledrectangle(
            $output_image,
            $x - 2,
            $y - 14,
            $x + 9,
            $y + 1,
            $fg_color
          );

          imagettftext(
            $output_image,
            15,
            0,
            $x, $y,
            -$bg_color,
            HOMER_FONT,
            $line[$character_index]
          );

          break;
      }

      $x += 12;

      if ($x === PICTURE_COLUMN_COUNT * 2) {
        $x = 0;
        $y += 16;
      }
    }

    $y += 16;
  }
}

if ($output_image !== null) {
  header('HTTP/1.0 200 OK');
  header('Content-Disposition: inline; filename=homer.' . $output_extension);

  switch ($output_extension) {
    case 'png':
      header('Content-Type: image/png');
      imagepng($output_image);
      break;
    case 'jpg':
      header('Content-Type: image/jpeg');
      imagejpeg($output_image);
      break;
    default:
      header('Content-Type: image/gif');
      imagegif($output_image);
      break;
  }

  die();
}
?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
  <head>
    <title>Homer</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link href="https://clrhome.org/logo.css" type="text/css" rel="stylesheet" />
    <style type="text/css">
      @font-face {
        font-family: ti-calc;
        src: url('/bin/fonts/ti-calc.eot');
      }
      @font-face {
        font-family: ti-calc;
        src: url('/bin/fonts/ti-calc.ttf');
      }
      body, input {
        font-family: sans-serif;
      }
      body {
        background-color: #272722;
        font-size: 24px;
      }
      form, blockquote{
        width: 44em;
        margin: 2em auto;
      }
      form {
        position: relative;
      }
      input[type=submit] {
        display: block;
        position: absolute;
        top: 21em;
        left: 50%;
        width: 10em;
        margin-left: -5.3em;
        border: 0.1em outset #44897a;
        border-radius: 0.5em;
        box-shadow: 0 0 0.4em #000;
        padding: 0.5em;
        background-color: #256e5d;
        color: #d3dad9;
        font-weight: bold;
        font-size: 1em;
        cursor: pointer;
      }
      input[type=submit]:active {
        border-width: 0.05em;
        border-color: #256e5d;
        padding: 0.55em;
        box-shadow: 0 0 0.2em #000;
        background-color: #0e5243;
        color: #a6c3bc;
      }
      input[type=submit]:active + * textarea {
        animation: flash 600ms;
      }
      fieldset {
        width: 21em;
        float: left;
        margin: 0;
        border-radius: 1em;
        padding: 0 1em;
        background-color: #d3d2c6;
      }
      h1 {
        margin: 0.5em 0;
        color: #272722;
        font: small-caps bold 1em 'Palatino Linotype', 'Book Antiqua', Palatino, serif;
      }
      h1::before {
        content: '\01f4f8\fe0e';
        padding-right: 0.4em;
        font-size: 1.2em;
      }
      textarea, form > div {
        font-family: ti-calc, monospace;
      }
      textarea {
        display: block;
        margin: auto;
        border: 0;
        border-radius: 0.2em;
        box-shadow: inset 0 0 0.2em #000;
        padding: 0.3em 0.6em;
        overflow: hidden;
        background-color: #9eab88;
        color: #1a1c16;
        font-size: 2em;
        line-height: 0.8;
        word-break: break-all;
        resize: none;
      }
      textarea:focus {
        outline: 0;
      }
      fieldset p {
        margin: 1em 0;
        text-align: right;
        font-size: 0.6em;
      }
      form > div {
        float: left;
        width: 18.5em;
        margin-left: 2em;
      }
      input[type=file] {
        margin-left: 1em;
        font-size: 1em;
      }
      div a {
        background-repeat: no-repeat;
        background-position: 0 4px;
        color: #993;
        text-decoration: none;
        cursor: pointer;
      }
      div a:hover {
        color: #666;
      }
      div a img {
        border: 0;
      }
      div a:hover img {
        visibility: hidden;
      }
      blockquote, blockquote a {
        color: #d3d2c6;
      }
      blockquote {
        clear: left;
        padding-top: 6em;
      }
      .logo {
        float: right;
        font-size: 0.8em;
      }
      cite {
        display: block;
        font-size: 0.6em;
        font-style: normal;
      }
      @keyframes flash {
        0% {
          background-color: #fff;
          color: #fff;
        }
        100% {
          background-color: #9eab88;
          color: #1a1c16;
        }
      }
    </style>
    <script src="/lib/js/ga.js"></script>
    <script src="homer.js?v=2023-04-30"></script>
  </head>
  <body>
    <form enctype="multipart/form-data" method="post" action="./" accept-charset="UTF-8">
      <input type="submit" value="Screenshot!" />
      <fieldset>
        <h1>Homescreen Image Maker</h1>
        <textarea rows="8" cols="16" name="q">

Your batteries
are low.

Recommend
change of
batteries.
</textarea>
        <p>Or upload an 8XP for program source: <input type="file" name="file" /></p>
      </fieldset>
      <div></div>
    </form>
    <blockquote>
      <h2 class="logo">
        <a href="https://clrhome.org/resources/">
          <span>another resource by</span>
          <img src="https://clrhome.org/images/emblem.png" alt="ClrHome" />
        </a>
      </h2>
      <cite>
        <p>Made by <a href="https://fishbotwilleatyou.com/">Deep Toaster</a>. Have a suggestion? <a href="mailto:deeptoaster@gmail.com">Send me an email</a> or <a href="https://github.com/deeptoaster/opcode-table">open a pull request</a>!</p>
        <p>Layout of text in the textbox may not accurately depict the resulting image.</p>
        <p>Some special characters may not appear in the textbox but will appear in the image.</p>
      </cite>
    </blockquote>
  </body>
</html>
