<div class="content">
	<div class="row mx-2">
	<?php if($backUrl != null) { ?>
		<div class="col-md-12">
			<h6 class="text-primary"><a href="#" onclick="history.back(-1); return false;"><i class="fa fa fa-long-arrow-left"></i> Back</a></h6>
		</div>
	<?php } ?>
		<div class="col-md-6">
			<h1 class="text-primary"><?=$user['display_name']?></h1>
			<h2 class="text-primary">Dashboard</h2>
		</div>
		<div class="col-md-6 text-right">
			<div class="my-1"><button id="create_pdf" class="btn btn-sm btn-primary"> Generate PDF </button></div>
			<div class="my-1">
				<form action="" method="post">
					<button class="btn btn-sm btn-primary pull-right"> Apply </button>
					<input class="form-control pull-right" type="text" name="dates" placeholder="Filter by date">
				</form>
			</div>
		</div>
	</div>
	<div class="row my-4 mx-2">
		<div class="col">
			<div class="tvalues py-4">
				<h5><?php echo count($group_list); ?></h5>
				<h6>Groups</h6>
			</div>
		</div>
		<div class="col">
			<div class="tvalues py-4">
				<h5><?=$sheets_count?> / <?=$pillows_count?></h5>
				<h6>Bed Sheets / Pillowcases</h6>
			</div>
		</div>
		<div class="col">
			<div class="tvalues py-4">
				<h5>$ <?=number_format($amount,2)?></h5>
				<h6>Total Raised</h6>
			</div>
		</div>
	</div>
	<div class="row my-5">
		<div class="col-md-6">
			<h6>Groups</h6>
			<table class="table table-striped table_data">
				<tbody id="tbody_group">
					<?php 
					if(count($group_list) > 0) {
						foreach ($group_list as $key => $group) { ?>
						<tr class="<?=($key > 9 ? 'hidden' : '')?>" data-id="<?=$group['user_login']?>">
							<td width="5%"><span><?=$key + 1?></span></td>
							<td><?=$group["group_name"]?></td>
							<td>
								<p><strong>Bed Sheets:</strong> <?=$group["sheets_count"]?></p>  
								<p><strong>Pillowcases:</strong> <?=$group["pillows_count"]?></p> 
							</td>
							<td class="text-right">$<?=number_format($group["amount"],2)?></td>
						</tr>
					<?php } } else { ?>
						<tr>
							<td class="text-center">There is no data.</td>
						</tr>
					<?php }?>
				</tbody>
			</table>
			<div class="text-center">
				<button class="btn btn-sm btn-outline-primary btn-show">SHOW ALL</button>
			</div>
		</div>
		<div class="col-md-6">
			<h6>Participants</h6>
			<table class="table table-striped table_data">
				<?php if(count($participant_list) > 0) { ?>
				<tbody id="tbody_aff">
					<?php 
						foreach ($participant_list as $key => $participant) { ?>
						<tr class="<?=($key > 9 ? 'hidden' : '')?>" data-id="<?=$participant['user_login']?>">
							<td width="5%"><span><?=$key + 1?></span></td>
							<td><?=$participant["display_name"]?></td>
							<td>
								<p><strong>Bed Sheets:</strong> <?=$participant["sheets_count"]?></p>
								<p><strong>Pillowcases:</strong> <?=$participant["pillows_count"]?></p>
							</td>
							<td class="text-right">$<?=number_format($participant["amount"],2)?></td>
						</tr>
					<?php } } else { ?>
						<tr>
							<td class="text-center">There is no data.</td>
						</tr>
				<?php } ?>
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

		$("#tbody_aff tr").on("click", function() {
			if($(this).attr('data-id') != undefined)
				document.location.href = baseUrl + "index.php/index/participant_dashboard/" + $(this).attr("data-id");
		});
	});
</script>