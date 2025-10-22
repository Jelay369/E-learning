
	                        <?php foreach($contacts as $contact): ?>
	                        <tr>
	                            <td><?= $contact->email_contact ?></td>
	                            <td><?= $contact->firstname_contact ?></td>
	                            <td><?= $contact->secteur_activite ?></td>
	                            <td>
	                            <span class="mr-2 text-success update-contact" data-contact="<?= $contact->id_contact ?>" data-email="<?= $contact->email_contact ?>" data-firstname="<?= $contact->firstname_contact ?>" data-lastname="<?= $contact->lastname_contact ?>" data-entreprise="<?= $contact->entreprise_contact ?>" data-poste="<?= $contact->poste_contact ?>" data-telephone="<?= $contact->telephone_contact ?>" data-secteur="<?= $contact->secteur_activite ?>">
	                                    <i class="fas fa-edit"></i>
	                                </span>
	                                <span class="mr-2 text-danger delete-contact" data-contact="<?= $contact->id_contact ?>">
	                                    <i class="fas fa-trash"></i>
	                                </span>
	                            </td>
	                        </tr>
	                        <?php endforeach ?>
