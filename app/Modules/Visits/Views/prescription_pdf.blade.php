<!DOCTYPE html>
<html lang="en">
<meta http-equiv="content-type" content="text/html;charset=UTF-8" />
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" type="text/css" href="{{ url(Config::get('constants.SAFE_HEALTH_CSS_PATH').'bootstrap.min.css') }}">
    <title>Prescription</title>
    <style type="text/css">
	    .prescription {
	    	width: 900px;
	    	max-width: 100%;
	    	margin: 0 auto;
	    }
	    .patient-details {
	    	border-bottom: solid 1px #ddd;
	    	padding-bottom: 10px;
	    	margin-bottom: 10px;
	    }
	    .doctor-details, .vitals-details  {
	    	margin-bottom: 30px;
	    }
	    .vitals-details h2, .clinical-note h2, .medicine-details h2, .patient-symptoms h2 {
	    	font-size: 20px;
	    	background: #eee;
	    	padding: 5px 10px;
	    	margin-top: 10px;
	    }
	    table {
	    	width: 100%;
	    }
		th, td {
		    text-align: inherit;
		    border-bottom: solid 1px #ddd;
			padding: 5px;
		}
		.width20{
			width: 20%;
			float: left;
		}
		.vitals-details-width16{
			width: 16%;
			float: left;
			margin-bottom: 20px;
		}
		.text-cente{
			text-align: center;
		}
    </style>
</head>

<body>
	<div class="prescription">
		<div class="patient-details">
			<div class="row">
				<div class="col-sm-9 col-xs-9 text-left">
					<b>{{$patient_info->patient_firstname.' '.$patient_info->patient_lastname}} ({{$visit_info['pat_code']}})</b><br>
					{{$patient_info->user_gender}}, {{$visit_info['pat_dob']}}
				</div>
				<div class="col-sm-3 col-xs-2 text-right">
					Date: <b>{{$visit_info['created_at']}}</b>
				</div>
			</div>
		</div>
		<div class="doctor-details">
			<div class="row">
				<div class="col-sm-9 col-xs-9">
					By: <b>Dr. {{$visit_info['doctor_firstname'].' '.$visit_info['doctor_lastname'] }}</b><br>					
				</div>
				<div class="col-sm-3 col-xs-2 text-right">
					Reg. No.:  {{$visit_info['doc_registration_number']}}
				</div>
			</div>
		</div>

		@if(count($vital) > 0)
			<div class="vitals-details">
				@foreach ($vital as $vitalsData)			    
				    <div class="vitals-details-width16">
						{{ $vitalsData['label'] }} ({{ $vitalsData['unit'] }})<br>
						<span><b>{{ $vitalsData['value'] }}</b></span>
					</div>
				@endforeach
			</div>
		@endif
<br><br><br><br>

		<div class="clearfix"></div>
		@if(count($clinical_notes) > 0)
			<div class="clinical-note">
				<h2>Clinical Notes</h2>
				<ul>
					@foreach ($clinical_notes as $clinicalNote)			    
					    <li>
							{{$clinicalNote->text}}
						</li>
					@endforeach
				</ul>
			</div>
		@endif

		<div class="medicine-details">
			<h2>Prescription(R<sub>x</sub>)</h2>
			<table>
				<thead>
					<tr>
						<th></th>
						<th>Drug Name</th>
						<th>End Date</th>
						<th colspan="3" class="text-center">Fequency</th>
						<th class="text-center">Instructions</th>
						<th class="text-center">Total</th>
					</tr>
				</thead>
				<tbody>
					@foreach ($medicines as $medicine)
						
					    <tr>
							<td>{{ $loop->iteration }}.</td>
							<td>
								{{$medicine['drug_type_name']}} 
								<b>{{empty($medicine['drug_dose']) ? $medicine['medicine_name'] : $medicine['medicine_name'].' ('.$medicine['drug_dose'].' '.$medicine['drug_dose_unit_name'].')'}}</b>
							</td>
							<td>{{$medicine['medicine_end_date_formatted']}}</td>
							<td class="text-center">{{$medicine['medicine_dose']}}<br> Morning</td>
							<td class="text-center">{{$medicine['medicine_dose2']}}<br> Afternoon</td>
							<td class="text-center">{{$medicine['medicine_dose3']}}<br> Night</td>
							<td class="text-left">
								<ul>
									<li>{{$medicine['medicine_duration'].' '.$medicine['medicine_duration_unitVal']}}<br>{{$medicine['medicine_meal_optVal']}}</li>
									
									@if(!empty($medicine['medicine_instractions']) && count($medicine['medicine_instractions']) > 0)
										@foreach ($medicine['medicine_instractions'] as $instructions)			    
										    <li>
												{{$instructions['text']}}
											</li>
										@endforeach
									@endif
								</ul>
							</td>
							<td class="text-center">
							@if ($medicine['drug_type_name'] == 'tablet')
								{{(($medicine['medicine_duration'] * $medicine['medicine_dose'])+($medicine['medicine_duration'] * $medicine['medicine_dose2'])+($medicine['medicine_duration'] * $medicine['medicine_dose3']))}}
							@else
								{{'Syrup'}}
							@endif
							</td>
						</tr>
					@endforeach
				</tbody>
			</table>
		</div>	

		@if(count($symptom_data) > 0)
			<div class="patient-symptoms">
				<h2>Symptoms</h2>
				<table>
					<thead>
						<tr>
							<th></th>
							<th>Name</th>
							<th>Since</th>
							<th>Comment</th>
						</tr>
					</thead>
					<tbody>
						@foreach ($symptom_data as $symptoms)			    
						    <tr>
								<td>{{ $loop->iteration }}.</td>
								<td>{{$symptoms['symptom_name']}}</td>
								<td>{{ \Carbon\Carbon::parse($symptoms['since_date'])->format('d/m/Y')}}</td>
								<td>{{$symptoms['comment']}}</td>
							</tr>
						@endforeach
					</tbody>
				</table>
			</div>
		@endif

		@if(count($diagnosis_data) > 0)
			<div class="patient-symptoms">
				<h2>Diagnosis</h2>
				<table>
					<thead>
						<tr>
							<th></th>
							<th>Disease/Disorder</th>
							<th>Date of Diagnosis</th>
						</tr>
					</thead>
					<tbody>
						@foreach ($diagnosis_data as $diagnosis)			    
						    <tr>
								<td>{{ $loop->iteration }}.</td>
								<td>{{$diagnosis['disease_name']}}</td>
								<td>{{ \Carbon\Carbon::parse($diagnosis['date_of_diagnosis'])->format('d/m/Y')}}</td>
							</tr>
						@endforeach
					</tbody>
				</table>
			</div>
		@endif	

		@if(count($labtest_data) > 0)
			<div class="patient-symptoms">
				<h2>Laboratory Test</h2>
				<table>
					<thead>
						<tr>
							<th></th>
							<th>Procedure/Test Name</th>
							<th>Uploaded Date & Time</th>
							<th>Report File</th>
						</tr>
					</thead>
					<tbody>
						@foreach ($labtest_data as $labtest)			    
						    <tr>
								<td>{{ $loop->iteration }}.</td>
								<td>{{$labtest['lab_report_name']}}</td>
								<td>{{ \Carbon\Carbon::parse($labtest['created_at'])->format('d/m/Y H:i:s')}}</td>
								<td>@if($labtest['lab_report_file'] != "")
			                       	Updated on RxHealth
			                    	@else
			                        Not uploaded
			                    	@endif
			                    </td>
							</tr>
						@endforeach
					</tbody>
				</table>
			</div>
		@endif
	</div>
</body>
</html>
