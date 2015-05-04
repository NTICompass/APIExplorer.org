<!DOCTYPE html>
<html>
	<head>
		<title><?=$name?> Data Sets</title>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">

		<?php if($this->input->get('nojs') !== '1'): ?>
			<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">
			<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap-theme.min.css">
			<link rel="stylesheet" href="//netdna.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css">
			<link rel="stylesheet" href="//code.jquery.com/ui/1.10.4/themes/overcast/jquery-ui.css">
			<link rel="stylesheet" href="/css/dataSetsTable.css">

			<script src="//code.jquery.com/jquery-2.1.1.min.js"></script>
			<script src="//code.jquery.com/ui/1.10.4/jquery-ui.min.js"></script>
			<script src="//cdnjs.cloudflare.com/ajax/libs/underscore.js/1.6.0/underscore-min.js"></script>
			<script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>

			<script src="/js/jquery.tablesorter.min.js"></script>
			<script src="/js/ba-linkify.min.js"></script>

			<script src="/js/dataSetsTable.js"></script>
		<?php endif; ?>
	</head>
	<body data-baseurl="<?=base_url()?>">
		<div class="container" id="main">
			<ul class="breadcrumb">
				<li>
					<a class="newURL" href="<?=base_url()?>">Home</a>
				</li>
				<li class="active"><?=$name?></li>
			</ul>
			<div class="row">
				<img class="col-md-6 img-responsive" src="/images/<?=$logo?>">
				<span id="pageTitle" class="col-md-6">
					<h1><?=$name?> API Explorer <small>by Eric Siegel</small></h1>
				</span>
			</div>
			<table class="table table-hover" id="dataSetsTable">
				<thead>
					<tr>
						<th class="col-sm-3">Name</th>
						<th class="col-sm-9" data-sorter="false">Description</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach($dataSets as $set): ?>
						<tr>
							<td>
								<a class="newURL" href="<?=site_url('query/'.$api_id.'/dataSetFields/'.$set->name)?>"><?=$set->title?></a>
							</td>
							<td>
								<span class="smallDescription"><?=character_limiter($set->description, 150, '&hellip; <a class="small readMore" href="#">[read more]</a>')?></span>
								<span class="fullDescription hide"><?=$set->description?> <a class="small readLess" href="#">[hide]</a></span>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	</body>
</html>
