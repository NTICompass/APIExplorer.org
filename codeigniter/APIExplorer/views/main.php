<!DOCTYPE html>
<html>
	<head>
		<title>API Explorer - beta</title>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">

		<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">
		<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap-theme.min.css">
		<link rel="stylesheet" href="//netdna.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css">
		<link rel="stylesheet" href="//code.jquery.com/ui/1.10.4/themes/overcast/jquery-ui.css">
		<link rel="stylesheet" href="/css/homePage.css">

		<script src="//code.jquery.com/jquery-2.1.1.min.js"></script>
		<script src="//code.jquery.com/ui/1.10.4/jquery-ui.min.js"></script>
		<script src="//cdnjs.cloudflare.com/ajax/libs/underscore.js/1.6.0/underscore-min.js"></script>
		<script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>

		<script src="/js/jquery.tablesorter.min.js"></script>
		<script src="/js/ba-linkify.min.js"></script>

		<script src="/js/homePage.js"></script>
	</head>
	<body data-baseurl="<?=base_url()?>">
		<span id="forkongithub">
			<a href="https://github.com/NTICompass/OpenFEMA-API-Explorer">Fork me on GitHub</a>
		</span>
		<div class="container">
			<div class="jumbotron">
				<h1>Welcome to APIExplorer.org <small>beta</small></h1>
				<p>Please select an API to begin querying!</p>
			</div>
			<table class="table table-hover" id="available_apis">
				<thead>
					<tr>
						<th class="col-sm-3">Name</th>
						<th class="col-sm-9" data-sorter="false">Description</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach($apis as $api): ?>
						<tr>
							<td>
								<a class="newURL" href="<?=site_url('query/'.$api->api_id)?>"><?=$api->name?></a>
								<?php if(isset($api->siteType) && $api->siteType === 'beta'): ?>
									<span class="badge">beta</span>
								<?php endif; ?>
							</td>
							<td>
								<span class="smallDescription"><?=character_limiter($api->description, 150, '&hellip; <a class="small readMore" href="#">[read more]</a>')?></span>
								<span class="fullDescription hide"><?=nl2br($api->description)?><br/><a class="small readLess" href="#">[hide]</a></span>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	</body>
</html>
