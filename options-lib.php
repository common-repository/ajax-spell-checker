<?php
/**
:folding=explicit:collapseFolds=1:
* Library containing template functions useful in building plugin options pages.
*
* @package AjaxSpellChecker
* @subpackage OptionsToolkit
* @author Sabin Iacob (m0n5t3r) <iacobs@m0n5t3r.info>
* @copyright Copyright &copy; 2006 Sabin Iacob
* @version 0.7
* @license http://creativecommons.org/licenses/GPL/2.0/ GNU General Public License
*/

/**
* @param mixed $disabled value to compare with
* @param mixed $current current value
*/
function disabled($disabled, $current) {
	if($disabled === $current)
		echo ' disabled="disabled"';
}

//{{{ function the_options_header($title)
/**
* display the page header
* @param string $title page title
*/
function the_options_header($title) {
?>
<div class="wrap">
<h2><?php echo $title ?></h2>
<?php }
//}}}

//{{{ function the_options_footer()
/**
* finish the options page
*/
function the_options_footer(){
?>
</div>
<?php }
//}}}

//{{{ function the_errors($title, $errors)
/**
* display error page;
* @param string $title error page title
* @param array $errors Array containing errors
*/
function the_errors($title, $errors) {
?>
<h3 style="color: red;"><?php echo $title ?></h3>
<ul>
<?php foreach($errors as $error => $reasons) { ?>
	<li>
	<?php
	echo $error;
	if(is_array($reasons)) {
	?>
	<ul><?php
		foreach($reasons as $reason) { ?>
		<li><?php echo $reason ?></li> <?php } ?>
	</ul><?php } ?>
	</li>
<?php } ?>
</ul>
<?php }
//}}}

//{{{ function the_form_header()
/**
* display the form header
*/
function the_form_header() {
?>
<form action="options.php" method="post">
<fieldset class="options">
<table class="editform optiontable">
<?php }
//}}}

//{{{ function the_form_footer(&$options)
/**
* Display the form footer
* @param array &$options Array containing option names
*/
function the_form_footer(&$options) {
?>
</table>
</fieldset>
<p class="submit">
<input type="hidden" name="action" value="update" />
<input type="hidden" name="page_options" value="<?php echo join(",",$options) ?>" />
<input type="submit" name="Submit" value="Update Options &raquo;" />
</p>
</form>
<?php }
//}}}

//{{{ function display_option(&$options, $label, $name, $type, $values, $info = false, $enabled = true)
/**
* display an option field and add the option name to the options list;
* @param array &$options Option names array
* @param string $label Option label
* @param string $name Option name
* @param string $type Option type; can be "text", "checkbox", "select"
* @param array $values Option possible values (for select fields)
* @param string $info Addidional information
* @param bool|array $enabled Whether the option is enabled, or array of enabled options for selects
*/
function display_option(&$options, $label, $name, $type, $values, $info = false, $enabled = true) {
	$options[] = $name;
	if($type != "checkbox") {
?>
<tr valign="top">
<th scope="row">
<label for="<?php echo $name ?>" title="<?php echo $label ?>"><?php echo $label ?>:</label>
</th>
<?php } else {
?><td></td>
<?php } ?>
<td>
<?php
switch($type){
	case "text": ?>
		<input type="text" name="<?php echo $name ?>" id="<?php echo $name ?>" class="code" value="<?php $val = get_option($name); echo $val ?>" size="<?php echo is_numeric($val) ? 10 : 40 ?>" alt="<?php echo $label ?>" title="<?php echo $label ?>"<?php disabled(false, $enabled) ?> />
	<?php
		break;
	case "checkbox":?>
		<label for="<?php echo $name ?>" title="<?php echo $label ?>">
		<input type="checkbox" name="<?php echo $name ?>" id="<?php echo $name ?>"<?php checked(1,get_option($name)) ?> value="1"<?php disabled(false, $enabled) ?> /> <?php echo $label ?>
		</label>
	<?php
		break;
	case "select": ?>
		<select name="<?php echo $name ?>" id="<?php echo $name ?>">
		<?php
		$none_selected = true;
		$current = get_option($name);
		$default = $current;
		/**
		* retain the first enabled option as the default if there is no match
		*/
		foreach($values as $vn=>$vl) {
			if($vn == $current && (isset($enabled[$vn]) ? $enabled[$vn]:true)) {
				$none_selected = false;
				break;
			}
			if($default == $current && $enabled[$vn])
				$default = $vn;
		}
		foreach($values as $vn=>$vl) {
			$selected_cond = ($vn == $current && (isset($enabled[$vn]) ? $enabled[$vn] : true)) || ($none_selected && $vn == $default); ?>
			<option value="<?php echo $vn ?>"<?php selected(true, $selected_cond); ?><?php disabled(false, $enabled[$vn]) ?>><?php echo $vl ?></option>
		<?php }?>
		</select>
<?php }

if($info) echo "<br />$info";

?>
</td>
</tr>
<?php }
//}}}
?>