
	</div>
	<script>  
	    (function () {  
	        $('#create_pdf').on('click', async function () {  
	            $('body').scrollTop(0); 

                var fromDate = new Date();
                var today = new Date();
                
				fromDate.setMonth(fromDate.getMonth() - 1);
                fromDate = fromDate.getFullYear() + "-" + String(fromDate.getMonth() + 1).padStart(2, '0') + "-" + String(fromDate.getDate()).padStart(2, '0');
                today = today.getFullYear() + String(today.getMonth() + 1).padStart(2, '0') + String(today.getDate()).padStart(2, '0');

	            var dates = document.querySelectorAll('.pdf_date');
	            dates.forEach(element => {
	            	if(element.innerText <= fromDate) 
	            		$(element).parent().parent().parent().addClass('d-none');
				});


	        	const element = document.getElementsByClassName("content");
		        await html2pdf()
		          .from(element[0])
		          .save("hibernate-dashboard_" + today);

	            dates.forEach(element => {
	            	$(element).parent().parent().parent().removeClass('d-none');
				});
	        });  
            
	        $(".btn-show").on('click', function() {
	        	$(this).parent().parent().find("tr").removeClass("hidden");
	        });
	    }());  
        
        var myVar;

        function hideSpinner() {
          document.getElementById("loader").style.display = "none";
          document.getElementById("content").style.display = "block";
        }
        
        $("form").submit(function() {
          document.getElementById("loader").style.display = "block";
          document.getElementById("content").style.display = "none";
        }); 

	var utag_data = {
	};

	(function(a,b,c,d){
		a='https://tags.tiqcdn.com/utag/360bizvue/hibernatefund/prod/utag.js';
		b=document;c='script';d=b.createElement(c);d.src=a;d.type='text/java'+c;d.async=true;
		a=b.getElementsByTagName(c)[0];a.parentNode.insertBefore(d,a);
	})();

	</script>  
</body>
</html>