<div class="container-fluid">
	<h4 class="pb-2 bold-700">Modifier un contact</h4>
	<hr class="mt-0">
	<div class="row">
		<div class="col-md-6">
			<form id="form-updateContact">
				<input type="hidden" name="id_contact" value="<?= $contact->id_contact ?>">
				<div class="form-group">
					<label class="bold-700 mb-0">Email</label>
					<input class="form-control" type="email" name="email_contact" autocomplete="off" value="<?= $contact->email_contact ?>"></input>
				</div>
				<div class="form-group">
					<label class="bold-700 mb-0">Nom</label>
					<input class="form-control" type="text" name="firstname_contact" autocomplete="off" value="<?= $contact->firstname_contact ?>"></input>
				</div>
				<div class="form-group">
					<label class="bold-700 mb-0">Prénom(s)</label>
					<input class="form-control" type="text" name="lastname_contact" autocomplete="off" value="<?= $contact->lastname_contact ?>"></input>
				</div>
				<div class="form-group">
					<label class="bold-700 mb-0">Entreprise</label>
					<input class="form-control" type="text" name="entreprise_contact" autocomplete="off" value="<?= $contact->entreprise_contact ?>"></input>
				</div>
				<div class="form-group">
					<label class="bold-700 mb-0">Poste</label>
					<input class="form-control" type="text" name="poste_contact" autocomplete="off" value="<?= $contact->poste_contact ?>"></input>
				</div>
				<div class="form-group">
					<label class="bold-700 mb-0">Téléphone</label>
					<input class="form-control" type="text" name="telephone_contact" autocomplete="off" value="<?= $contact->telephone_contact ?>"></input>
				</div>

				<div class="form-group text-right">
					<button type="submit" class="btn btn-info">
						<i class="fas fa-save"></i>
						<span class="ml-2">Mettre à jour</span>
					</button>
				</div>
			</form>
		</div>
	</div>
	
</div>