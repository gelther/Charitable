<?php
/**
 * The template used to display textarea fields.
 *
 * @author 	Studio 164a
 * @since 	1.0.0
 * @version 1.0.0
 */

$form = charitable_get_current_donation_form();

if ( ! $form ) {
	return;
}

$field = $form->get_current_field();
?>
<div class="charitable-form-field">
	<?php if ( isset( $field['label'] ) ) : ?>
		<label for="charitable_field_<?php echo $field['key'] ?>"><?php echo $field['label'] ?></label>
	<?php endif ?>
	<textarea name="<?php echo $field['key'] ?>"></textarea>
</div>