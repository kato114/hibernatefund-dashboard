<div class="content">
	<div class="row mx-2">
		<div class="col-md-6">
			<h1 class="text-primary"> <?=$user['display_name']?> </h1>
			<h2 class="text-primary"> Dashboard </h2>
		</div>
		<div class="col-md-6 text-right">
			<div class="my-1"><button id="create_pdf" class="btn btn-sm btn-primary"> Generate PDF </button></div>
			<div class="my-1">
				<form action="" method="post">
					<button class="btn btn-sm btn-primary pull-right"> Apply </button>
					<input class="form-control pull-right" type="text" name="dates" placeholder="Filter by date" value="<?=$date_filter?>">
				</form>
			</div>
		</div>
	</div>
	<div class="row my-4 mx-2">
		<div class="col">
			<div class="tvalues py-4">
				<h5 class="mb-3"><?=$owner_count; ?></h5>
				<h6>Owners</h6>
			</div>
		</div>
		<div class="col">
			<div class="tvalues py-4">
				<h5 class="mb-3"><?php echo count($group_list); ?></h5>
				<h6>Groups</h6>
			</div>
		</div>
		<div class="col">
			<div class="tvalues py-4">
				<h5 class="mb-3"><?=$sheets_count?> / <?=$pillows_count?></h5>
				<h6>Bed Sheets / Pillowcases</h6>
			</div>
		</div>
		<div class="col">
			<div class="tvalues py-4">
				<h5 class="mb-3">$ <?=number_format($amount,2)?></h5>
				<h6>Total Raised</h6>
			</div>
		</div>
	</div>
	<div class="row my-5 mx-2">
		<div class="col-md-12">
			<h6>Groups
				<button id="export" class="btn btn-sm btn-primary float-right">Export CSV</button></h6>
			<table id="exportMe" class="table table-striped table_data">
				<thead>
					<tr>
						<th>GROUP ID</th>
						<th>GROUP</th>
						<th>GROUP Total</th>
						<th>Rep Username</th>
						<th>Owner Username</th>
						<th>Bedsheets</th>
						<th>Extra Pillowcases</th>
					</tr>
				</thead>
				<tbody id="tbody_group">
					<?php if(count($group_list) > 0) { 
						foreach ($group_list as $key => $group) { ?>
						<tr class="<?=($key > 9 ? 'hidden' : '')?>" data-id="<?=$group["user_login"]?>">
							<td><?=$group["affiliate_id"]?></td>
							<td><?=$group["group_name"]?></td>
							<td>$<?=number_format($group["amount"],2)?></td>
							<td><?=$group["rep_name"]?></td>
							<td><?=$group["owner_name"]?></td>
							<td class="text-right"><?=$group["sheets_count"]?></td>
							<td class="text-right"><?=$group["pillows_count"]?></td>
						</tr>
					<?php } } else { ?>
						<tr>
							<td class="text-center">There is no data.</td>
						</tr>
					<?php } ?>
				</tbody>
			</table>
			<div class="text-center">
				<button class="btn btn-sm btn-outline-primary btn-show">SHOW ALL</button>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
	var baseUrl = "<?=base_url()?>";
	$(document).ready(function() {
		$('input[name="dates"]').daterangepicker({
			autoUpdateInput: false,
			locale: {
			  	cancelLabel: 'Clear'
			}
		});

		$('input[name="dates"]').on('apply.daterangepicker', function(ev, picker) {
			$(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));
		});

		$('input[name="dates"]').on('cancel.daterangepicker', function(ev, picker) {
			$(this).val('');
		});

		$("#tbody_group tr").on("click", function() {
			if($(this).attr('data-id') != undefined)
				document.location.href = baseUrl + "index.php/index/group_dashboard/" + $(this).attr("data-id");
		});
        
		const toCsv = function(table) {
		    // Query all rows
		    const rows = table.querySelectorAll('tr');

		    return [].slice.call(rows)
		        .map(function(row) {
		            // Query all cells
		            const cells = row.querySelectorAll('th,td');
		            return [].slice.call(cells)
		                .map(function(cell) {
		                    return cell.textContent.replace(/,/gi, "");;
		                })
		                .join(',');
		        })
		        .join('\n');
		};

		const download = function(text, fileName) {
		    const link = document.createElement('a');
		    link.setAttribute('href', `data:text/csv;charset=utf-8,${encodeURIComponent(text)}`);
		    link.setAttribute('download', fileName);

		    link.style.display = 'none';
		    document.body.appendChild(link);

		    link.click();

		    document.body.removeChild(link);
		};

		const table = document.getElementById('exportMe');
		const exportBtn = document.getElementById('export');

		exportBtn.addEventListener('click', function() {
		    const csv = toCsv(table);
		    download(csv, 'download.csv');
		});
	});
</script>