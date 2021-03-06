<div class="legents">
	<h3 class="legent_headers"><?php echo $this->legent; ?></h3>

	<div class="form_wrapper">

	<?php echo $this->start; ?>

		<?php foreach($this->fields as $name => $field) : ?>

		<div class="form-block">

			<?php echo $this->labels[$name]; ?>

			<div class="form-right-block">

				<?php echo $this->fields[$name]['html']; ?>

				<?php if(isset($this->errors[$name])) : ?>
				<span class="error"><?php echo $this->errors[$name]; ?></span>
				<?php endif; ?>

				<p class="info"><?php echo $this->fields[$name]['info']; ?></p>
			</div>
		</div> <!-- end of input_group -->

		<?php endforeach; ?>


		<div class="form-block form-action button-wrapper">

			<?php echo $this->submit; ?>

			<?php echo $this->reset; ?>

			<?php echo $this->cancel; ?>

		</div>

	</form>

	</div> <!-- end of form_wrapper -->
</div> <!-- end of legents -->
