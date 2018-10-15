@extends('frontend.common.master')

@section('title'){{ __('passwords.password_reset_title') }}@endsection
@section('meta_keywords'){{ __('frontend.meta_become_investigator_keyword') }}@endsection
@section('meta_description'){{ __('frontend.meta_become_investigator_description') }}@endsection

@section('css-style')
<link rel="stylesheet" type="text/css" href="{{ url(Config::get('constants.SAFE_HEALTH_CSS_PATH').'parsley.css') }}">
@endsection

@section('content')
<section class="fw-main-row pt40 pb50">
    <div class="fw-container">
    <div class="col-md-4 col-md-offset-4">
        @if($isTokenValid == 1)
            <h2 class="heading-decor"> {{ __('passwords.password_reset_title') }} </h2>

            <p class="response-message alert mt20"></p>

            <form role="form" action="{{url('/password/reset')}}" method="post" id="reset_password" enctype="multipart/form-data" data-parsley-validate >
                @csrf
                <input type="hidden" name="token" value="{{$token}}">
                <input type="hidden" name="user_token" value="{{$emailToken}}">
                <div class="fw-row form-body-classic pt20 pb50 ">
                    
                        <div class="form-group">
                            <input type="password" name="password" id="user_password" placeholder="password" class="form-control" required data-parsley-minlength="6"
                            data-parsley-uppercase="1"
                            data-parsley-lowercase="1"
                            data-parsley-number="1"
                            data-parsley-pattern="/^.*(?=.{3,})(?=.*[a-zA-Z])(?=.*[0-9])(?=.*[\d\X])(?=.*[!@*$#%]).*$/"
                            data-parsley-special="1"
                            data-parsley-pattern-message="{{ __('passwords.password_validate_message') }}"
                            >
                            <label>{{ __('passwords.password_new_pasword') }}</label>
                        </div>
                    
                    
                        <div class="form-group">
                            <input type="password" name="user_password_confirm" placeholder="confirm password" class="form-control" required data-parsley-minlength="6"
                            data-parsley-equalto="#user_password">
                            <label>{{ __('register_investigator.password_confirm') }}</label>
                        </div>
                    
                    <div class="text-right">
                        <button type="submit" class="button-style1">{{ __('frontend.btn_submit') }}</button>
                    </div>
                </div>
            </form>
        @else
            <div class="message">
                <h2 class="text-center"> {{ __('passwords.password_invalid_token_message') }} </h2>
            </div>
        @endif

    </div>
</section>         
         
@endsection

@section('js-script')
    <script type="text/javascript" src="{{ url(Config::get('constants.SAFE_HEALTH_JS_PATH').'frontend-lang.js') }}"></script>
    <script type="text/javascript" src="{{ url(Config::get('constants.SAFE_HEALTH_JS_PATH').'frontend.js') }}"></script>
    <script type="text/javascript" src="{{ url(Config::get('constants.SAFE_HEALTH_JS_PATH').'parsley.min.js') }}"></script>
@endsection