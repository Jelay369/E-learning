
	                        <?php foreach($emails_envoye as $email): ?>
	                        <tr>
	                            <td><?= $email->destinataire_mail ?></td>
	                            <td><?= $email->objet_mail ?></td>
	                            <td><?= $email->message_mail ?></td>
	                            <td>
	                                <span class="mr-2 text-danger delete-email" data-email="<?= $email->id_mail_envoye ?>">
	                                    <i class="fas fa-trash"></i>
	                                </span>
	                            </td>
	                        </tr>
	                        <?php endforeach ?>
