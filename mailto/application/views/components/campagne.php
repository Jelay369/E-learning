<div class="container-fluid">
	<h4 class="pb-2 bold-700">Campagne</h4>
	<hr class="mt-0">
	<form id="form-campagne" action="<?= base_url('Campagne/create') ?>">
		<input type="hidden" name="id_campagne" value="-1" id="id-campagne-input">
		<div class="row">
			<div class="col-md-6">
			    <div class="form-group">
			        <label class="mb-0 bold-700">Nom de la campagne</label>
			        <input class="form-control" type="text" autocomplete="off" name="name_campagne">
			    </div>
			    <div class="form-group">
			        <label class="mb-0 bold-700">Date de début</label>
			        <input class="form-control" type="date" autocomplete="off" name="debut_campagne">
			    </div>
			    <div class="form-group">
			        <label class="mb-0 bold-700">Date de fin</label>
			        <input class="form-control" type="date" autocomplete="off" name="fin_campagne">
			    </div>	
			    <div class="form-group">
			        <label class="mb-0 bold-700">Description (objectif)</label>
			        <textarea name="objectif_campagne" class="form-control" rows="5"></textarea>
			    </div>
			    <div class="form-group text-right">
			    	<button type="submit" class="btn btn-info">
			    		<i class="fa fa-save"></i>
			    		<span id="btn-submit-campagne">Enregistrer</span>
			    	</button>
			    </div>			
	        </div>
		</div>
	</form>

	<div class="table-responsive scroll-moz scroll-all px-0 table-campagne">

	    <table id="" class="table table-striped table-bordered table-sm tableau-sticky">
	        <thead>
	            <tr>
	                <th class="th-lg">Nom de la campagne</th>
	                <th class="th-lg">Début</th>
	                <th class="th-lg">Fin</th>
	                <th class="th-lg">Objectif</th>
	                <th class="th-lg">Action</th>
	            </tr>
	        </thead>

	        <tbody id="table-list-campagne">
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
