<div class="content">
	<div class="row mx-2">
	<?php if($backUrl != null) { ?>
		<div class="col-md-12">
			<h6 class="text-primary"><a href="#" onclick="history.back(-1); return false;"><i class="fa fa fa-long-arrow-left"></i> Back</a></h6>
		</div>
	<?php } ?>
		<div class="col-md-6">
			<h1 class="text-primary"><strong>Hiberblast</strong></h1>
			<h2 class="text-primary"><strong>Started on <span id="start_datetime"><?=$fdate?></span></strong></h2>
		</div>
		<div class="col-md-6 text-right">
			<div class="my-1"><button id="create_pdf" class="btn btn-sm btn-primary"> CONTACTS </button></div>
		</div>
	</div>
	<div class="row mx-2 my-5">
		<div class="col-md-6">
			<h1>$<span class="total_amount">0.00</span> <small>/ $<span><?=number_format($fundraising_goal, 2)?></span></small></h1>
		</div>
		<div class="col-md-6 text-right mt-3">
			<span class="text-danger"><span class="left_datetime">00h 00m 00s</span> Left</span>
		</div>
		<div class="col-md-12">
			<div class="progress">
				<div class="progress-bar progress-bar-striped progress-bar-animated" style="width:0%; border-radius: 30px;"></div>
			</div>
		</div>
	</div>
	<div class="row my-4 mx-2">
		<div class="col">
			<div class="tvalues py-4">
				<h5 class="left_datetime">00h 00m 00s</h5>
				<h6>Time Left</h6>
			</div>
		</div>
		<div class="col">
			<div class="tvalues py-4">
				<h5><span id="bed_count">0</span> / <span id="pillow_count">0</span></h5>
				<h6>Bed Sheets / Pillowcases</h6>
			</div>
		</div>
		<div class="col">
			<div class="tvalues py-4">
				<h5>$<span class="total_amount">0.00</span></h5>
				<h6>Total Raised</h6>
			</div>
		</div>
		<div class="col">
			<div class="tvalues py-4">
				<h5>$<span><?=number_format($fundraising_goal, 2)?></span></h5>
				<h6>Group Goal</h6>
			</div>
		</div>
	</div>
	<div class="row my-5 mx-2">
		<div class="col">
			<h5>Hiberblast Top Participants</h5>
			<table class="table table-striped table_data">
				<tbody id="tbody_participant_hiberblast">
					<tr>
						<td class="text-center">There is no data.</td>
					</tr>
				</tbody>
			</table>
		</div>
		<div class="col">
			<h5>Overall Top Participiants</h5>
			<table class="table table-striped table_data">
				<tbody id="tbody_participant_overall">
					<tr>
						<td class="text-center">There is no data.</td>
					</tr>
				</tbody>
			</table>
		</div>
		<div class="col">
			<h5>Recent Purchases</h5>
			<table class="table table-striped table_data">
				<tbody id="tbody_recent_purchase">
					<tr>
						<td class="text-center">There is no data.</td>
					</tr>
				</tbody>
			</table>
		</div>
		<div class="col">
			<h5>Subgroups</h5>
			<table class="table table-striped table_data">
				<tbody id="tbody_subgroups">
					<tr>
						<td class="text-center">There is no data.</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</div>

<script type="text/javascript">
	var baseUrl = "<?=base_url()?>";
	var userlogin = "<?=$user['user_login']?>";
	var date_from = "<?=$sdate?>";
	var fundraising_goal = "<?=$fundraising_goal?>";

	$(document).ready(function() {
		var currentdate = new Date();
		var time = 3600;

		onTimer();
		var timerID = setInterval(onTimer, 1000);

		function onTimer()
		{
			$(".left_datetime").text("0" + Math.floor(time/3600) + "h " + 
			(Math.floor((time % 3600) / 60) < 10 ? "0" : "") + Math.floor((time % 3600) / 60) + "m " + 
			(time % 60 < 10 ? "0" : "") + (time % 60) + "s");

			if(--time < 0)
				clearInterval(timerID);

			$.ajax({
				url: baseUrl + "index.php/index/group_hiberblast_data/" + userlogin, 
				data: {
					date_from : date_from
				},
				success: function(result){
					result = JSON.parse(result);

					if(fundraising_goal != 0)
						$(".progress-bar").css("width", result.odata.amount / fundraising_goal * 100 + "%");

			   		$("#bed_count").text(result.hdata.sheets_count);
					$("#pillow_count").text(result.hdata.pillows_count);
					$(".total_amount").text(result.odata.amount.toFixed(2));

					var data_list = result.hdata.participant_list;
                    
                    if(data_list.length > 0) {
                        $("#tbody_participant_hiberblast").empty();
                        for(var i = 0; i < data_list.length; i++)
                        {
                            var tr_str = "<tr>";
                            tr_str += '<td width="5%"><span>' + (i + 1) + '</span></td>';
                            tr_str += '<td>' + data_list[i]["display_name"] + '</td>';
                            tr_str += '<td class="text-right">$' + data_list[i]["amount"].toFixed(2) + '</td>';
                            tr_str += "</tr>";

                            $("#tbody_participant_hiberblast").append(tr_str);
                        }
                    }

					data_list = result.odata.participant_list;
                    
                    if(data_list.length > 0) {
                        $("#tbody_participant_overall").empty();
                        for(var i = 0; i < data_list.length; i++)
                        {
                            var tr_str = "<tr>";
                            tr_str += '<td width="5%"><span>' + (i + 1) + '</span></td>';
                            tr_str += '<td>' + data_list[i]["display_name"] + '</td>';
                            tr_str += '<td class="text-right">$' + data_list[i]["amount"].toFixed(2) + '</td>';
                            tr_str += "</tr>";

                            $("#tbody_participant_overall").append(tr_str);
                        }
                    }

                    data_list = result.hdata.order_list;
                    if(data_list.length > 0) {
                        $("#tbody_recent_purchase").empty();
                        for(var i = 0; i < data_list.length; i++)
                        {
                            var tr_str = "<tr>";
                            tr_str += '<td width="5%"><span>' + (i + 1) + '</span></td>';
                            tr_str += '<td>' + data_list[i]["customer_name"] + '</td>';
                            tr_str += '<td class="text-right">'
                            tr_str += '<p><strong>Bed Sheets:</strong>' + data_list[i]["sheets_count"] + '</p>';
                            tr_str += '<p><strong>Pillowcases:</strong>' + data_list[i]["pillows_count"] + '</p>';
                            tr_str += "</td></tr>";

                            $("#tbody_recent_purchase").append(tr_str);
                        }
                    }
				}
			});
		}
	});
</script>