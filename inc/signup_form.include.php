<?php CalculatieTool::signup_form_error_messages(); ?>

<div class="ctsignup_signup" <?php isset( $id ) ? _e('id="' . $id . '"') : null ?> >
	<form id="ctsignup_signup_form" action="" method="post">
		<p>
			<label for="ctsignup_signup_first"><?php _e('Voornaam (verplicht)'); ?></label>
			<input name="ctsignup_signup_first" id="ctsignup_signup_first" type="text" value="<?php isset($_POST["ctsignup_signup_first"]) ? _e($_POST["ctsignup_signup_first"]) : null ?>" data-validation="required"/>
		</p>
		<p>
			<label for="ctsignup_signup_last"><?php _e('Achternaam (verplicht)'); ?></label>
			<input name="ctsignup_signup_last" id="ctsignup_signup_last" type="text" value="<?php isset($_POST["ctsignup_signup_last"]) ? _e($_POST["ctsignup_signup_last"]) : null ?>" data-validation="required"/>
		</p>
		<p>
			<label for="ctsignup_signup_email"><?php _e('Email (verplicht)'); ?></label>
			<input name="ctsignup_signup_email" id="ctsignup_signup_email" class="required" type="email" value="<?php isset($_POST["ctsignup_signup_email"]) ? _e($_POST["ctsignup_signup_email"]) : null ?>" required data-validation="email"/>
		</p>
		<p>
			<label for="ctsignup_signup_phone"><?php _e('Telefoonnummer (verplicht)'); ?></label>
			<input name="ctsignup_signup_phone" id="ctsignup_signup_phone" type="text" value="<?php isset($_POST["ctsignup_signup_phone"]) ? _e($_POST["ctsignup_signup_phone"]) : null ?>" data-validation="number" data-validation-length="max12" />
		</p>
		<p>
			<label for="ctsignup_signup_company"><?php _e('Bedrijfsnaam (verplicht)'); ?></label>
			<input name="ctsignup_signup_company" id="ctsignup_signup_company" class="required" type="text" value="<?php isset($_POST["ctsignup_signup_company"]) ? _e($_POST["ctsignup_signup_company"]) : null ?>" data-validation="required"/>
		</p>
		<p>
			<label for="ctsignup_signup_account"><?php _e('Gebruikersnaam (verplicht)'); ?></label>
			<input name="ctsignup_signup_account" id="ctsignup_signup_account" class="required" type="text" value="<?php isset($_POST["ctsignup_signup_account"]) ? _e($_POST["ctsignup_signup_account"]) : null ?>" data-sanitize="trim lower" data-validation="server" data-validation-url="<?php _e(add_query_arg( 'usercheck', true )); ?>"/>
		</p>
		<p>
			<label for="password"><?php _e('Wachtwoord (verplicht)'); ?></label>
			<input name="ctsignup_signup_pass" id="password" class="required" type="password" data-validation="length" data-validation-length="min5"/>
		</p>
		<p>
			<label for="password_again"><?php _e('Herhaal wachtwoord (verplicht)'); ?></label>
			<input name="ctsignup_signup_pass_confirm" id="password_again" class="required" type="password" data-validation="confirmation" data-validation-confirm="ctsignup_signup_pass"/>
		</p>
		<p>
			<label for="ctsignup_signup_agreement"><?php _e('Ga akkoord met de algemene voorwaarden'); ?> *</label>
			<input name="ctsignup_signup_agreement" type="checkbox" data-validation="required">
		</p>
		<p>
			<?php if ( isset( $tags ) ) { ?>
			<input type="hidden" name="ctsignup_signup_form_tags" value="<?php _e( $tags ) ?>"/>
			<?php } ?>
			<input type="hidden" name="ctsignup_signup_form_redirect" value="<?php _e( $redirect ) ?>"/>
			<input type="submit" name="ctsignup_signup_form_save" value="<?php _e('Registreer account'); ?>"/>
		</p>
	</form>
</div>
