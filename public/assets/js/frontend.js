$(document).ready(function(){
	/**
	 * @DateOfCreation        11 June 2018
	 * @ShortDescription      This function is responsible for save degree record
	 * @return                Array of response in table body
	 */
	$( "#degrees" ).submit(function( e ) {
		if($(this).parsley().validate()){	
		  	e.preventDefault();
		  	var formUrl = this.action;
		  	$.ajax({
			    type: "POST",
			    dataType: "json",
			    url: formUrl,
			    data: $(this).serializeArray(),
			    success: function(response) {
			    	if(response.error != ''){
			    		var errorResponse = getFirstErrorMessage(response.error);
			    		$('.response-message').text(errorResponse).removeClass('hide').addClass('alert-danger');
			    	} else {
			    		$('.response-message').text('').removeClass('alert-danger').addClass('hide');

				    	$("#degrees")[0].reset();
			    		getDegreeList();		    	
			    	}
			    }
			})
		}
	});

	/**
	 * @DateOfCreation        11 June 2018
	 * @ShortDescription      This function is responsible to get the Degree list
	 * @return                Array of response in table body
	 */
	$("#next-degree").click(function(e){
		$('.response-message').text('').removeClass('alert-success').addClass('hide');
		getDegreeList();
	});

	/**
	 * @DateOfCreation        11 June 2018
	 * @ShortDescription      This function is responsible to get the Membership list
	 * @return                Array of response in table body
	 */
	$("#next-membership").click(function(e){
		$('.response-message').text('').removeClass('alert-success').addClass('hide');
		getMembershipList();
	});

	/**
	 * @DateOfCreation        11 June 2018
	 * @ShortDescription      This function is responsible for save membership record
	 * @return                Array of response in table body
	 */
	$( "#membership_of_socity" ).submit(function( e ) {
		if($(this).parsley().validate()){	
		  	e.preventDefault();
		  	var formUrl = this.action;
		  	$.ajax({
			    type: "POST",
			    dataType: "json",
			    url: formUrl,
			    data: $(this).serializeArray(),
			    success: function(response) {
			    	if(response.error != ''){
			    		var errorResponse = getFirstErrorMessage(response.error);
			    		$('.response-message').text(errorResponse).removeClass('hide').addClass('alert-danger');
			    	} else {
			    		$('.response-message').text('').removeClass('alert-danger').addClass('hide');

				    	$("#membership_of_socity")[0].reset();
			    		getMembershipList();		    	
			    	}
			    }
			})
		}
	});

	/**
	 * @DateOfCreation        11 June 2018
	 * @ShortDescription      This function is responsible to get the Membership list
	 * @return                Array of response in table body
	 */
	$("#next-job-profile").click(function(e){
		$('.response-message').text('').removeClass('alert-success').addClass('hide');
		getJobProfileList();
	});

	/**
	 * @DateOfCreation        11 June 2018
	 * @ShortDescription      This function is responsible for save JOB Profile Records
	 * @return                Array of response in table body
	 */
	$( "#job_profile" ).submit(function( e ) {
		if($(this).parsley().validate()){	
		  	e.preventDefault();
		  	var formUrl = this.action;
		  	$.ajax({
			    type: "POST",
			    dataType: "json",
			    url: formUrl,
			    data: $(this).serializeArray(),
			    success: function(response) {
			    	if(response.error != ''){
			    		var errorResponse = getFirstErrorMessage(response.error);
			    		$('.response-message').text(errorResponse).removeClass('hide').addClass('alert-danger');
			    	} else {
			    		$('.response-message').text('').removeClass('alert-danger').addClass('hide');

				    	$("#job_profile")[0].reset();
				    	getJobProfileList();		    		
			    	}
			    }
			})
		}
	});

	/**
	 * @DateOfCreation        11 June 2018
	 * @ShortDescription      This function is responsible for get state list
	 * @return                state list convert to select option
	 */

	$(document).on('change','#country_id',function( e ) {
	  if($(this).val()!= '' && $(this).val() != null){
		  	var formUrl = BASE_URL+'/get-states';
		  	$.ajax({
			    type: "POST",
			    dataType: "json",
			    url: formUrl,
			    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
			    data: {country_id:$(this).val(),_token:$('meta[name="csrf-token"]').attr('content')},

			    success: function(response) {
			    	$('#state_id').empty();
			    	 $('#state_id').append($("<option/>", {
				        value: '',
				        text: 'Select'
					    }));
			    	$.each(response.result, function(key, value) {
					    $('#state_id').append($("<option/>", {
				        value: value.id,
				        text: value.name
					    }));
						});
			    }
			});
	  }
	});


	/**
	 * @DateOfCreation        11 June 2018
	 * @ShortDescription      This function is responsible for get city list
	 * @return                city list convert to select option
	 */
	$(document).on('change','#state_id',function( e ) {
	  if($(this).val()!= '' && $(this).val() != null){
		  	var formUrl = BASE_URL+'/get-city';
		  	$.ajax({
			    type: "POST",
			    dataType: "json",
			    url: formUrl,
			    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
			    data: {state_id:$(this).val(),_token:$('meta[name="csrf-token"]').attr('content')},

			    success: function(response) {
			    	$('#city_id').empty();
			    	 $('#city_id').append($("<option/>", {
				        value: '',
				        text: 'Select'
					    }));
			    	$.each(response.result, function(key, value) {
					    $('#city_id').append($("<option/>", {
				        value: value.id,
				        text: value.name
					    }));
						});
			    }
			});
	  }
	});

	/**
	 * @DateOfCreation        11 June 2018
	 * @ShortDescription      This function is responsible for save Doctor Profile Records
	 * @return                Array of response in table body
	 */
	$(document).on('click','#basic_info_save',function(e){	
		
		if($('#basic_info_form').parsley().validate()){	
			$('#basic_info_save').prop('disabled',true).text(lang.please_wait_text);
			var form = $("#basic_info_form");

		    // you can't pass Jquery form it has to be javascript form object
		    var formData = new FormData(form[0]);
		  	var formUrl = BASE_URL+'/doctor/registration';

		  	$.ajax({
			    type: "POST",
			    cache:false,
	            contentType: false,
	            processData: false,
			    url: formUrl,
			    data: formData,
			    success: function(response) {
			    	if(response.code == '5000'){
			    			$('#basic_info_save').prop('disabled',false).text(BASIC_INFO_SAVE_TEXT);
			    		value = getFirstErrorMessage(response.error);
			    		$('.basic-info-message').text(value).removeClass('hide').removeClass('alert-success').addClass('alert-danger');
			    	}else if(response.code == '1000') {
						$('.basic-info-message').text(response.message).removeClass('hide').removeClass('alert-danger').addClass('alert-success');
						$('.user_id_token').val(response.result.user_id);
						$('#basic_info_save').remove();
						$('#next-job-profile').removeClass('hide').trigger('click');
			    	}else if(response.code == '3000') {
			    		$('#basic_info_save').prop('disabled',false).text(BASIC_INFO_SAVE_TEXT);
			    		$('.basic-info-message').text(response.message).removeClass('hide').removeClass('alert-success').addClass('alert-danger');
			    	}else{
			    		$('#basic_info_save').prop('disabled',false).text(BASIC_INFO_SAVE_TEXT);
			    	}
			    	$('html, body').animate({
							scrollTop: $("#basic_info_form").offset().top
						}, 1000);
			    }
			})
		}
	});
	/**
	 * @DateOfCreation        11 June 2018
	 * @ShortDescription      This function is responsible for manage select other city option
	 * @return                Array of response in table body
	 */
	$(document).on('change','#city_id',function(){
		var cityIdOther = $('#city_id option').filter(function () { 
     return this.text.toLowerCase() == OTHER_CITY_TEXT.toLowerCase(); 
     } ).attr('value');
		if($(this).val() == cityIdOther){
			$('#doc_other_city').prop('required',true);
			$('.city_other').removeClass('hide');
		}else{
			$('#doc_other_city').prop('required',false);
			$('.city_other').addClass('hide');
		}
	});
	
	// By default India country selected
	var countryId = $('#country_id option').filter(function () { 
    	return this.text.toLowerCase() == DEFAULT_COUNTRY_NAME_SELECTED.toLowerCase(); 
    } ).attr('value');
	$('#country_id').val(countryId).change();

	/**
	 * @DateOfCreation        11 June 2018
	 * @ShortDescription      This function is responsible hide form and show welcome message on screen
	 * @return                Array of response in table body
	 */
	$("#complete-registration").click(function(e){
		$('.form-container').addClass('hide');
		$('.welcome-message').removeClass('hide');
	});

	/**
	 * @DateOfCreation        14 June 2018
	 * @ShortDescription      This function is responsible for reset password functionality
	 * @return                Array of response in table body
	 */
	$( "#reset_password" ).submit(function( e ) {
		if($(this).parsley().validate()){	
		  	e.preventDefault();
		  	var formUrl = this.action;
		  	$.ajax({
			    type: "POST",
			    dataType: "json",
			    url: formUrl,
			    data: $(this).serializeArray(),
			    success: function(response) {
			    	if(response.error != ''){
			    		var errorResponse = getFirstErrorMessage(response.error);
			    		$('.response-message').text('').removeClass('alert-success');
			    		$('.response-message').text(errorResponse).removeClass('hide').addClass('alert-danger');
			    	} else {
			    		$('.response-message').text('').removeClass('alert-danger');
			    		$('.response-message').text(response.message).removeClass('hide').addClass('alert-success');

				    	$("#reset_password")[0].reset();
			    	}
			    }
			})
		}
	});
});

	/**
	 * @DateOfCreation        11 June 2018
	 * @ShortDescription      This function is responsible to get the Degree list
	 * @return                Array of response in table body
	 */
	function getDegreeList(){
		var user_id = $('.user_id_token').val();
	  	var getUrl = BASE_URL+'/get-degrees/'+user_id;

	  	var tableContent = '';
	  	$.get(getUrl, function(data, status){
	  		if(data.result.length > 0)
	  		{
		  		$.each(data.result, function(i) {
		  			var delete_url  = BASE_URL+'/delete-degree/'+data.result[i].doc_deg_id;
		  			var confMessage = lang.confirmDelete;
		  			
		  			tableContent += '<tr>';
		                tableContent +='<td>'+data.result[i].doc_deg_name+'</td>';
		                tableContent +='<td>'+data.result[i].doc_deg_passing_year+'</td>';
		                tableContent +='<td>'+data.result[i].doc_deg_institute+'</td>';
		                tableContent +='<td class="text-center"><a href="javascript:void(0)" class="btn red" onclick="delete_degree(\''+data.result[i].doc_deg_id+'\', \''+confMessage.replace("??", "degree")+'\');">Delete</a></td>';
		            tableContent +='</tr>';
		        });
		  	} else { 
		  		tableContent += '<tr>';
	                tableContent +='<td colspan="4">'+lang.no_record_found+'</td>';
	            tableContent +='</tr>';
		  	}

			$("#degree-table tbody").html(tableContent);
	    });
	}

	/**
	 * @DateOfCreation        11 June 2018
	 * @ShortDescription      This function is responsible for delete Degree by id
	 * @return                Array of response in table body
	 */
	function delete_degree(degreeId, confrimMessage){
		var deleteUrl = BASE_URL+'/delete-degree/'+degreeId;

		if(confirm(confrimMessage)){
		  	$.ajax({
			    type: "DELETE",
			    url: deleteUrl,
			    data: {_token:$('meta[name="csrf-token"]').attr('content')},
			    success: function(response) {
			    	$('.response-message').text(response.message).removeClass('hide').addClass('alert-success');
			    	getDegreeList();
			    }
			});
		}else{
			return false;
		}
	}

	/**
	 * @DateOfCreation        11 June 2018
	 * @ShortDescription      This function is responsible to get the Membership list by ajax post
	 * @return                Array of response in table body
	 */
	function getMembershipList(){
		var user_id = $('.user_id_token').val();
	  	var getUrl = BASE_URL+'/get-membership/'+user_id;

	  	var tableContent = '';
	  	$.get(getUrl, function(data, status){
	  		
	  		if(data.result.length > 0)
	  		{
		  		$.each(data.result, function(i) {
		  			var confMessage = lang.confirmDelete;

		  			var membershipNumber = 'NA';
		  			if(data.result[i].doc_mem_no != '' && data.result[i].doc_mem_no !== null){
		  				membershipNumber = data.result[i].doc_mem_no;
		  			}

		  			var membershipYear = 'NA';
		  			if(data.result[i].doc_mem_year != '' && data.result[i].doc_mem_year !== null){
		  				membershipYear = data.result[i].doc_mem_year;
		  			}

		  			tableContent += '<tr>';
		                tableContent +='<td>'+data.result[i].doc_mem_name+'</td>';
		                tableContent +='<td>'+membershipNumber+'</td>';
		                tableContent +='<td>'+membershipYear+'</td>';
		                tableContent +='<td class="text-center"><a href="javascript:void(0)" class="btn red" onclick="delete_membership(\''+data.result[i].doc_mem_id+'\', \''+confMessage.replace("??", "membership")+'\');">Delete</a></td>';
		            tableContent +='</tr>';
		        });
		    } else { 
		  		tableContent += '<tr>';
	                tableContent +='<td colspan="4">'+lang.no_record_found+'</td>';
	            tableContent +='</tr>';
		  	}

			$("#membership-table tbody").html(tableContent);
	    });
	}

	/**
	 * @DateOfCreation        11 June 2018
	 * @ShortDescription      This function is responsible for delete Degree by id
	 * @return                Array of response in table body
	 */
	function delete_membership(membershipId, confrimMessage){
		var deleteUrl = BASE_URL+'/delete-membership/'+membershipId;

		if(confirm(confrimMessage)){
		  	$.ajax({
			    type: "DELETE",
			    url: deleteUrl,
			    data: {_token:$('meta[name="csrf-token"]').attr('content')},
			    success: function(response) {
			    	$('.response-message').text(response.message).removeClass('hide').addClass('alert-success');
			    	getMembershipList();
			    }
			});
		}else{
			return false;
		}
	}

	/**
	 * @DateOfCreation        11 June 2018
	 * @ShortDescription      This function is responsible to get the JOB Profile list by ajax post
	 * @return                Array of response in table body
	 */
	function getJobProfileList(){
		var user_id = $('.user_id_token').val();
	  	var getUrl = BASE_URL+'/get-job-profiles/'+user_id;

	  	var tableContent = '';
	  	$.get(getUrl, function(data, status)
	  	{  		
	  		var confMessage = lang.confirmDelete;

	  		if(data.result.length > 0)
	  		{
		  		$.each(data.result, function(i) {
		  			console.log(data.result[i].doc_exp_organisation_type);
		  			var jobType = 'Private';
		  			if(data.result[i].doc_exp_organisation_type == '1'){
		  				jobType = 'Government';
		  			}

		  			tableContent += '<tr>';
		                tableContent +='<td>'+data.result[i].doc_exp_organisation_name+'</td>';
		                tableContent +='<td>'+jobType+'</td>';
		                tableContent +='<td>'+data.result[i].doc_exp_designation+'</td>';
		                tableContent +='<td>'+data.result[i].doc_exp_start_year+'</td>';
		                tableContent +='<td>'+data.result[i].doc_exp_end_year+'</td>';
		                tableContent +='<td class="text-center"><a href="javascript:void(0)" class="btn red" onclick="delete_job_profile(\''+data.result[i].doc_exp_id+'\', \''+confMessage.replace("??", "job profile")+'\');">Delete</a></td>';
		            tableContent +='</tr>';
		        });
	        } else { 
		  		tableContent += '<tr>';
	                tableContent +='<td colspan="4">'+lang.no_record_found+'</td>';
	            tableContent +='</tr>';
		  	}
			
			$("#job-profile-table tbody").html(tableContent);
	    });
	}

	/**
	 * @DateOfCreation        11 June 2018
	 * @ShortDescription      This function is responsible for delete Degree by id
	 * @return                Array of response in table body
	 */
	function delete_job_profile(profileId, confrimMessage){
		var deleteUrl = BASE_URL+'/delete-job-profile/'+profileId;

		if(confirm(confrimMessage)){
		  	$.ajax({
			    type: "DELETE",
			    url: deleteUrl,
			    data: {_token:$('meta[name="csrf-token"]').attr('content')},
			    success: function(response) {
			    	$('.response-message').text(response.message).removeClass('hide').addClass('alert-success');
			    	getJobProfileList();
			    }
			});
		}else{
			return false;
		}
	}

	/**
	 * @DateOfCreation        11 June 2018
	 * @ShortDescription      This function is responsible for return first error from response
	 * @return                Array of response in table body
	 */
	function getFirstErrorMessage(jsonObj){
	    var key = Object.keys(jsonObj)[0];
	    var value = jsonObj[key][0];
	    return value;
	}


	$(function() {

	    // preventing page from redirecting
	    $("html").on("dragover", function(e) {
	        e.preventDefault();
	        e.stopPropagation();
	    });

	    $("html").on("drop", function(e) { e.preventDefault(); e.stopPropagation(); });

	    // Drag enter
	    $('.upload-area').on('dragenter', function (e) {
	        e.stopPropagation();
	        e.preventDefault();
	    });

	    // Drag over
	    $('.upload-area').on('dragover', function (e) {
	        e.stopPropagation();
	        e.preventDefault();
	
	        var file = e.originalEvent.dataTransfer.files;
	       imageLoader.files = file;
	       $("#user_photo").trigger("change");
	    });

	    // Drop
	    $('.upload-area').on('drop', function (e) {
	        e.stopPropagation();
	        e.preventDefault();
	        var file = e.originalEvent.dataTransfer.files;
	       imageLoader.files = file;
	       $("#user_photo").trigger("change");
	    });

	    // Open file selector on div click
	    $("#uploadfile").click(function(){
	        $("#user_photo").click();
	    });
	    
	});

	var imageLoader = document.getElementById('user_photo');
    imageLoader.addEventListener('change', handleImage, false);

	/**
	 * @DateOfCreation        11 June 2018
	 * @ShortDescription      This function is responsible to view upload image preview
	 * @return                image preview on image_preview element id
	 */
	function handleImage(e) {
	    let reader = new FileReader();
	    reader.onload = function(){
	      $('#image_preview').attr('src',reader.result);
	    }
	    if(e.target.files[0]){
	      reader.readAsDataURL(e.target.files[0]);
	      $(".imageuploadify-message").addClass('hide');
	    }
	}