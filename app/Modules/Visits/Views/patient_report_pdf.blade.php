<!DOCTYPE html>
<html lang="en">
<meta http-equiv="content-type" content="text/html;charset=UTF-8" />
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" type="text/css" href="{{ url(Config::get('constants.SAFE_HEALTH_CSS_PATH').'bootstrap.min.css') }}">
    <title>Patients Report</title>
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
		
		@if(count($pdf_data) > 0)
			<div class="patient-symptoms">
				<h2>Patients</h2>
				<table>
					<thead>
						<tr>
							<th>Created Date</th>
							<th>Patient Name</th>
							<th>Patient Code</th>
							<th>Mobile Number</th>
							<th>Email Address</th>
							<th>Gender</th>
							<th>Group</th>
						</tr>
					</thead>
					<tbody>
						@foreach ($pdf_data as $dataValue)
						    <tr>
								<td>{{ \Carbon\Carbon::parse($dataValue->created_at)->format('d/m/Y')}}</td>
								<td>{{$dataValue->user_firstname}}</td>
								<td>{{$dataValue->pat_code}}</td>
								<td>{{$dataValue->user_mobile}}</td>
								<td>{{$dataValue->user_email}}</td>
								<td>{{$dataValue->user_gender}}</td>
								<td>{{$dataValue->pat_group_name}}</td>
							</tr>
						@endforeach
					</tbody>
				</table>
			</div>
		@endif
	</div>
</body>
</html>