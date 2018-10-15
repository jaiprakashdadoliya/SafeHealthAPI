<?php

namespace App\Modules\ReviewRating\Controllers;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Traits\RestApi;
use Config;
use App\Modules\ReviewRating\Models\ReviewRating as ReviewRating;

class ReviewRatingController extends Controller
{
    /**
     *  use restApi is trait for using function
     */
    use RestApi;
    // @var Array $http_codes
    // This protected member contains Http Status Codes
    protected $http_codes = [];
    
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->http_codes = $this->http_status_codes();
        // Init ReviewRating model object
        $this->reviewRatingModel = new ReviewRating();
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $requestData = $this->getRequestData($request);
        // Validate request
        $validate = $this->ReviewRatingValidator($requestData);
        if($validate["error"])
        {
            return $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                $validate['errors'],
                trans('ReviewRating::messages.rev_rat_failed'), 
                $this->http_codes['HTTP_OK']
            ); 
        }
        $requestData['review_user_id'] = $request->user()->user_id;

        $isReviewerExist = $this->reviewRatingModel->getReviewRatingById('',$requestData['review_user_id'],$requestData['user_id']);
         if(!empty($isReviewerExist))
        {
            return  $this->resultResponse(
                Config::get('restresponsecode.SUCCESS'), 
                [], 
                [],
                trans('ReviewRating::messages.reviewer_exist'), 
                $this->http_codes['HTTP_OK']
            );

        }
       // Create service in database 
        $isReviewRatingCreated = $this->reviewRatingModel->createReviewRating($requestData);
        // validate, is query executed successfully 
        if(!empty($isReviewRatingCreated))
        {
            return  $this->resultResponse(
                Config::get('restresponsecode.SUCCESS'), 
                $isReviewRatingCreated, 
                [],
                trans('ReviewRating::messages.rev_rat_save'), 
                $this->http_codes['HTTP_OK']
            );

        }else{
            return  $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                [],
                trans('ReviewRating::messages.rev_rat_failed'), 
                $this->http_codes['HTTP_OK']
            );
        }
    }

    /**
    * This function is responsible for validating service data
    * 
    * @param  Array $data This contains full member input data 
    * @return Array $error status of error
    */ 
    private function ReviewRatingValidator(array $data)
    {
        $error      = false;
        $errors     = [];
        $rules      = ['comment'=> 'required'];
        $messages   = ['comment.required'     => "The comment required is required."];
        $validator = Validator::make($data, $rules, $messages); 
        if($validator->fails()){
            $error = true;
            $errors = $validator->errors();
        }
        return ["error" => $error,"errors" => $errors];
    }
}
