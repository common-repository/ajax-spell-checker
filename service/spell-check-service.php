<?php
/**
:folding=explicit:collapseFolds=1:
* This is the communication interface for the Ajax web service.
*
* The input data is unicode escaped html, the output is executable javascript code.
* I went for javascript return instead of XML or HTML in order to minimize network
* overhead and client side computing overhead. Besides, the client side is pretty much
* out of my control, so it's better if I do most of the processing on stable grounds.
* The ideal would have been to send the annotated HTML source back, and use minimal
* javascript on the client side, but network traffic would have increased a lot, so
* this looks like a reasonable tradeoff.
*
* @package AjaxSpellChecker
* @subpackage WebService
* @author Sabin Iacob (m0n5t3r) <iacobs@m0n5t3r.info>
* @copyright Copyright &copy; 2006 Sabin Iacob
* @version 0.6
* @license http://creativecommons.org/licenses/GPL/2.0/ GNU General Public License
*/

/**
* includes
*/
include "../../../../wp-config.php";
include "spell-check-library.php";

//{{{ UTF-8 input handling functions

//{{{ code2utf($num)
/**
* converts charcodes to utf-8 characters
*
* code taken from {@link http://www.kanolife.com/escape/2006/03/unicode-url-escapes-in-php.html}
* @param int $num the utf-8 character code
* @return string
*/

function code2utf($num){
	if($num<128)
		return chr($num);
	if($num<1024)
		return chr(($num>>6)+192).chr(($num&63)+128);
	if($num<32768)
		return chr(($num>>12)+224).chr((($num>>6)&63)+128).chr(($num&63)+128);
	if($num<2097152)
		return chr(($num>>18)+240).chr((($num>>12)&63)+128).chr((($num>>6)&63)+128).chr(($num&63)+128);
	return '';
}
//}}}

//{{{ unescape($strIn, $iconv_to = 'UTF-8')
/**
* converts javascript-escaped unicode back to utf-8
*
* code taken from {@link http://www.kanolife.com/escape/2006/03/unicode-url-escapes-in-php.html} and
* modified to use mbstring instead of iconv
*
* @param string $strIn the javascript unicode escaped string
* @param string $iconv_to the destination charset (default utf-8)
* @return string
*/

function unescape($strIn, $iconv_to = 'UTF-8') {
	$strOut = '';
	$iPos = 0;
	$len = strlen ($strIn);
	while ($iPos < $len) {
		$charAt = substr ($strIn, $iPos, 1);
		if ($charAt == '%') {
			$iPos++;
			$charAt = substr ($strIn, $iPos, 1);
			if ($charAt == 'u') {
				// Unicode character
				$iPos++;
				$unicodeHexVal = substr ($strIn, $iPos, 4);
				$unicode = hexdec ($unicodeHexVal);
				$strOut .= code2utf($unicode);
				$iPos += 4;
			}
			else {
				// Escaped ascii character
				$hexVal = substr ($strIn, $iPos, 2);
				if (hexdec($hexVal) > 127) {
					// Convert to Unicode
					$strOut .= code2utf(hexdec ($hexVal));
				}
				else {
					$strOut .= chr (hexdec ($hexVal));
				}
				$iPos += 2;
			}
		}
		else {
			$strOut .= $charAt;
			$iPos++;
		}
	}
	if ($iconv_to != "UTF-8") {
		$strOut = mb_convert_encoding($strOut, $iconv_to);
	}
	return $strOut;
}
//}}}

//}}}

//{{{ variables init
$action = "";
$content = "";
$backend = get_option("as_backend");
$as_options = array(
	"lang"					=> get_option("as_lang"),
	"runTogether"			=> get_option("as_runtogether"),
	"personal"				=> ABSPATH . get_option("as_personal") . "/custom." . get_option("as_lang") . ".pws",
	"repl"					=> ABSPATH . get_option("as_repl") . "/custom." . get_option("as_lang") . ".prepl",
	"maxSuggestions"		=> get_option("as_maxsug"),
	"customDict"			=> get_option("as_custom"),
	"customDictLocation"	=> ABSPATH . get_option("as_custompath"),
	"charset"				=> get_option("blog_charset")
);

$factory = new SpellChecker($as_options);

//}}}

//{{{ request handling
switch($_SERVER["REQUEST_METHOD"]){
	case "GET":
		$action = $_GET["do"];
		$content = preg_replace("/[0-9]/", " ", unescape($_GET["content"])); //hack for a strange segfault
		break;
	case "POST":
		$action = $_POST["do"];
		$content = preg_replace("/[0-9]/", " ", unescape($_POST["content"])); //hack for a strange segfault
		break;
	default:
		die("Request not understood");
}
//}}}

//{{{ ... and output
ob_start('ob_gzhandler');

switch($action) {
	case "check":
		$spell = $factory->create($content, $backend);
		header("Content-type: text/plain; charset=$as_options[charset]");
		echo "updateDisplay(" . $spell->toJSArray() . ")";
		break;
	case "store":
		$pair = explode(":", $content);
		$spell = $factory->create("", $backend);
		$spell->storeReplacement($pair[0], $pair[1]);
		header("Content-type: text/javascript; charset=$as_options[charset]");
		echo "checkSpelling()";
		break;
	case "add":
		$spell = $factory->create("", $backend);
		$spell->addWord($content);
		header("Content-type: text/javascript; charset=$as_options[charset]");
		echo "checkSpelling()";
		break;
	default:
		die("I wish you humans would leave me alone!");
}
//}}}

?>