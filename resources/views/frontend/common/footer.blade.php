<footer class="footer fw-main-row">
   <div class="fw-container">
      <div class="fw-row">
         <div class="fw-col-xs-12 fw-col-sm-4 fw-col-md-4">
            <div class="footer-logo"><a href={{ url('/') }}><img src="{{ url(Config::get('constants.SAFE_HEALTH_IMAGE_PATH').'logo.png') }}" alt="ILD India Registry"></a></div>
            <p><strong>Interstitial Lung Disease Registry, India, Protocol</strong></p>
            <div class="footer-copy">(c) ILD India Registry 2018</div>
         </div>
         <div class="fw-col-xs-12 fw-col-sm-4 fw-col-md-4">
            <h6>Contact us</h6>
            <!-- Contact item -->
            <span class="contact-item"><i class="icon-font icon-placeholder-1"></i> <span>4321 Your Address, Country</span></span>
            <!-- END Contact item -->
            <!-- Contact item -->
            <span class="contact-item"><i class="icon-font icon-telephone-1"></i> <span>8 800 2336 7811</span></span>
            <!-- END Contact item -->
         </div>
         <div class="fw-col-xs-12 fw-col-sm-4 fw-col-md-4">
            <h6>Links</h6>
            <ul class="footer-menu">
               <li><a href="{{ url('/') }}">Home</a></li>
               <li><a href="{{ url('/classification-of-ild') }}">Classification of ILD</a></li>
               <li><a href="{{ url('/become-an-investigator') }}">Become an Investigator</a></li>
               <li><a href="{{ url('/term-and-condition') }}">Terms & Conditions</a></li>
               <li><a href="{{ url('/contact-us') }}">Contact Us</a></li>
               <li><a href="{{ Config::get('constants.REACT_APP_PATH') }}">Login</a></li>
            </ul>
         </div>
      </div>
   </div>
</footer>