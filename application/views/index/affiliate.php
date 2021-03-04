<style type="text/css">
    .multiple_emails-container { 
        border:1px #00ff00 solid; 
        border-radius: 1px; 
        box-shadow: inset 0 1px 1px rgba(0,0,0,.075); 
        padding:0; margin: 0; cursor:text; width:100%; 
    }

    .multiple_emails-container input { 

        width:100%; 
        border:0; 
        outline: none; 
        margin-bottom:30px; 
        padding-left: 5px; 
        
    }

    ,,.multiple_emails-container input{
        border: 0 !important;
    }

    .multiple_emails-container input.multiple_emails-error {    
        box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075), 0 0 6px red !important; 
        outline: thin auto red !important; 
    }

    .multiple_emails-container ul { 
        list-style-type:none; 
        padding-left: 0; 
    }

    .multiple_emails-email { 
        margin: 3px 5px 3px 5px; 
        padding: 3px 5px 3px 5px; 
        border:1px #BBD8FB solid;   
        border-radius: 3px; 
        background: #F3F7FD; 
    }

    .multiple_emails-close { 
        float:left; 
        margin:0 3px;
    }

</style>
<div class="content">
    <div class="row mx-2">
        <?php if ($backUrl != null) {?>
        <div class="col-md-12">
            <h6 class="text-primary"><a href="#" onclick="history.back(-1); return false;"><i class="fa fa fa-long-arrow-left"></i> Back</a></h6>
        </div>
        <?php }?>
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
                <h5><?=$sheets_count?> / <?=$pillows_count?></h5>
                <h6>Bed Sheets / Pillowcases</h6>
            </div>
        </div>
        <div class="col">
            <div class="tvalues py-4">
                <h5>$ <?=number_format($amount, 2)?></h5>
                <h6>Total Raised</h6>
            </div>
        </div>
        <div class="col">
            <div class="tvalues py-4">
                <h5><?=number_format(count($contact_list))?></h5>
                <h6>Total Contacts</h6>
            </div>
        </div>
    </div>
    <div class="row my-5 mx-2">
        <div class="col-md-6">
            <h5>Orders</h5>
            <table class="table table-striped table_data">
                <tbody id="tbody_group">
                    <?php
if (count($order_list) > 0) {
    foreach ($order_list as $key => $order) {?>
                    <tr class="<?=($key > 9 ? 'hidden' : '')?>" data-sale="<?=$key?>">
                        <td><span><?=$key + 1?></span></td>
                        <td>
                            <p><strong><?=$order["customer_name"] == null ? "UNKNOWN" : $order["customer_name"]?></strong></p>
                            <p>Customer</p>
                        </td>
                        <td>
                            <p><strong>Bed Sheets:</strong> <?=$order["sheets_count"]?></p>
                            <p><strong>Pillowcases:</strong> <?=$order["pillows_count"]?></p>
                        </td>
                        <td>
                            <p><strong><?=substr($order["date"], 0, 10)?></strong></p>
                            <p>Date</p>
                        </td>
                        <td class="text-right">$<?=number_format($order["amount"], 2)?></td>
                    </tr>
                    <?php }} else {?>
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
            <h5>Contacts
                <a href="#" class="btn btn-sm btn-primary pull-right mr-1" data-toggle="modal" data-target="#myModal">Add Contact</a>
            </h5>
            <table class="table table-striped table_data">
                <tbody id="tbody_group">
                    <?php
if (count($contact_list) > 0) {
    foreach ($contact_list as $key => $order) {?>
                    <tr class="<?=($key > 9 ? 'hidden' : '')?>" data-sale="<?=$key?>">
                        <td><span><?=$key + 1?></span></td>
                        <td>
                            <p><strong><?=$order["email"]?></strong></p>
                            <p>Email</p>
                        </td>
                        <td>
                            <p><strong><?=substr($order["created_date"], 0, 10)?></strong></p>
                            <p>Registered Date</p>
                        </td>
                    </tr>
                    <?php }} else {?>
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
    </div>
</div>
<div class="modal" id="myModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Add Contact</h4>
                <button type="button" class="close" data-dismiss="modal" style="background-color: transparent!important;">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="contact_email">Email : </label>
                    <input type="text" id="example_emailBS" name="example_emailBS" class="form-control" value='[]'>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="btn_save">Send</button>
                <button type="button" class="btn btn-default" data-dismiss="modal" style="background-color: #898989!important;">Close</button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
	var baseUrl = "<?=base_url()?>";
	var userlogin = "<?=$user['user_login']?>";

    (function( $ ){
 
        $.fn.multiple_emails = function(options) {
            
            // Default options
            var defaults = {
                checkDupEmail: true,
                theme: "Bootstrap",
                position: "top"
            };
            
            // Merge send options with defaults
            var settings = $.extend( {}, defaults, options );
            
            var deleteIconHTML = "";
            if (settings.theme.toLowerCase() == "Bootstrap".toLowerCase())
            {
                deleteIconHTML = '<a href="#" class="multiple_emails-close" title="Remove"><i class="fa fa-times"></i></a>';
            }
            else if (settings.theme.toLowerCase() == "SemanticUI".toLowerCase() || settings.theme.toLowerCase() == "Semantic-UI".toLowerCase() || settings.theme.toLowerCase() == "Semantic UI".toLowerCase()) {
                deleteIconHTML = '<a href="#" class="multiple_emails-close" title="Remove"><i class="remove icon"></i></a>';
            }
            else if (settings.theme.toLowerCase() == "Basic".toLowerCase()) {
                //Default which you should use if you don't use Bootstrap, SemanticUI, or other CSS frameworks
                deleteIconHTML = '<a href="#" class="multiple_emails-close" title="Remove"><i class="basicdeleteicon">Remove</i></a>';
            }
            
            return this.each(function() {
                //$orig refers to the input HTML node
                var $orig = $(this);
                var $list = $('<ul class="multiple_emails-ul" />'); // create html elements - list of email addresses as unordered list

                if ($(this).val() != '' && IsJsonString($(this).val())) {
                    $.each(jQuery.parseJSON($(this).val()), function( index, val ) {
                        $list.append($('<li class="multiple_emails-email"><span class="email_name" data-email="' + val.toLowerCase() + '">' + val + '</span></li>')
                          .prepend($(deleteIconHTML)
                               .click(function(e) { $(this).parent().remove(); refresh_emails(); e.preventDefault(); })
                          )
                        );
                    });
                }
                
                var $input = $('<input type="text" class="multiple_emails-input text-left" />').on('keyup', function(e) { // input
                    $(this).removeClass('multiple_emails-error');
                    var input_length = $(this).val().length;
                    
                    var keynum;
                    if(window.event){ // IE                 
                        keynum = e.keyCode;
                    }
                    else if(e.which){ // Netscape/Firefox/Opera                 
                        keynum = e.which;
                    }
                    
                    //if(event.which == 8 && input_length == 0) { $list.find('li').last().remove(); } //Removes last item on backspace with no input
                    
                    // Supported key press is tab, enter, space or comma, there is no support for semi-colon since the keyCode differs in various browsers
                    if(keynum == 9 || keynum == 32 || keynum == 188) { 
                        display_email($(this), settings.checkDupEmail);
                    }
                    else if (keynum == 13) {
                        display_email($(this), settings.checkDupEmail);
                        //Prevents enter key default
                        //This is to prevent the form from submitting with  the submit button
                        //when you press enter in the email textbox
                        e.preventDefault();
                    }

                }).on('blur', function(event){ 
                    if ($(this).val() != '') { display_email($(this), settings.checkDupEmail); }
                });

                var $container = $('<div class="multiple_emails-container" />').click(function() { $input.focus(); } ); // container div
     
                // insert elements into DOM
                if (settings.position.toLowerCase() === "top")
                    $container.append($list).append($input).insertAfter($(this));
                else
                    $container.append($input).append($list).insertBefore($(this));

                /*
                t is the text input device.
                Value of the input could be a long line of copy-pasted emails, not just a single email.
                As such, the string is tokenized, with each token validated individually.
                
                If the dupEmailCheck variable is set to true, scans for duplicate emails, and invalidates input if found.
                Otherwise allows emails to have duplicated values if false.
                */
                function display_email(t, dupEmailCheck) {
                    
                    //Remove space, comma and semi-colon from beginning and end of string
                    //Does not remove inside the string as the email will need to be tokenized using space, comma and semi-colon
                    var arr = t.val().trim().replace(/^,|,$/g , '').replace(/^;|;$/g , '');
                    //Remove the double quote
                    arr = arr.replace(/"/g,"");
                    //Split the string into an array, with the space, comma, and semi-colon as the separator
                    arr = arr.split(/[\s,;]+/);
                    
                    var errorEmails = new Array(); //New array to contain the errors
                    
                    var pattern = new RegExp(/^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i);
                    
                    for (var i = 0; i < arr.length; i++) {
                        //Check if the email is already added, only if dupEmailCheck is set to true
                        if ( dupEmailCheck === true && $orig.val().indexOf(arr[i]) != -1 ) {
                            if (arr[i] && arr[i].length > 0) {
                                new function () {
                                    var existingElement = $list.find('.email_name[data-email=' + arr[i].toLowerCase().replace('.', '\\.').replace('@', '\\@') + ']');
                                    existingElement.css('font-weight', 'bold');
                                    setTimeout(function() { existingElement.css('font-weight', ''); }, 1500);
                                }(); // Use a IIFE function to create a new scope so existingElement won't be overriden
                            }
                        }
                        else if (pattern.test(arr[i]) == true) {
                            $list.append($('<li class="multiple_emails-email"><span class="email_name" data-email="' + arr[i].toLowerCase() + '">' + arr[i] + '</span></li>')
                                  .prepend($(deleteIconHTML)
                                       .click(function(e) { $(this).parent().remove(); refresh_emails(); e.preventDefault(); })
                                  )
                            );
                        }
                        else
                            errorEmails.push(arr[i]);
                    }
                    // If erroneous emails found, or if duplicate email found
                    if(errorEmails.length > 0)
                        t.val(errorEmails.join("; ")).addClass('multiple_emails-error');
                    else
                        t.val("");
                    refresh_emails ();
                }
                
                function refresh_emails () {
                    var emails = new Array();
                    var container = $orig.siblings('.multiple_emails-container');
                    container.find('.multiple_emails-email span.email_name').each(function() { emails.push($(this).html()); });
                    $orig.val(JSON.stringify(emails)).trigger('change');
                }
                
                function IsJsonString(str) {
                    try { JSON.parse(str); }
                    catch (e) { return false; }
                    return true;
                }
                
                return $(this).hide();
     
            });
            
        };
        
    })(jQuery);

    $(document).ready(function() {
    	$('input[name="dates"]').daterangepicker({
    		autoUpdateInput: false,
    		locale: {
    		  	cancelLabel: 'Clear'
    		}
        });

        $('#example_emailBS').multiple_emails({position: "top"});

        $('input[name="dates"]').on('apply.daterangepicker', function(ev, picker) {
    		$(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));
    	});

    	$('input[name="dates"]').on('cancel.daterangepicker', function(ev, picker) {
    		$(this).val('');
    	});

		$("#btn_save").on('click', function(event)
		{
            var emails = $('#example_emailBS').val();

			if(JSON.parse(emails).length == 0) {
				alert("Please input email.");
				return;
			}

			if(confirm("Are you sure to register these contacts?")) {
                document.getElementById("loader").style.display = "block";
                document.getElementById("content").style.display = "none";

				$.ajax({
					url: baseUrl + "index.php/index/add_contact/" + userlogin,
					data: {
						emails : emails
					},
					success: function(result){
						result = JSON.parse(result);

						if(result.status == "success")
							document.location.href = document.location.href;
						else
							alert(result.error);
					}
				});
			}
		});
    });
</script>