<?
namespace ClrHome;

error_reporting(0);
include(__DIR__ . '/../lib/tools/Program.class.php');
include(__DIR__ . '/src/classes/HomerCode.class.php');

$homer_code = new HomerCode();

if (is_uploaded_file($_FILES['file']['tmp_name'])) {
	$variables = Program::fromFile($_FILES['file']['tmp_name'], 1);

	if (count($variables) === 1 && (
		$variables[0]->getType() === VariableType::PROGRAM ||
				$variables[0]->getType() === VariableType::PROGRAM_LOCKED
	)) {
		$_SERVER['REQUEST_URI'] = '/homer/%' .
				implode('%', str_split(strtoupper(bin2hex(':' . str_replace(
			"\xd6",
			"\xd6:",
			$variables[0]->getBodyAsTiChars()
		))), 2)) . '.gif';
	}
} elseif (array_key_exists('q', $_POST)) {
	header('Location: ' . str_replace(
		'%2F',
		'/',
		rawurlencode($homer_code->encode($_POST['q'])) . '.gif'
	));

	die();
}

if (preg_match('#^/homer/(.*)\.(gif|jpg|png)$#', $_SERVER['REQUEST_URI'], $match)) {
	if (preg_match('#^%FF%FF((/\d+){7})$#', $match[1], $submatch)) {
		$numbers = explode('/', $submatch[1]);
		imagecolorallocate($image = imagecreate(14, 18), $numbers[4], $numbers[5], $numbers[6]);
		imagettftext($image, 18, 0, 0, 16, imagecolorallocate($image, $numbers[1], $numbers[2], $numbers[3]), '../lib/fonts/calc.ttf', chr($numbers[7]));
	} else {
		$lines = explode("\xd6", urldecode($match[1]));
		$height = count($lines);

		foreach ($lines as $line)
			$height += (int)((strlen(str_replace(chr(0xff), '', $line)) - 1) / 16);

		$bg = imagecolorallocate($image = imagecreate(192, max($height * 16, 128)), 0x9e, 0xab, 0x88);
		$fg = imagecolorallocate($image, 0x1a, 0x1c, 0x16);
		$y = $l = 0;

		foreach ($lines as $line) {
			$y++;
			$x = 0;

			for ($j = 0; $j < strlen($line); $j++) {
				$k = $line[$j];

				if ($k == chr(0xff)) {
					$l = !$l;
					continue;
				}

				$y += $x && !($x % 16);
				$v = $x++ % 16 * 12;
				$w = $y * 16 - 2;

				if ($l) {
					imagefilledrectangle($image, $v - 2, $w - 14, $v + 9, $w + 1, $fg);
					imagettftext($image, 15, 0, $v, $w, -$bg, '../lib/fonts/calc.ttf', $k);
				} else {
					imagettftext($image, 15, 0, $v, $w, -$fg, '../lib/fonts/calc.ttf', $k);
				}
			}
		}
	}

	header('HTTP/1.0 200 OK');
	header('Content-Disposition: inline; filename=homer.' . $match[2]);

	switch ($match[2]) {
		case 'png':
			header('Content-Type: image/png');
			imagepng($image);
			die();
		case 'jpg':
			header('Content-Type: image/jpeg');
			imagejpeg($image);
			die();
		default:
			header('Content-Type: image/gif');
			imagegif($image);
			die();
	}
}
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>Homer - ClrHome</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<style type="text/css">
			@font-face {
				font-family: calc;
				src: url('/lib/fonts/calc.eot');
			}
			@font-face {
				font-family: calc;
				src: url('/lib/fonts/calc.ttf');
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
				-moz-border-radius: 24px;
				-webkit-border-radius: 24px;
				border-radius: 24px;
				padding: 24px 12px;
				background-color: #ccc;
			}
			textarea {
				display: block;
				width: 460px;
				height: 304px;
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
				white-space: pre;
				word-wrap: break-word;
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
				color: #993;
				cursor: pointer;
			}
			input[type=submit] {
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
				font-size: 2em;
				cursor: pointer;
			}
			input[type=submit]:active {
				border-width: 1px;
				padding: 9px 25px 8px 25px;
				-moz-box-shadow: 0 0 4px #000;
				-webkit-box-shadow: 0 0 4px #000;
				box-shadow: 0 0 4px #000;
			}
		</style>
		<link rel="shortcut icon" href="/favicon.ico" />
		<script type="text/javascript" src="/lib/js/jquery.js"></script>
		<script type="text/javascript" src="/lib/js/ga.js"></script>
		<script type="text/javascript">// <![CDATA[
			$(function() {
				s = $('textarea').get(0);
				z = [];
<?
foreach ($homer_code->getMap() as $unicode => $ti)
	echo "				z[$ti] = $unicode;\n";
?>
				$('div a').click(function() {
					var e = $(this).index() + 1;
					e = String.fromCharCode(z[e] ? z[e] : e);

					if (document.selection) {
						s.focus();
						var f = document.selection.createRange();
						f.text = e;
						f.select();
					} else {
						var f = s.selectionStart;
						$(s).val($(s).val().slice(0, f) + e + $(s).val().slice(s.selectionEnd));
						s.setSelectionRange(f + 1, f + 1);
					}

					s.focus();
				});

				$('form').submit(function(e) {
					$('span').css({top: $(s).offset().top, left: $(s).offset().left, width: $(s).innerWidth(), height: $(s).innerHeight(), display: 'block'}).fadeOut();
				});
			});
		// ]]></script>
	</head>
	<body>
		<form enctype="multipart/form-data" method="post" action="./" accept-charset="UTF-8">
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
			<div>
				<div>
<?
for ($i = 1; $i < 0xf5; $i++) {
	if ($i == 0x0a or $i == 0x0d or $i == 0x7f) {
		echo '<a></a>';
		continue;
	}

	echo '					<a', $i % 0x80 < 0x20 ? " style=\"background-image: url('%FF%FF/102/102/102/51/51/51/$i.png'); background-repeat: no-repeat; background-position: 0 4px;\"><img src=\"%FF%FF/153/153/51/51/51/51/$i.png\" alt=\"\" />" : '>' . htmlentities(utf8_encode(chr($i)), null, 'UTF-8'), '</a>
';
}
?>				</div>
			</div>
			<h1>
				<input type="submit" accesskey="s" value="Screenshot!" />
			</h1>
			<p>Copyright &copy; 2011 DEEP THOUGHT.<br />Layout of text in the textbox may not accurately reflect how it will look as an image.<br />Some special characters may not appear in the textbox but will appear in the image.</p>
		</form>
	</body>
</html>
