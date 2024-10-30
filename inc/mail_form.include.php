<?php CalculatieTool::signup_form_error_messages(); ?>

<div class="ctsignup_mail" <?php isset( $id ) ? _e('id="' . $id . '"') : null ?> >
	<form id="ctsignup_mail_form" action="" method="post">
		<p>
			<label for="ctsignup_mail_first"><?php _e('Voornaam (verplicht)') ; ?></label>
			<input name="ctsignup_mail_first" id="ctsignup_mail_first" type="text" value="<?php isset($_POST["ctsignup_mail_first"]) ? _e($_POST["ctsignup_mail_first"]) : null ?>" data-validation="required"/>
		</p>
		<p>
			<label for="ctsignup_mail_last"><?php _e('Achternaam (verplicht)'); ?></label>
			<input name="ctsignup_mail_last" id="ctsignup_mail_last" type="text" value="<?php isset($_POST["ctsignup_mail_last"]) ? _e($_POST["ctsignup_mail_last"]) : null ?>" data-validation="required"/>
		</p>
		<p>
			<label for="ctsignup_mail_email"><?php _e('Email (verplicht)'); ?></label>
			<input name="ctsignup_mail_email" id="ctsignup_mail_email" class="required" type="email" value="<?php isset($_POST["ctsignup_mail_email"]) ? _e($_POST["ctsignup_mail_email"]) : null ?>" required data-validation="email"/>
		</p>
		<p>
			<label for="ctsignup_mail_phone"><?php _e('Telefoonnummer (verplicht)'); ?></label>
			<input name="ctsignup_mail_phone" id="ctsignup_mail_phone" type="text" value="<?php isset($_POST["ctsignup_mail_phone"]) ? _e($_POST["ctsignup_mail_phone"]) : null ?>" data-validation="required"/>
		</p>
		<p>
			<label for="ctsignup_mail_comment"><?php _e('Opmerking'); ?></label>
			<textarea name="ctsignup_mail_comment" id="ctsignup_mail_comment"><?php isset($_POST["ctsignup_mail_comment"]) ? _e($_POST["ctsignup_mail_comment"]) : null ?></textarea>
		</p>
		<p>
			<?php if ( isset( $tags ) ) { ?>
			<input type="hidden" name="ctsignup_mail_form_tags" value="<?php _e( $tags ) ?>"/>
			<?php } ?>
			<input type="hidden" name="ctsignup_mail_form_redirect" value="<?php _e( $redirect ) ?>"/>
			<input type="submit" name="ctsignup_mail_form_save" value="<?php _e('Versturen'); ?>"/>
		</p>
	</form>
</div>
