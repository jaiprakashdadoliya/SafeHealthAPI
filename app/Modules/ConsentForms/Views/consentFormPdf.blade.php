<!DOCTYPE html>
<html lang="en">
<meta http-equiv="content-type" content="text/html;charset=UTF-8" />
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" type="text/css" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
    <title>{{$title}}</title>
    <style type="text/css">
        .consentForm {
            width: 900px;
            max-width: 100%;
            margin: 0 auto;
        }
        .form-details {
            border-bottom: solid 1px #ddd;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }
    </style>
</head>

<body>
    <div class="consentForm">
        <div class="form-details">
            <div class="row">
                <div class="col-md-12">
                    <h1>{{$title}}</h2>
                    <hr>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <p>{!! $content !!}</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>