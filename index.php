<?
error_reporting(0);
include('../lib/tools/Program.class.php');

function unhex($match) {
	return pack('H2', substr($match[0], 2));
}

$ascii = array('\x11', '\x01', '\x02', '\x03', '\x04', '\x05', '\x06', '\x07', '\x08', '\x09', '\x0b', '\x0c', '\x0e', '\x0f', '\x10', '\x11', '\x12', '\x13', '\x14', '\x15', '\x16', '\x17', '\x18', '\x19', '\x1a', '\x1b', '\x1c', '\x1d', '\x1e', '\x1f');
$utf8 = array("\xd7\x99\xd6\xbc", "\xd5\xbc", "\xe1\xb4\x9c", "\xe1\xb4\xa0", "\xe1\xb4\xa1", "\xe2\x96\xba", "\xe2\xac\x86", "\xe2\xac\x87", "\xe2\x88\xab", "\xe2\x85\xb9", "\xe2\x82\x8a", "\xe2\x88\x99", "\xe1\xb5\x8c", "\xea\x9c\xb0", "\xe2\x88\x9a", "\xef\xac\xb9", "\xe1\xb6\xbb", "\xe2\x88\xa0", "\xe2\x81\xb0", "\xca\xb3", "\xe1\xb5\x80", "\xe2\x89\xa4", "\xe2\x89\xa0", "\xe2\x89\xa5", "\xe2\x81\xbb", "\xe1\xb4\x87", "\xe2\x86\x92", "\xd1\x8e", "\xe2\x86\x91", "\xe2\x86\x93");
$utf16 = array(0, 0x057c, 0x1d1c, 0x1d20, 0x1d21, 0x25ba, 0x2b06, 0x2b07, 0x222b, 0x2179, 0x208a, 0x2219, 0x1d4c, 0xa730, 0x221a, 0xfb39, 0x1dbb, 0x2220, 0x2070, 0x02b3, 0x1d40, 0x2264, 0x2260, 0x2265, 0x207b, 0x1d07, 0x2192, 0x044e, 0x2191, 0x2193);

for ($i = 0; $i < 10; $i++) {
	$ascii[] = "\\x8$i";
	$utf8[] = "\xe2\x82" . chr(0x80 + $i);
	$utf16[] = 0x2080 + $i;
}

if (isset($_GET['t'])) {
	$_POST['q'] = $_GET['t'];
}

if (is_uploaded_file($_FILES['file']['tmp_name'])) {
	$variables = \ClrHome\Program::fromFile($_FILES['file']['tmp_name'], 1);

	if (count($variables) === 1 && (
		$variables[0]->getType() === \ClrHome\VariableType::PROGRAM ||
				$variables[0]->getType() === \ClrHome\VariableType::PROGRAM_LOCKED
	)) {
		$_SERVER['REQUEST_URI'] = '/homer/%' . implode('%', str_split(strtoupper(bin2hex(':' . str_replace("\xd6", "\xd6:", $variables[0]->getBodyAsTiChars()))), 2)) . '.gif';
	}
} elseif (isset($_POST['q'])) {
	header('Location: ' . str_replace('%2F', '/', rawurlencode(preg_replace_callback("#\\\\x[\da-fA-F]{2}#", 'unhex', preg_replace('#\r\n?|\n#', "\xd6", utf8_decode(str_replace($utf8, $ascii, stripslashes($_POST['q'])))))) . '.gif'));
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
foreach ($utf16 as $i => $ord)
	echo '				z[0' . substr($ascii[$i], 1) . "] = $ord;
";
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
