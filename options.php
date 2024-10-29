<?php
/**
:folding=explicit:collapseFolds=1:
* This is the Ajax Spell Checker plugin options page
*
* @package AjaxSpellChecker
* @subpackage Plugin
* @author Sabin Iacob (m0n5t3r) <iacobs@m0n5t3r.info>
* @copyright Copyright &copy; 2006 Sabin Iacob
* @version 0.7
* @license http://creativecommons.org/licenses/GPL/2.0/ GNU General Public License
*/

include "options-lib.php";
include "service/spell-check-library.php";

$preferred_backend = get_option("as_backend");
$factory = new SpellChecker();
$spelltest = $factory->create("", get_option("as_backend"));

the_options_header("Spell Checker Options");

if($spelltest === false) {
	the_errors("It looks like none of the provided spell checking backends can work on your system!",$factory->errorLog());
} else {
	$options = array();
	the_form_header();
	$backends_av = $factory->backends();
	$backends = $factory->backends();
	foreach($backends as $b => $v)
		$backends[$b] = $b;
	display_option($options, "Preferred backend", "as_backend", "select", $backends, "", $backends_av);
	display_option($options, "Language", "as_lang", "select", $spelltest->supportedLanguages());
	display_option($options, "Max. suggestions", "as_maxsug", "text", false);
	display_option($options, "Ignore run-together words", "as_runtogether", "checkbox", false);
	display_option($options, "Custom word list location", "as_personal", "text", false, "The above directory needs to be writable by the web server; path is relative to blog root.");
	display_option($options, "Custom replacement list location", "as_repl", "text", false, "The above directory needs to be writable by the web server; path is relative to blog root.");
	display_option($options, "Use custom main dictionary", "as_custom", "checkbox", false);
	display_option($options, "Custom dictionary location", "as_custompath", "text", false, "Path is relative to blog root");
	the_form_footer($options);
	?>
<?php }

the_options_footer();
?>