<div class="container-fluid">
	<h4 class="pb-2 bold-700">Ajouter une template</h4>
	<hr class="mt-0">
	<form id="form-addTemplate" action="<?= base_url("Template/create") ?>">
		<div class="row row-formTemplate scroll-moz scroll-all">
			<div class="col-md-6">
					<input type="hidden" name="id_template" value="-1" id="id-template-input">
					<div class="form-group">
						<label class="bold-700 mb-0">Nom du template</label>
						<input class="form-control form-control-sm" type="text" name="name_template" autocomplete="off"></input>
					</div>
					<div class="form-group">
						<label class="bold-700 mb-0">Couleur de l'entête</label>
						<input class="form-control form-control-sm" type="color" name="couleur_header_template" autocomplete="off" value="#757575"></input>
					</div>
					<div class="form-group">
						<label class="bold-700 mb-0">Couleur du fond</label>
						<input class="form-control form-control-sm" type="color" name="couleur_fond_template" autocomplete="off" value="#f0f0f0"></input>
					</div>
					<div class="form-group">
						<label class="bold-700 mb-0">Téléphone</label>
						<input class="form-control form-control-sm" type="text" name="telephone_template" autocomplete="off"></input>
					</div>
					<div class="form-group">
						<label class="bold-700 mb-0">Facebook</label>
						<input class="form-control form-control-sm" type="text" name="facebook_template" autocomplete="off"></input>
					</div>
					<div class="form-group">
						<label class="bold-700 mb-0">Twitter</label>
						<input class="form-control form-control-sm" type="text" name="twitter_template" autocomplete="off"></input>
					</div>
					<div class="form-group">
						<label class="bold-700 mb-0">LinkedIn</label>
						<input class="form-control form-control-sm" type="text" name="linkedin_template" autocomplete="off"></input>
					</div>
					<div class="form-group">
						<label class="bold-700 mb-0">Youtube</label>
						<input class="form-control form-control-sm" type="text" name="youtube_template" autocomplete="off"></input>
					</div>
					<div class="form-group">
						<label class="bold-700 mb-0">Site web</label>
						<input class="form-control form-control-sm" type="text" name="site_web_template" autocomplete="off"></input>
					</div>
				
			</div>
			<div class="col-md-6">
				<label class="bold-700 mb-0">Logo template</label>
				<input type="file" name="logo_template" class="d-none" id="file-logo-template">
				<div class="mt-1 p-4 border w-50">
					<img src="<?= base_url('public/img/logo.png') ?>" alt="LOGO" width="100%" id="preview-logo-template">
				</div>
				<label class="btn btn-blue-grey w-50 ml-0" for="file-logo-template">Modifier</label>
			</div>
			
		</div>
		<hr>
		<div class="row">
			<div class="col-md-12">
				<div class="form-group text-right">
						<button type="button" class="btn btn-info" data-toggle="modal" data-target="#preview-template">
							<i class="fas fa-eye"></i>
							<span class="ml-2" id="btn-preview-template">Previsualisation</span>
						</button>
						<button type="submit" class="btn btn-info">
							<i class="fas fa-save"></i>
							<span class="ml-2" id="btn-submit-template">Enregistrer</span>
						</button>
				</div>
			</div>
		</div>
	</form>
	<div class="row mt-5">
		
		<div class="col-12">
			<div class="table-responsive scroll-moz scroll-all px-0 table-template">

	                <table id="" class="table table-striped table-bordered table-sm tableau-sticky">
	                    <thead>
	                        <tr>
	                            <th class="th-lg">Nom</th>
	                            <th class="th-lg">Couleur entête</th>
	                            <th class="th-lg">Couleur de fond</th>
	                            <th class="th-lg">Téléphone</th>
	                            <th class="th-lg">Action</th>
	                        </tr>
	                    </thead>

	                    <tbody id="table-list-template">
	                    	<tr>
	                    		<td colspan="5">
	                    			<div style="height: 20vh;" class="d-flex align-items-center justify-content-center">
										<div class="spinner-border spinner-border-sm" role="status">
										    <span class="sr-only">Loading...</span>
										</div>
	                    			</div>
	                    		</td>			
	                    	</tr>
	                    </tbody>
	                </table>
	        </div>
        </div>
	</div>
	
</div>

<!--------------------------Modal prévisualisation template------------------------------>
<div class="modal fade" id="preview-template" tabindex="-1" role="dialog" aria-labelledby="modal-template-header"
  aria-hidden="true" data-backdrop="static">

  <!-- Change class .modal-sm to change the size of the modal -->
  <div class="modal-dialog modal-xl" role="document">


    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title w-100" id="modal-template-header">Prévisualisation</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
           <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="preview-template-html">
      	<div class="d-flex justify-content-center align-items-center w-100 spinner-template">
              <div class="spinner-border" role="status">
                <span class="sr-only">Loading...</span>
              </div>
        </div>
      <div class="modal-footer w-100">
        <button type="button" class="btn btn-primary btn-sm" id="btn-close-preview-template">Fermer</button>
      </div>
    </div>
  </div>
</div>