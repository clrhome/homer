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
    <link href="/logo.css" type="text/css" rel="stylesheet" />
    <link href="homer.css" type="text/css" rel="stylesheet" />
    <script src="/bin/js/ga.js"></script>
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
        <a href="/resources/">
          <span>another resource by</span>
          <img src="/images/emblem.png" alt="ClrHome" />
        </a>
      </h2>
      <div>
        <p>Made by <a href="https://fishbotwilleatyou.com/">Deep Toaster</a>. Have a suggestion? <a href="mailto:deeptoaster@gmail.com">Send me an email</a> or <a href="https://github.com/deeptoaster/opcode-table">open a pull request</a>!</p>
        <p>Layout of text in the textbox may not accurately depict the resulting image.</p>
        <p>Some special characters may not appear in the textbox but will appear in the image.</p>
      </div>
    </blockquote>
  </body>
</html>
