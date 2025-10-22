
	                        <?php foreach($templates as $template): ?>
	                        <tr>
	                            <td><?= $template->name_template ?></td>
	                            <td><?= $template->couleur_header_template ?></td>
	                            <td><?= $template->couleur_fond_template ?></td>
	                            <td><?= $template->telephone_template ?></td>
	                            <td>
	                            <span class="mr-2 text-success update-template" data-template="<?= $template->id_template ?>" data-name="<?= $template->name_template ?>" data-hcolor="<?= $template->couleur_header_template ?>" data-fcolor="<?= $template->couleur_fond_template ?>" data-telephone="<?= $template->telephone_template ?>" data-facebook="<?= $template->facebook_template ?>" data-twitter="<?= $template->twitter_template ?>" data-linkedin="<?= $template->linkedin_template ?>" data-youtube="<?= $template->youtube_template ?>" data-site="<?= $template->site_web_template ?>" data-logo="<?= $template->logo_template ?>">
	                                    <i class="fas fa-edit"></i>
	                                </span>
	                                <span class="mr-2 text-danger delete-template" data-template="<?= $template->id_template ?>">
	                                    <i class="fas fa-trash"></i>
	                                </span>
	                            </td>
	                        </tr>
	                        <?php endforeach ?>
