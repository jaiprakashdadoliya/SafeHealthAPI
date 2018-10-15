<!DOCTYPE HTML>
<html lang="en">
<head>
      @include('frontend.common.head')
      
      <!-- CSRF Token -->
      <meta name="csrf-token" content="{{ csrf_token() }}">

      <script type="text/javascript">
         var BASE_URL = "{{url('/')}}";
         var OTHER_CITY_TEXT = "{{ Config::get('constants.OTHER_CITY_TEXT') }}";
         var DEFAULT_COUNTRY_NAME_SELECTED = "{{ Config::get('constants.DEFAULT_COUNTRY_NAME_SELECTED') }}";
         var BASIC_INFO_SAVE_TEXT = "{{ __('register_investigator.save_and_continue') }}";
      </script>

      @yield('css-style')
   </head>
   <body>
      <div id="page">
         <!-- Preloader -->
         <div id="page-preloader">
            <div class="spinner centered-container"></div>
         </div>
         <!-- END Preloader -->
         
         <!-- Header -->
            @include('frontend.common.menu')
         <!-- END Header -->

         @yield('content')

         <?php /*@include('frontend.common.footer')*/ ?>
      </div>
         <script type="text/javascript" src="{{ url(Config::get('constants.SAFE_HEALTH_JS_PATH').'jquery-2.1.3.min.js') }}"></script>
         <script type="text/javascript" src="{{ url(Config::get('constants.SAFE_HEALTH_JS_PATH').'script.js') }}"></script>
         <script type="text/javascript" src="{{ url(Config::get('constants.SAFE_HEALTH_JS_PATH').'bootstrap.min.js') }}"></script>
         <script type="text/javascript" src="{{ url(Config::get('constants.SAFE_HEALTH_JS_PATH').'wizard.js') }}"></script>
         <script type="text/javascript" src="{{ url(Config::get('constants.SAFE_HEALTH_JS_PATH').'main.js') }}"></script>

         @yield('js-script')
   </body>

</html>        