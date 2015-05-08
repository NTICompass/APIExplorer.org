$(function(){
	var $main = $('#main'),
		baseURL = $('body').data('baseurl'),
		api_id = $('body').data('api'),
		fieldNames;

	function loadURL(url){
		$main.load(url+'#main', 'nojs=1', function(fullHTML){
			// I'd use jQuery's $.parseHTML, but it strips out the <body> tag
			// I tried $.parseXML, but <meta> isn't valid XML
			// $.parseXML uses DOMParser, but as text/xml, not text/html
			var parser = new DOMParser,
				fullDocument = parser.parseFromString(fullHTML, 'text/html');

			// TODO: Find a better way to get this data from the AJAX call
			$('body').data('api', api_id=$(fullDocument).find('body').data('api'));

			fixStuff();
			generateSQL();
		});
	}

	function fixStuff(){
		fieldNames = {};

		$('#dataSetsFieldsTable tr td[name]').each(function(){
			var $this = $(this);
			fieldNames[$this.attr('name')] = $(this).text();
		});

		$main.find('span.fullDescription,td.fieldDescription').html(function(i, html){
			return linkify(html);
		});

		$('#dataSetsTable,#dataSetsFieldsTable').tablesorter();

		$('#sortOrder,#filterOrder').sortable({
			handle: '.fa-arrows',
			stop: function(){
				generateSQL();
			}
		});

		$('#results').dialog({
			autoOpen: false,
			//modal: true,
			title: 'Query Results',
			//width: 'auto',
			//height: 'auto',
			open: function(){
				var $this = $(this);

				fixDialog($this);
			},
			buttons: {
				'Close': function(){
					$(this).dialog('close')
				}
			}
		});
	}

	function fixDialog($dialog){
		_.defer(function(){
			$dialog.css('maxHeight', window.innerHeight-300);
			$dialog.dialog('option', 'position', 'center');
		});
		$dialog.dialog('option', 'width', window.innerWidth-400);
		$dialog.dialog('widget').css('position', 'fixed');
	}

	function generateSQL(){
		var $sql = $('#generatedQuery');
		if($sql.length){
			var theQuery = $sql.data('sql').sql,
				vars = {
					fields: '*',
					query: '1',
					sort: '',
					offset: '',
					limit: ''
				};

			// SELECT
			if(!$('#checkAll').is(':checked')){
				vars.fields = $main.find(':checkbox.field:checked').map(function(){
					return this.value;
				}).get().join(', ');
			}

			// WHERE
			vars.query = $('#filterOrder li').map(function(){
				var $this = $(this),
					field = $this.data('filtername'),
					func = $this.find('select.filterFunc').val(),
					val = $this.find('input.filterText').val();

				if(func[0] === '!'){
					func = func.substring(1);
					return '"'+val+'" '+func+' '+field;
				}
				else{
					return field+' '+func+' "'+val+'"';
				}
			}).get().join("\nAND ");

			// ORDER BY
			vars.sort = $('#sortOrder li').map(function(){
				var $this = $(this);

				return $this.data('sortname')+' '+$this.find('select').val();
			}).get().join(', ');

			// OFFSET, LIMIT
			vars.offset = $('#offset').val();
			vars.limit = $('#numRows').val();

			// Replace the string with variables
			$.each(vars, function(key, value){
				theQuery = theQuery.replace(':'+key+':', value);
			});

			$sql.text(theQuery);
		}
	}

	$(window).on('popstate', function(e){
		loadURL(location.href);
	});

	$(window).resize(_.debounce(function(){
		var $dialog = $('#results');

		if($dialog.dialog('isOpen')){
			fixDialog($dialog);
		}
	}, 250));

	$main.on('click', 'a.newURL', function(e){
		e.preventDefault();

		// pushState pushes the current page (and the given object) into the history stack
		// and updates the location bar with the new URL
		history.pushState({}, '', this.href);
		// I then need to load that URL myself
		loadURL(this.href);
	});

	$main.on('click', 'a.readMore,a.readLess', function(e){
		var $this = $(this),
			$cell = $this.closest('td');

		e.preventDefault();

		$cell.find('span.smallDescription,span.fullDescription').toggleClass('hide');
	});

	$main.on('click', '#addFilterOption', function(){
		var $this = $(this),
			$filter = $('#filterOptions'),
			filterOption = $filter.val(),
			filterName, $select;

		if(filterOption !== '-1'){
			filterName = $filter.find('option[value="'+filterOption+'"]').text();

			if(!$this.data('select')){
				$this.data('select', '<select class="filterFunc">'+$('#filterFuncs_clone').remove().removeAttr('id').removeClass('hide').html()+'</select>');
			}

			$('#filterOrder').append('<li data-filtername="'+filterOption+'"><i class="moveRow fa fa-arrows"></i> '+filterName+' '+
				$this.data('select')+' <input type="text" class="filterText"> <i class="delFilter fa fa-times"></i></li>');
		}

		generateSQL();
	});

	$main.on('click', '#addSortOption', function(){
		var $sort = $('#sortOptions'),
			sortOption = $sort.val(),
			sortName, select;

		if(sortOption !== '-1'){
			sortName = $sort.find('option[value="'+sortOption+'"]').text();
			select = '<select><option value="asc">Ascending (A->Z)</option><option value="desc">Descending (Z->A)</option></select>';

			$('#sortOrder').append('<li data-sortname="'+sortOption+'"><i class="moveRow fa fa-arrows"></i> '+sortName+' '+
				select+' <i class="delSort fa fa-times"></i></li>');
		}

		generateSQL();
	});

	$main.on('click', '#sortOrder .delSort,#filterOrder .delFilter', function(){
		$(this).closest('li').remove();

		generateSQL();
	});

	$main.on('change', '#checkAll', function(){
		$main.find(':checkbox.field').prop('checked', this.checked);

		generateSQL();
	});

	$main.on('change', ':checkbox.field', function(){
		var totalFields = $main.find(':checkbox.field').length,
			checkedFields = $main.find(':checkbox.field:checked').length;

		$('#checkAll').prop('checked', checkedFields === totalFields);

		generateSQL();
	});

	$main.on('change', '#numRows,#offset,.filterFunc,.filterText', function(){
		generateSQL();
	});

	$main.on('click', '#run', function(){
		var fields = !$('#checkAll').prop('checked') ? $main.find(':checkbox.field:checked').map(function(){
				return this.value;
			}).get() : true,
			sort = $('#sortOrder li').map(function(){
				var $this = $(this);

				return $this.data('sortname')+' '+$this.find('select').val();
			}).get(),
			filters = $('#filterOrder li').map(function(){
				var $this = $(this);

				return {
					field: $this.data('filtername'),
					func: $this.find('select.filterFunc').val(),
					val: $this.find('input.filterText').val()
				};
			}).get();

		$.post(baseURL+'query/'+api_id+'/ajax_queryAPI', {
			dataSet: $('#dataSetsFieldsTable').data('dataset'),
			fields: fields,
			sort: _.values(sort),
			numRows: $('#numRows').val(),
			offset: $('#offset').val(),
			showCount: $('#showCount:checked').val(),
			filters: filters
		}, function(data){
			var meta = data.metadata,
				result = data[meta.entityname],
				showCount = $('#showCount').is(':checked'),
				count = meta.count,
				$result = $('#results'),
				$table = $result.find('table'),
				$thead = $table.find('thead').html('<tr></tr>'),
				$tbody = $table.find('tbody').empty(),
				$tfoot = $table.find('tfoot').empty(),
				$headTR, $currentTR,
				theFields = $.isArray(fields) ? fields : $main.find(':checkbox.field').map(function(){
					return this.value;
				}).get();

			$.each(result, function(index, value){
				$tbody.append('<tr></tr>');
				$currentTR = $tbody.find('tr:last');

				$headTR = $thead.find('tr');

				$.each(theFields, function(){
					if($headTR.children().length < theFields.length){
						$headTR.append('<th>'+fieldNames[this]+'</th>');
					}

					$currentTR.append('<td>'+value[this]+'</td>');
				});
			});

			if(showCount){
				$tfoot.append('<tr><td colspan="'+fields+'">'+count+' rows found ('+result.length+' rows shown)</td></tr>');
			}

			$result.dialog('open');
		}, 'json');
	});

	fixStuff();
	generateSQL();
});
