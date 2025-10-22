	                        <?php foreach($campagnes as $campagne): ?>
	                        <tr>
	                            <td><?= $campagne->name_campagne ?></td>
	                            <td><?= $campagne->debut_campagne ?></td>
	                            <td><?= $campagne->fin_campagne ?></td>
	                            <td><?= $campagne->objectif_campagne ?></td>
	                            <td>
	                            <span class="mr-2 text-success update-campagne" data-campagne="<?= $campagne->id_campagne ?>" data-name="<?= $campagne->name_campagne ?>" data-debut="<?= $campagne->debut_campagne ?>" data-fin="<?= $campagne->fin_campagne ?>" data-objectif="<?= $campagne->objectif_campagne ?>">
	                                    <i class="fas fa-edit"></i>
	                                </span>
	                                <span class="mr-2 text-danger delete-campagne" data-campagne="<?= $campagne->id_campagne ?>">
	                                    <i class="fas fa-trash"></i>
	                                </span>
	                            </td>
	                        </tr>
	                        <?php endforeach ?>
