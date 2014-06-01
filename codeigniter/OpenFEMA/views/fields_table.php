<!DOCTYPE html>
<html>
	<head>
		<title>OpenFEMA Data Sets</title>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">

		<?php if($this->input->get('nojs') !== '1'): ?>
			<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">
			<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap-theme.min.css">
			<link rel="stylesheet" href="//netdna.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css">
			<link rel="stylesheet" href="//code.jquery.com/ui/1.10.4/themes/overcast/jquery-ui.css">
			<link rel="stylesheet" href="/OpenFEMA/css/dataSetsTable.css">

			<script src="//code.jquery.com/jquery-2.1.1.min.js"></script>
			<script src="//code.jquery.com/ui/1.10.4/jquery-ui.min.js"></script>
			<script src="//cdnjs.cloudflare.com/ajax/libs/underscore.js/1.6.0/underscore-min.js"></script>
			<script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>

			<script src="/OpenFEMA/js/jquery.tablesorter.min.js"></script>
			<script src="/OpenFEMA/js/ba-linkify.min.js"></script>

			<script src="/OpenFEMA/js/dataSetsTable.js"></script>
		<?php endif; ?>
	</head>
	<body data-baseurl="<?=base_url()?>">
		<div class="container" id="main">
			<ul class="breadcrumb">
				<li>
					<a class="newURL" href="<?=base_url()?>">Home</a>
				</li>
				<li class="active"><?=$dataSetInfo->name?></li>
			</ul>
			<h1>OpenFEMA API Explorer <small><?=$dataSetInfo->title?></small></h1>
			<div class="row">
				<span class="col-md-2">
					<button type="button" class="btn btn-primary" id="run">Run Query</button>
				</span>
				<span class="col-md-4">
					<label>
						Number of rows: <input type="number" min="1" max="100" id="numRows" value="100">
					</label>
				</span>
				<span class="col-md-4">
					<label>
						Offset: <input type="number" min="0" id="offset" value="0">
					</label>
				</span>
				<span class="col-md-2">
					<div class="checkbox">
						<label>
							<input type="checkbox" value="allpages" id="showCount">
							<strong class="small">Include Count?</strong>
						</label>
					</div>
				</span>
			</div>
			<table class="table" id="dataSetsFieldsTable" data-dataset="<?=$dataSetInfo->name?>">
				<thead>
					<tr>
						<th class="col-sm-2">Name</th>
						<th class="col-sm-8" data-sorter="false">Description</th>
						<th class="col-sm-2">
							<div class="checkbox">
								<label>
									<input type="checkbox" id="checkAll" checked>
									<strong class="small">Toggle All</strong>
								</label>
							</div>
						</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach($dataSetsFields as $set): ?>
						<tr>
							<td name="<?=$set->name?>"><?=$set->title?></td>
							<td class="fieldDescription"><?=$set->description?></td>
							<td>
								<div class="checkbox">
									<label>
										<input type="checkbox" class="field" value="<?=$set->name?>" checked>
										<strong class="small">Select in Query</strong>
									</label>
								</div>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<div>
				<h2>Filter Options</h2>
				<?=$filterFuncs?>
				<div class="row">
					<span class="col-md-6">
						<label>
							<?=form_dropdown('filterOptions', [-1=>'-- Select Field --']+$fieldNames, -1, 'id="filterOptions"')?>
						</label>
						<button type="button" id="addFilterOption" class="btn btn-primary"><i class="fa fa-plus"></i> Add Filter Option</button>
					</span>
					<span class="col-md-6">
						<ul id="filterOrder"></ul>
					</span>
				</div>
			</div>
			<div>
				<h2>Sort Options</h2>
				<div class="row">
					<span class="col-md-6">
						<label>
							<?=form_dropdown('sortOptions', [-1=>'-- Select Field --']+$fieldNames, -1, 'id="sortOptions"')?>
						</label>
						<button type="button" id="addSortOption" class="btn btn-primary"><i class="fa fa-plus"></i> Add Sort Option</button>
					</span>
					<span class="col-md-6">
						<ul id="sortOrder"></ul>
					</span>
				</div>
			</div>
		</div>

		<div id="results">
			<table class="table">
				<thead></thead>
				<tbody></tbody>
				<tfoot></tfoot>
			</table>
		</div>
	</body>
</html>
