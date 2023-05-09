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
    <title>Homer - ClrHome</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <style type="text/css">
      @font-face {
        font-family: calc;
        src: url('/bin/fonts/ti-calc.eot');
      }
      @font-face {
        font-family: calc;
        src: url('/bin/fonts/ti-calc.ttf');
      }
      body {
        background-color: #666;
        font-size: 24px;
        color: #333;
      }
      body, input {
        font-family: 'Trebuchet MS', Helvetica, sans-serif;
      }
      fieldset h1 {
        margin: 0 12px 12px 12px;
        color: #333;
        font: small-caps bold 1em 'Palatino Linotype', 'Book Antiqua', Palatino, serif;
        text-align: left;
      }
      fieldset h2 {
        float: right;
        margin: 0 12px;
        font-size: 1em;
      }
      fieldset p {
        margin: 12px 12px 0 12px;
        font-size: 0.5em;
      }
      a {
        text-decoration: none;
        color: #333;
      }
      a:hover {
        color: #666;
      }
      a img {
        border: 0;
      }
      a:hover img {
        visibility: hidden;
      }
      form {
        min-width: 840px;
        width: 80%;
        height: 480px;
        margin: 0 auto;
        padding: 32px 24px;
        -moz-border-radius: 32px;
        -webkit-border-radius: 32px;
        border-radius: 32px;
        background-color: #333;
      }
      fieldset {
        float: left;
        width: 550px;
        margin: 0;
        -moz-border-radius: 24px;
        -webkit-border-radius: 24px;
        border-radius: 24px;
        padding: 24px 12px;
        background-color: #ccc;
      }
      textarea {
        display: block;
        margin: auto;
        border: 0;
        -moz-border-radius: 8px;
        -webkit-border-radius: 8px;
        border-radius: 8px;
        -moz-box-shadow: inset 0 0 8px #000;
        -webkit-box-shadow: inset 0 0 8px #000;
        box-shadow: inset 0 0 8px #000;
        padding: 16px 32px;
        overflow: hidden;
        background-color: #9eab88;
        color: #1a1c16;
        font-size: 2em;
        font-family: calc, 'Consolas Bold', 'Monaco Bold', 'Courier New Bold', monospace;
        line-height: 0.8;
        word-break: break-all;
        resize: none;
      }
      textarea:focus {
        outline: 0;
      }
      span {
        display: none;
        position: absolute;
        background-color: #fff;
      }
      h1 {
        margin: 32px 0;
        text-align: center;
        font-size: 0.5em;
        vertical-align: top;
      }
      form > p {
        margin-left: 600px;
        font-size: 0.5em;
        color: #ccc;
      }
      form > div {
        margin-left: 600px;
        font-family: calc, 'Lucida Console', Monaco, monospace;
      }
      div div {
        height: 280px;
        overflow: auto;
      }
      div a {
        background-repeat: no-repeat;
        background-position: 0 4px;
        color: #993;
        cursor: pointer;
      }
      input[type=submit] {
        position: absolute;
        border: 2px outset #66f;
        border-bottom-width: 3px;
        -moz-border-radius: 16px;
        -webkit-border-radius: 16px;
        border-radius: 16px;
        -moz-box-shadow: 0 0 8px #000;
        -webkit-box-shadow: 0 0 8px #000;
        box-shadow: 0 0 8px #000;
        padding: 8px 24px 6px 24px;
        background-color: #339;
        color: #fff;
        font-weight: bold;
        font-size: 1em;
        cursor: pointer;
      }
      input[type=submit]:active {
        border-width: 1px;
        padding: 9px 25px 8px 25px;
        -moz-box-shadow: 0 0 4px #000;
        -webkit-box-shadow: 0 0 4px #000;
        box-shadow: 0 0 4px #000;
      }
      input[type=submit]:active + * textarea {
        animation: flash 600ms;
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
      <input type="submit" accesskey="s" value="Screenshot!" />
      <fieldset>
        <h2>
          <a href="/">@ ClrHome</a>
        </h2>
        <h1><img src="icon.gif" alt="" /> Homescreen Image Maker</h1>
        <textarea rows="8" cols="16" name="q">

Your batteries
are low.

Recommend
change of
batteries.
</textarea>
        <span></span>
        <p>Or upload an 8XP for program source: <input type="file" name="file" /></p>
      </fieldset>
      <div></div>
      <p>Copyright &copy; 2011 DEEP THOUGHT.<br />Layout of text in the textbox may not accurately reflect how it will look as an image.<br />Some special characters may not appear in the textbox but will appear in the image.</p>
    </form>
  </body>
</html>
