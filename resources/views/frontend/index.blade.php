@extends('frontend.common.master')

@section('title'){{ __('frontend.meta_home_title') }}@endsection
@section('meta_keywords'){{ __('frontend.meta_home_keyword') }}@endsection
@section('meta_description'){{ __('frontend.meta_home_description') }}@endsection

@section('content')
<!-- Full screen section -->
<section class="full-screen fw-main-row" style="background-image: url({{url(Config::get('constants.ILD_IMAGE_PATH').'imgs/medical.jpg')}});">
   <div class="fw-container centered-container tar">
      <div class="container tar fw-col-xs-12 fw-col-sm-6 fw-col-md-5">
         <h2 class="h1"><span class="blue-color">Want to Join as </span><br>an Investigator for<br> {{ __('frontend.site_title') }} </h2>
         <a href="{{ url('/become-an-investigator') }}" class="button-style1"><span>{{ __('frontend.btn_click_here') }}</span></a>
      </div>
   </div>
      <div class="bottom-strap">
         <i>An Initiative of</i> &nbsp; <strong>{{ __('frontend.indian_chest_society') }}</strong>
      </div>
</section>
<!-- END Full screen section -->
<!-- Category items -->
<section class="fw-main-row pt40 pb50">
   <div class="fw-container">
      <h2 class="heading-decor">About ILD</h2>
      <div class="fw-row">
         
         <div class="fw-col-md-12 content">
            <div class="fw-col-md-6">
               <img class="retina-img" src="{{url(Config::get('constants.ILD_IMAGE_PATH').'imgs/people.jpg')}}" alt="People">
            </div>
            <div class="fw-col-md-6">
               <p>{{ __('frontend.index_para_1') }}</p>
            </div>
         </div>
         <div class="fw-col-md-12 content">
            <p>{{ __('frontend.index_para_2') }}</p>

            <h5>Historical names of ILD</h5>
            <p>{{ __('frontend.index_para_3') }}</p>
            <p>{{ __('frontend.index_para_4') }}</p>
            <p>{{ __('frontend.index_para_5') }}</p>
         </div>
      </div>
   </div>
</section>
<!-- END Category items -->
 @endsection